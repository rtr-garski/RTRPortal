<?php

class BackblazeB2 {

    private string $keyId;
    private string $appKey;
    private string $bucketId;
    private string $bucketName;

    private ?string $authToken   = null;
    private ?string $apiUrl      = null;
    private ?string $downloadUrl = null;

    public function __construct(string $keyId, string $appKey, string $bucketId, string $bucketName) {
        $this->keyId      = $keyId;
        $this->appKey     = $appKey;
        $this->bucketId   = $bucketId;
        $this->bucketName = $bucketName;
    }

    private function authorize(): void {
        $ch = curl_init('https://api.backblazeb2.com/b2api/v2/b2_authorize_account');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($this->keyId . ':' . $this->appKey),
            ],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resp = json_decode($body, true);
        if ($code !== 200 || !isset($resp['authorizationToken'])) {
            throw new RuntimeException('B2 authorize failed: ' . ($resp['message'] ?? $body));
        }

        $this->authToken   = $resp['authorizationToken'];
        $this->apiUrl      = $resp['apiUrl'];
        $this->downloadUrl = $resp['downloadUrl'];
    }

    private function ensureAuth(): void {
        if ($this->authToken === null) {
            $this->authorize();
        }
    }

    private function post(string $url, array $payload, string $authToken): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $authToken,
                'Content-Type: application/json',
            ],
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $resp ?? [];
    }

    public function upload(string $originalName, string $tmpPath, string $mimeType): array {
        $this->ensureAuth();

        $resp = $this->post(
            $this->apiUrl . '/b2api/v2/b2_get_upload_url',
            ['bucketId' => $this->bucketId],
            $this->authToken
        );

        if (!isset($resp['uploadUrl'])) {
            throw new RuntimeException('B2 get_upload_url failed: ' . ($resp['message'] ?? 'unknown'));
        }

        $uploadUrl   = $resp['uploadUrl'];
        $uploadToken = $resp['authorizationToken'];

        $content  = file_get_contents($tmpPath);
        $sha1     = sha1($content);
        $size     = strlen($content);
        $b2Name   = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $content,
            CURLOPT_HTTPHEADER     => [
                'Authorization: '      . $uploadToken,
                'X-Bz-File-Name: '     . rawurlencode($b2Name),
                'Content-Type: '       . $mimeType,
                'Content-Length: '     . $size,
                'X-Bz-Content-Sha1: '  . $sha1,
            ],
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($resp['fileId'])) {
            throw new RuntimeException('B2 upload failed: ' . ($resp['message'] ?? 'unknown'));
        }

        return [
            'b2_file_id'   => $resp['fileId'],
            'b2_file_name' => $b2Name,
            'file_size'    => $size,
        ];
    }

    // Equivalent of generate_presigned_url — valid for 1 hour
    public function generatePresignedUrl(string $b2FileName): string {
        $this->ensureAuth();

        $resp = $this->post(
            $this->apiUrl . '/b2api/v2/b2_get_download_authorization',
            [
                'bucketId'               => $this->bucketId,
                'fileNamePrefix'         => $b2FileName,
                'validDurationInSeconds' => 3600,
            ],
            $this->authToken
        );

        if (!isset($resp['authorizationToken'])) {
            throw new RuntimeException('B2 presign failed: ' . ($resp['message'] ?? 'unknown'));
        }

        return $this->downloadUrl
            . '/file/' . rawurlencode($this->bucketName)
            . '/' . rawurlencode($b2FileName)
            . '?Authorization=' . urlencode($resp['authorizationToken']);
    }

    public function deleteFile(string $b2FileId, string $b2FileName): void {
        $this->ensureAuth();

        $this->post(
            $this->apiUrl . '/b2api/v2/b2_delete_file_version',
            ['fileId' => $b2FileId, 'fileName' => $b2FileName],
            $this->authToken
        );
    }
}

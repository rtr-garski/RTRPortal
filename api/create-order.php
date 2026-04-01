<?php

// ─── Config ───────────────────────────────────────────────────────────────────

define('API_TOKEN', 'K4AwY7EZCRMkUfRPnc2qFCZusN9uPvBH9cT8HjXcrBfHJ492HH');

// ─── Helpers ──────────────────────────────────────────────────────────────────

function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data"    => $data
    ]);
    exit;
}

function getApiKey() {
    $headers = getallheaders();
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-api-key') {
            return $value;
        }
    }
    return null;
}

// ─── Validation ───────────────────────────────────────────────────────────────

function validateOrderPayload($data) {

    // Required top-level fields
    foreach (['customer_name', 'customer_email', 'phone_number', 'items'] as $field) {
        if (empty($data[$field])) {
            return "$field is required";
        }
    }

    // Email format
    if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
        return "customer_email is not a valid email address";
    }

    // Items must be a non-empty array
    if (!is_array($data['items']) || count($data['items']) === 0) {
        return "items must be a non-empty array";
    }

    // Each item must have sku, quantity, and unit_price
    foreach ($data['items'] as $i => $item) {
        if (empty($item['sku'])) {
            return "items[$i].sku is required";
        }
        if (!isset($item['quantity']) || !is_numeric($item['quantity']) || (int)$item['quantity'] < 1) {
            return "items[$i].quantity must be a positive integer";
        }
        if (!isset($item['unit_price']) || !is_numeric($item['unit_price']) || (float)$item['unit_price'] < 0) {
            return "items[$i].unit_price must be a non-negative number";
        }
    }

    // Optional shipping address — if provided, all sub-fields required
    if (isset($data['shipping_address'])) {
        foreach (['street', 'city', 'state', 'postal', 'country'] as $f) {
            if (empty($data['shipping_address'][$f])) {
                return "shipping_address.$f is required when shipping_address is provided";
            }
        }
    }

    return null;
}

// ─── Order Builder ────────────────────────────────────────────────────────────

function buildOrder($data) {
    $subtotal  = 0.0;
    $lineItems = [];

    foreach ($data['items'] as $item) {
        $qty       = (int)$item['quantity'];
        $price     = (float)$item['unit_price'];
        $lineTotal = $qty * $price;
        $subtotal += $lineTotal;

        $lineItems[] = [
            'sku'        => htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8'),
            'name'       => isset($item['name']) ? htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') : null,
            'quantity'   => $qty,
            'unit_price' => round($price, 2),
            'line_total' => round($lineTotal, 2),
        ];
    }

    return [
        'order_id'         => 'ORD-' . strtoupper(bin2hex(random_bytes(4))),
        'created_at'       => date('c'),
        'status'           => 'pending',
        'customer_name'    => htmlspecialchars($data['customer_name'],  ENT_QUOTES, 'UTF-8'),
        'customer_email'   => filter_var($data['customer_email'], FILTER_SANITIZE_EMAIL),
        'phone_number'     => htmlspecialchars($data['phone_number'],   ENT_QUOTES, 'UTF-8'),
        'items'            => $lineItems,
        'subtotal'         => round($subtotal, 2),
        'shipping_address' => $data['shipping_address'] ?? null,
        'notes'            => isset($data['notes']) ? htmlspecialchars($data['notes'], ENT_QUOTES, 'UTF-8') : null,
    ];
}

// ─── Request Handler ──────────────────────────────────────────────────────────

function handleOrderRequest() {

    // POST only — GET exposes order data in URLs and server logs
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, "Only POST method allowed", null, 405);
    }

    // API key authentication via X-API-Key header
    $apiKey = getApiKey();
    if (!$apiKey || $apiKey !== API_TOKEN) {
        sendResponse(false, "Unauthorized - Invalid API Key", null, 401);
    }

    // Parse JSON body
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        sendResponse(false, "Invalid or missing JSON body", null, 400);
    }

    // Validate payload
    $error = validateOrderPayload($input);
    if ($error) {
        sendResponse(false, $error, null, 400);
    }

    // Build and return the order
    $order = buildOrder($input);
    sendResponse(true, "Order created successfully", $order, 201);
}

if (!defined('API_INCLUDED')) {
    handleOrderRequest();
}

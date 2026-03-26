<?php

// Test API Token (SQL List)
define('API_TOKEN', 'K4AwY7EZCRMkUfRPnc2qFCZusN9uPvBH9cT8HjXcrBfHJ492HH');

// Send JSON response
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// Get API Key from header
function getApiKey() {
    $headers = getallheaders();

    // Some servers lowercase headers
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-api-key') {
            return $value;
        }
    }

    return null;
}

// Validation
function validatePayload($data) {

    //required fields
    if (empty($data['phone_number'])) {
        return "phone_number is required";
    }

    // Address validation
    if (isset($data['address'])) {
        if (
            empty($data['address']['city']) ||
            empty($data['address']['state']) ||
            empty($data['address']['postal'])
        ) {
            return "address must include city, state, and postal";
        }
    }

    // data1 validation
    if (isset($data['data1'])) {
        if (
            empty($data['data1']['company_name']) ||
            empty($data['data1']['date']) ||
            empty($data['data1']['test1'])
        ) {
            return "data1 must include company_name, date, and test1";
        }
    }

    return null;
}

// Main handler
function handleRequest() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, "Only POST method allowed", null, 405);
    }

    // API Key check
    $apiKey = getApiKey();
    if (!$apiKey || $apiKey !== API_TOKEN) {
        sendResponse(false, "Unauthorized - Invalid API Key", null, 401);
    }

    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        sendResponse(false, "Invalid JSON", null, 400);
    }

    // Validate
    $error = validatePayload($input);
    if ($error) {
        sendResponse(false, $error, null, 400);
    }

    // Success
    sendResponse(true, "Data received successfully", $input);
}

// Run
handleRequest();
<?php
// =============================================
//  KONFIGURASI SUPABASE
//  Ganti dengan URL dan KEY milik kamu
// =============================================

define('SUPABASE_URL', 'https://ryecexgzmfxcknmfjshz.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJ5ZWNleGd6bWZ4Y2tubWZqc2h6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4NjM5OTksImV4cCI6MjA5MjQzOTk5OX0.47FCcoJrRMe8CFPa-ufDRAAuUDw7Hd4ELizey9EmYag');

/**
 * Kirim request ke Supabase REST API
 */
function supabase_request(string $path, string $method = 'GET', array $body = [], array $params = []): array {
    $url = SUPABASE_URL . '/rest/v1/' . $path;

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    return ['code' => $httpCode, 'data' => $data];
}

/**
 * Upload file ke Supabase Storage
 */
function supabase_upload(string $bucket, string $filePath, string $fileContent, string $mimeType): ?string {
    $url = SUPABASE_URL . '/storage/v1/object/' . $bucket . '/' . $filePath;

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: ' . $mimeType,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        return SUPABASE_URL . '/storage/v1/object/public/' . $bucket . '/' . $filePath;
    }

    return null;
}

// =============================================
//  HELPERS RESPONSE
// =============================================

function respond(int $code, array $payload): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function respond_ok($data, string $message = 'Berhasil'): void {
    respond(200, ['success' => true, 'message' => $message, 'data' => $data]);
}

function respond_created($data, string $message = 'Data berhasil dibuat'): void {
    respond(201, ['success' => true, 'message' => $message, 'data' => $data]);
}

function respond_error(string $message, int $code = 400): void {
    respond($code, ['success' => false, 'message' => $message, 'data' => null]);
}

// Handle preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

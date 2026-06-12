<?php
/**
 * POST /api/v1/auth.php
 * Body: { "email": "...", "password": "..." }
 * Response: { success, message, data: UserModel }
 */

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Method tidak diizinkan', 405);
}

$body = json_decode(file_get_contents('php://input'), true);

$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

if (empty($email) || empty($password)) {
    respond_error('Email dan password wajib diisi');
}

$result = supabase_request('users', 'GET', [], [
    'email'    => 'eq.' . $email,
    'password' => 'eq.' . $password,
    'select'   => 'id,nama,email,role,posisi,no_hp',
]);

if ($result['code'] !== 200 || empty($result['data'])) {
    respond_error('Email atau password salah', 401);
}

$user = $result['data'][0];

respond_ok([
    'id'     => $user['id'],
    'nama'   => $user['nama'],
    'email'  => $user['email'],
    'role'   => $user['role'],
    'posisi' => $user['posisi'],
    'noHp'   => $user['no_hp'] ?? '',
], 'Login berhasil');

<?php
/**
 * GET    /api/v1/users.php              → Semua karyawan
 * GET    /api/v1/users.php?id=xxx       → Karyawan by ID
 * POST   /api/v1/users.php              → Tambah karyawan
 * PUT    /api/v1/users.php?id=xxx       → Edit karyawan
 * DELETE /api/v1/users.php?id=xxx       → Hapus karyawan
 */

require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

// ─── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    if ($id) {
        $res = supabase_request('users', 'GET', [], [
            'id'     => 'eq.' . $id,
            'select' => 'id,nama,email,role,posisi,no_hp',
        ]);
        if (empty($res['data'])) respond_error('Karyawan tidak ditemukan', 404);
        respond_ok(_fmt($res['data'][0]));
    } else {
        $res = supabase_request('users', 'GET', [], [
            'role'   => 'eq.karyawan',
            'select' => 'id,nama,email,role,posisi,no_hp',
            'order'  => 'nama.asc',
        ]);
        respond_ok(array_map('_fmt', $res['data'] ?? []));
    }
}

// ─── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $required = ['id', 'nama', 'email', 'password', 'posisi'];
    foreach ($required as $f) {
        if (empty($body[$f])) respond_error("Field '$f' wajib diisi");
    }

    // Cek duplikat email
    $cek = supabase_request('users', 'GET', [], [
        'email'  => 'eq.' . trim($body['email']),
        'select' => 'id',
    ]);
    if (!empty($cek['data'])) respond_error('Email sudah terdaftar');

    $res = supabase_request('users', 'POST', [
        'id'       => $body['id'],
        'nama'     => trim($body['nama']),
        'email'    => trim($body['email']),
        'password' => trim($body['password']),
        'role'     => 'karyawan',
        'posisi'   => trim($body['posisi']),
        'no_hp'    => trim($body['noHp'] ?? ''),
    ]);

    if ($res['code'] !== 201) respond_error('Gagal menambah karyawan', 500);
    respond_created(_fmt($res['data'][0] ?? []), 'Karyawan berhasil ditambahkan');
}

// ─── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!$id) respond_error('ID karyawan wajib disertakan');
    $body = json_decode(file_get_contents('php://input'), true);

    $update = array_filter([
        'nama'     => trim($body['nama']     ?? ''),
        'email'    => trim($body['email']    ?? ''),
        'password' => trim($body['password'] ?? ''),
        'posisi'   => trim($body['posisi']   ?? ''),
        'no_hp'    => trim($body['noHp']     ?? ''),
    ]);

    $res = supabase_request('users', 'PATCH', $update, ['id' => 'eq.' . $id]);
    if ($res['code'] !== 200) respond_error('Gagal mengupdate karyawan', 500);
    respond_ok(null, 'Data karyawan berhasil diperbarui');
}

// ─── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) respond_error('ID karyawan wajib disertakan');
    $res = supabase_request('users', 'DELETE', [], ['id' => 'eq.' . $id]);
    if ($res['code'] !== 200 && $res['code'] !== 204) respond_error('Gagal menghapus karyawan', 500);
    respond_ok(null, 'Karyawan berhasil dihapus');
}

respond_error('Method tidak diizinkan', 405);

// ─── FORMATTER ─────────────────────────────────────────────────────────────────
function _fmt(array $u): array {
    return [
        'id'     => $u['id']     ?? '',
        'nama'   => $u['nama']   ?? '',
        'email'  => $u['email']  ?? '',
        'role'   => $u['role']   ?? 'karyawan',
        'posisi' => $u['posisi'] ?? '',
        'noHp'   => $u['no_hp']  ?? '',
    ];
}

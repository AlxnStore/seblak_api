<?php
/**
 * GET    /api/v1/attendances.php                         → Semua absensi
 * GET    /api/v1/attendances.php?user_id=xxx             → Absensi by user
 * GET    /api/v1/attendances.php?tanggal=2025-01-01      → Absensi by tanggal
 * GET    /api/v1/attendances.php?check_today=xxx         → Cek absen hari ini
 * GET    /api/v1/attendances.php?rekap=xxx               → Rekap per user
 * GET    /api/v1/attendances.php?rekap_total=1           → Rekap total semua
 * POST   /api/v1/attendances.php (multipart/form-data)   → Tambah absensi + upload file
 * PUT    /api/v1/attendances.php?id=xxx (multipart)      → Edit absensi
 * DELETE /api/v1/attendances.php?id=xxx                  → Hapus absensi
 */

require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {

    // Cek absen hari ini
    if (!empty($_GET['check_today'])) {
        $today = date('Y-m-d');
        $res = supabase_request('attendances', 'GET', [], [
            'user_id' => 'eq.' . $_GET['check_today'],
            'tanggal' => 'eq.' . $today,
            'select'  => '*',
        ]);
        respond_ok(empty($res['data']) ? null : _fmt($res['data'][0]));
    }

    // Rekap per user
    if (!empty($_GET['rekap'])) {
        $res = supabase_request('attendances', 'GET', [], [
            'user_id' => 'eq.' . $_GET['rekap'],
            'select'  => 'status',
        ]);
        respond_ok(_rekap($res['data'] ?? []));
    }

    // Rekap total
    if (!empty($_GET['rekap_total'])) {
        $res = supabase_request('attendances', 'GET', [], ['select' => 'status']);
        respond_ok(_rekap($res['data'] ?? []));
    }

    // Filter tanggal
    if (!empty($_GET['tanggal'])) {
        $res = supabase_request('attendances', 'GET', [], [
            'tanggal' => 'eq.' . $_GET['tanggal'],
            'select'  => '*',
            'order'   => 'created_at.desc',
        ]);
        respond_ok(array_map('_fmt', $res['data'] ?? []));
    }

    // Filter user
    if (!empty($_GET['user_id'])) {
        $res = supabase_request('attendances', 'GET', [], [
            'user_id' => 'eq.' . $_GET['user_id'],
            'select'  => '*',
            'order'   => 'tanggal.desc',
        ]);
        respond_ok(array_map('_fmt', $res['data'] ?? []));
    }

    // Semua (admin)
    $res = supabase_request('attendances', 'GET', [], [
        'select' => '*',
        'order'  => 'created_at.desc',
    ]);
    respond_ok(array_map('_fmt', $res['data'] ?? []));
}

// ─── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    // Validasi field wajib
    foreach (['id', 'user_id', 'nama_karyawan', 'status'] as $f) {
        if (empty($_POST[$f])) respond_error("Field '$f' wajib diisi");
    }

    $validStatus = ['hadir', 'sakit', 'cuti'];
    if (!in_array($_POST['status'], $validStatus)) {
        respond_error('Status tidak valid. Gunakan: hadir, sakit, atau cuti');
    }

    // Upload file jika ada
    $fotoUrl      = _uploadFile('foto',       'foto',        $_POST['id']);
    $suratSakitUrl = _uploadFile('surat_sakit', 'surat_sakit', $_POST['id']);
    $suratCutiUrl  = _uploadFile('surat_cuti',  'surat_cuti',  $_POST['id']);

    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    $res = supabase_request('attendances', 'POST', [
        'id'              => $_POST['id'],
        'user_id'         => $_POST['user_id'],
        'nama_karyawan'   => trim($_POST['nama_karyawan']),
        'tanggal'         => $tanggal,
        'status'          => $_POST['status'],
        'foto_path'       => $fotoUrl,
        'surat_sakit_path' => $suratSakitUrl,
        'surat_cuti_path'  => $suratCutiUrl,
        'keterangan'      => trim($_POST['keterangan'] ?? ''),
        'created_at'      => date('c'),
    ]);

    if ($res['code'] !== 201) {
        respond_error('Gagal menyimpan absensi: ' . json_encode($res['data']), 500);
    }

    respond_created(_fmt($res['data'][0] ?? []), 'Absensi berhasil disimpan');
}

// ─── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $id = $_GET['id'] ?? null;
    if (!$id) respond_error('ID absensi wajib disertakan');

    // Ambil data lama
    $old = supabase_request('attendances', 'GET', [], [
        'id'     => 'eq.' . $id,
        'select' => '*',
    ]);
    if (empty($old['data'])) respond_error('Absensi tidak ditemukan', 404);
    $existing = $old['data'][0];

    // Upload file baru jika ada, pakai lama jika tidak
    $fotoUrl       = _uploadFile('foto',        'foto',        $id) ?? $existing['foto_path'];
    $suratSakitUrl = _uploadFile('surat_sakit', 'surat_sakit', $id) ?? $existing['surat_sakit_path'];
    $suratCutiUrl  = _uploadFile('surat_cuti',  'surat_cuti',  $id) ?? $existing['surat_cuti_path'];

    $update = array_filter([
        'status'           => $_POST['status']     ?? $existing['status'],
        'foto_path'        => $fotoUrl,
        'surat_sakit_path' => $suratSakitUrl,
        'surat_cuti_path'  => $suratCutiUrl,
        'keterangan'       => $_POST['keterangan'] ?? $existing['keterangan'],
    ], fn($v) => $v !== null);

    $res = supabase_request('attendances', 'PATCH', $update, ['id' => 'eq.' . $id]);
    if ($res['code'] !== 200) respond_error('Gagal mengupdate absensi', 500);
    respond_ok(null, 'Absensi berhasil diperbarui');
}

// ─── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) respond_error('ID absensi wajib disertakan');

    $res = supabase_request('attendances', 'DELETE', [], ['id' => 'eq.' . $id]);
    if ($res['code'] !== 200 && $res['code'] !== 204) respond_error('Gagal menghapus absensi', 500);
    respond_ok(null, 'Absensi berhasil dihapus');
}

respond_error('Method tidak diizinkan', 405);

// ─── HELPERS ───────────────────────────────────────────────────────────────────

function _uploadFile(string $field, string $folder, string $prefix): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

    $file     = $_FILES[$field];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    if (!in_array($ext, $allowed)) return null;

    $mimeMap = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'pdf'  => 'application/pdf',
    ];

    $mime     = $mimeMap[$ext] ?? 'application/octet-stream';
    $fileName = $folder . '/' . $prefix . '_' . time() . '.' . $ext;
    $content  = file_get_contents($file['tmp_name']);

    return supabase_upload('attendance-files', $fileName, $content, $mime);
}

function _fmt(array $a): array {
    return [
        'id'             => $a['id']               ?? '',
        'userId'         => $a['user_id']           ?? '',
        'namaKaryawan'   => $a['nama_karyawan']     ?? '',
        'tanggal'        => $a['tanggal']            ?? '',
        'status'         => $a['status']             ?? '',
        'fotoPath'       => $a['foto_path']          ?? null,
        'suratSakitPath' => $a['surat_sakit_path']   ?? null,
        'suratCutiPath'  => $a['surat_cuti_path']    ?? null,
        'keterangan'     => $a['keterangan']         ?? '',
        'createdAt'      => $a['created_at']         ?? '',
    ];
}

function _rekap(array $rows): array {
    $hadir = $sakit = $cuti = 0;
    foreach ($rows as $r) {
        if ($r['status'] === 'hadir')     $hadir++;
        elseif ($r['status'] === 'sakit') $sakit++;
        elseif ($r['status'] === 'cuti')  $cuti++;
    }
    return ['hadir' => $hadir, 'sakit' => $sakit, 'cuti' => $cuti];
}

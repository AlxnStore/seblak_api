<?php
$base = 'https://seblak-api-5t88.vercel.app';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>API Absensi Seblak Prasmanan</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; color: #333; }
  .hero {
    background: linear-gradient(135deg, #D32F2F, #FF7043);
    color: white; padding: 40px 24px; text-align: center;
  }
  .hero h1 { font-size: 2rem; margin-bottom: 8px; }
  .hero p  { opacity: .85; font-size: 1rem; }
  .container { max-width: 900px; margin: 0 auto; padding: 32px 16px; }
  .section { background: white; border-radius: 12px; padding: 24px; margin-bottom: 24px;
             box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .section h2 { font-size: 1.2rem; color: #D32F2F; border-bottom: 2px solid #fce4e4;
                padding-bottom: 10px; margin-bottom: 16px; }
  .endpoint { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
  .badge {
    display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: .75rem;
    font-weight: bold; color: white; min-width: 64px; text-align: center; flex-shrink: 0; margin-top: 2px;
  }
  .GET    { background: #28a745; }
  .POST   { background: #007bff; }
  .PUT    { background: #fd7e14; }
  .DELETE { background: #dc3545; }
  .url  { color: #555; font-family: monospace; font-size: .85rem; word-break: break-all; }
  .desc { color: #777; font-size: .82rem; margin-top: 2px; }
  .params { background: #f8f8f8; border-radius: 8px; padding: 14px; margin-top: 8px;
            border-left: 3px solid #D32F2F; }
  .params code { font-size: .82rem; display: block; margin-bottom: 4px; color: #333; }
  .note { background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 16px;
          border-radius: 6px; font-size: .85rem; color: #555; margin-bottom: 20px; }
  footer { text-align: center; color: #aaa; font-size: .8rem; padding: 24px; }
</style>
</head>
<body>

<div class="hero">
  <h1>🌶️ API Absensi Seblak Prasmanan</h1>
  <p>REST API untuk sistem absensi karyawan — Powered by PHP + Supabase</p>
</div>

<div class="container">

  <div class="note">
    ⚠️ <strong>Base URL:</strong> Ganti <code>YOUR-PROJECT.vercel.app</code> dengan domain Vercel kamu setelah deploy.<br>
    Semua endpoint menerima dan mengembalikan format <strong>JSON</strong>.<br>
    Upload file menggunakan <strong>multipart/form-data</strong>.
  </div>

  <!-- AUTH -->
  <div class="section">
    <h2>🔐 Autentikasi</h2>

    <div class="endpoint">
      <span class="badge POST">POST</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/auth.php</div>
        <div class="desc">Login karyawan / admin</div>
        <div class="params">
          <code>Body (JSON): { "email": "...", "password": "..." }</code>
          <code>Response: { success, message, data: { id, nama, email, role, posisi, noHp } }</code>
        </div>
      </div>
    </div>
  </div>

  <!-- USERS -->
  <div class="section">
    <h2>👥 Karyawan (Users)</h2>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/users.php</div>
        <div class="desc">Semua karyawan (role=karyawan)</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/users.php?id=UUID</div>
        <div class="desc">Karyawan berdasarkan ID</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge POST">POST</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/users.php</div>
        <div class="desc">Tambah karyawan baru</div>
        <div class="params">
          <code>Body (JSON): { "id", "nama", "email", "password", "posisi", "noHp" }</code>
        </div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge PUT">PUT</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/users.php?id=UUID</div>
        <div class="desc">Edit data karyawan</div>
        <div class="params">
          <code>Body (JSON): { "nama", "email", "password", "posisi", "noHp" }</code>
        </div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge DELETE">DELETE</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/users.php?id=UUID</div>
        <div class="desc">Hapus karyawan</div>
      </div>
    </div>
  </div>

  <!-- ATTENDANCES -->
  <div class="section">
    <h2>📋 Absensi (Attendances)</h2>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php</div>
        <div class="desc">Semua data absensi (untuk admin)</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?user_id=UUID</div>
        <div class="desc">Riwayat absensi per karyawan</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?tanggal=2025-01-01</div>
        <div class="desc">Absensi berdasarkan tanggal (format: YYYY-MM-DD)</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?check_today=USER_ID</div>
        <div class="desc">Cek apakah karyawan sudah absen hari ini</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?rekap=USER_ID</div>
        <div class="desc">Rekap hadir/sakit/cuti per karyawan</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge GET">GET</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?rekap_total=1</div>
        <div class="desc">Rekap total semua karyawan (untuk admin)</div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge POST">POST</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php</div>
        <div class="desc">Tambah absensi baru (dengan upload foto/surat)</div>
        <div class="params">
          <code>Content-Type: multipart/form-data</code>
          <code>Fields: id, user_id, nama_karyawan, status (hadir/sakit/cuti), tanggal, keterangan</code>
          <code>Files (opsional): foto, surat_sakit, surat_cuti (jpg/png/pdf)</code>
        </div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge PUT">PUT</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?id=UUID</div>
        <div class="desc">Edit absensi (status, keterangan, ganti file)</div>
        <div class="params">
          <code>Content-Type: multipart/form-data</code>
          <code>Fields: status, keterangan</code>
          <code>Files (opsional): foto, surat_sakit, surat_cuti</code>
        </div>
      </div>
    </div>

    <div class="endpoint">
      <span class="badge DELETE">DELETE</span>
      <div>
        <div class="url"><?= $base ?>/api/v1/attendances.php?id=UUID</div>
        <div class="desc">Hapus data absensi</div>
      </div>
    </div>
  </div>

  <!-- RESPONSE FORMAT -->
  <div class="section">
    <h2>📦 Format Response</h2>
    <div class="params">
      <code>// Sukses</code>
      <code>{ "success": true, "message": "...", "data": { ... } }</code>
      <code></code>
      <code>// Error</code>
      <code>{ "success": false, "message": "Pesan error", "data": null }</code>
    </div>
  </div>

</div>

<footer>🌶️ Seblak Prasmanan &copy; <?= date('Y') ?> — REST API v1</footer>
</body>
</html>

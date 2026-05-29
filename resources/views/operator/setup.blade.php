@extends('layouts.app')
@section('title', 'Panduan Instalasi & Database')
@section('page-title', 'Panduan Instalasi')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Panduan Instalasi</span></div>
  <h1>Panduan Instalasi &amp; Database</h1>
  <p class="text-muted">Instruksi teknis untuk memindahkan atau mengatur ulang database aplikasi.</p>
</div>

<div style="max-width: 900px;" class="animate-slideup">
  
  {{-- Status Koneksi Saat Ini --}}
  <div class="card mb-4" style="border-left: 4px solid var(--teal); background: linear-gradient(to right, rgba(0,201,167,0.05), transparent);">
    <div class="card-body-sm">
      <div style="display:flex; align-items:center; gap:16px;">
        <div style="width:48px; height:48px; border-radius:12px; background:var(--teal); display:flex; align-items:center; justify-content:center; color:var(--navy); font-size:1.4rem; box-shadow: 0 4px 12px rgba(0,201,167,0.3);">
          <i class="fa-solid fa-database"></i>
        </div>
        <div>
          <h4 style="margin:0; font-weight:700; color:var(--text-main);">Status Koneksi Database</h4>
          <p class="text-sm text-muted" style="margin:0;">Aplikasi saat ini terhubung ke: <strong style="color:var(--teal);">{{ strtoupper($dbConnection) }}</strong> ({{ $dbName }})</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Langkah-langkah --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-gears" style="color:var(--blue-light);"></i>
      <h3>Langkah Instalasi Baru</h3>
    </div>
    <div class="card-body">
      
      <div class="setup-step" style="margin-bottom:32px; border-bottom: 1px solid var(--border-color); padding-bottom: 24px;">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <span style="width:24px; height:24px; border-radius:50%; background:var(--teal); color:var(--navy); display:flex; align-items:center; justify-content:center; font-size:0.8rem;">1</span>
          Persiapan Database
        </h4>
        <div style="padding-left: 32px;">
          <p>Jika ingin menggunakan <strong>MySQL (XAMPP)</strong>:</p>
          <ul style="padding-left:20px; margin-bottom:12px;" class="text-sm text-muted">
            <li>Buka <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin).</li>
            <li>Buat database baru dengan nama <code style="color:var(--blue-light);">{{ $dbName }}</code>.</li>
            <li>Pastikan Apache dan MySQL di XAMPP sudah dalam status <strong>Running</strong>.</li>
          </ul>
        </div>
      </div>

      <div class="setup-step" style="margin-bottom:32px; border-bottom: 1px solid var(--border-color); padding-bottom: 24px;">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <span style="width:24px; height:24px; border-radius:50%; background:var(--teal); color:var(--navy); display:flex; align-items:center; justify-content:center; font-size:0.8rem;">2</span>
          Konfigurasi File .env
        </h4>
        <div style="padding-left: 32px;">
          <p>Buka file <code style="color:var(--blue-light);">.env</code> di folder utama aplikasi, lalu sesuaikan bagian berikut:</p>
          <pre style="background:var(--navy-light); padding:16px; border-radius:10px; font-size:0.85rem; border:1px solid rgba(255,255,255,0.05); overflow-x:auto; color:#e2e8f0; font-family:'Fira Code', 'Consolas', monospace; line-height:1.6;">
<span style="color:var(--blue-light);">DB_CONNECTION</span>=mysql
<span style="color:var(--blue-light);">DB_HOST</span>=127.0.0.1
<span style="color:var(--blue-light);">DB_PORT</span>=3306
<span style="color:var(--blue-light);">DB_DATABASE</span>={{ $dbName }}
<span style="color:var(--blue-light);">DB_USERNAME</span>=root
<span style="color:var(--blue-light);">DB_PASSWORD</span>=</pre>
        </div>
      </div>

      <div class="setup-step" style="margin-bottom:32px;">
        <h4 style="color:var(--teal); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
          <span style="width:24px; height:24px; border-radius:50%; background:var(--teal); color:var(--navy); display:flex; align-items:center; justify-content:center; font-size:0.8rem;">3</span>
          Perintah Inisialisasi (Terminal/CMD)
        </h4>
        <div style="padding-left: 32px;">
          <p>Buka Terminal atau Command Prompt di folder utama aplikasi, lalu jalankan perintah berikut secara berurutan:</p>
          
          <div style="display:flex; flex-direction:column; gap:16px; margin-top:16px;">
            
            <div style="background:var(--bg-body); padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:12px;">
              <div style="flex:1;">
                <div class="text-xs" style="font-weight:600; color:var(--teal); margin-bottom:4px;">A. MIGRASI TABEL (WAJIB)</div>
                <code style="background:transparent; color:var(--text-main); padding:0; font-family:monospace; font-size:0.95rem;">php artisan migrate</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div style="background:var(--bg-body); padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:12px;">
              <div style="flex:1;">
                <div class="text-xs" style="font-weight:600; color:var(--blue-light); margin-bottom:4px;">B. SEED DATA ADMIN (REKOMENDASI)</div>
                <code style="background:transparent; color:var(--text-main); padding:0; font-family:monospace; font-size:0.95rem;">php artisan db:seed</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div style="background:var(--bg-body); padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:12px;">
              <div style="flex:1;">
                <div class="text-xs" style="font-weight:600; color:var(--amber); margin-bottom:4px;">C. SYMBOLIC LINK (WAJIB)</div>
                <code style="background:transparent; color:var(--text-main); padding:0; font-family:monospace; font-size:0.95rem;">php artisan storage:link</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

            <div style="background:var(--bg-body); padding:12px 16px; border-radius:8px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; gap:12px;">
              <div style="flex:1;">
                <div class="text-xs" style="font-weight:600; color:var(--purple); margin-bottom:4px;">D. BERSIHKAN CACHE (OPSIONAL)</div>
                <code style="background:transparent; color:var(--text-main); padding:0; font-family:monospace; font-size:0.95rem;">php artisan optimize:clear</code>
              </div>
              <i class="fa-solid fa-terminal text-muted"></i>
            </div>

          </div>
        </div>
      </div>

      <div class="alert alert-warning" style="background:rgba(255,171,64,0.05); border:1px solid rgba(255,171,64,0.15); color:#d97706; padding:16px; border-radius:10px; margin-top:12px;">
        <div style="display:flex; gap:12px;">
          <i class="fa-solid fa-triangle-exclamation" style="margin-top:3px; font-size:1.1rem;"></i>
          <div class="text-sm">
            <strong style="display:block; margin-bottom:4px;">Peringatan Keamanan</strong>
            Gunakan perintah <code>migrate:fresh</code> dengan sangat hati-hati karena akan menghapus seluruh data yang ada. Lakukan backup database secara rutin untuk keamanan data perusahaan.
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

      <div class="alert alert-warning" style="background:rgba(255,171,64,0.08); border:1px solid rgba(255,171,64,0.2); color:#d97706; padding:16px; border-radius:8px;">
        <div style="display:flex; gap:12px;">
          <i class="fa-solid fa-triangle-exclamation" style="margin-top:3px;"></i>
          <div class="text-sm">
            <strong>Peringatan:</strong> Menjalankan perintah <code>migrate:fresh</code> akan menghapus seluruh data yang ada dan mengosongkan database. Pastikan untuk melakukan backup data penting secara rutin.
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

<style>
.setup-step p { font-size: 0.95rem; margin-bottom: 8px; }
pre { font-family: 'Consolas', 'Monaco', monospace; }
</style>
@endsection

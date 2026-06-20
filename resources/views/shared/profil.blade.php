@extends('layouts.app')
@section('title','Profil Saya')
@section('page-title','Profil Saya')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Profil Saya</span></div>
  <h1>Profil Saya</h1>
  <p class="text-muted">Kelola informasi akun dan data pribadi Anda.</p>
</div>

<div class="animate-slideup">
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:24px;align-items:start;">

  {{-- LEFT: Avatar Card --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    <div class="card" style="border-left: 4px solid var(--teal); background: linear-gradient(to bottom right, rgba(0,201,167,0.05), transparent);">
      <div class="card-body" style="text-align:center;padding:32px 24px;">
        {{-- Avatar --}}
        <div style="position:relative;display:inline-block;margin-bottom:16px;">
          @if($karyawan?->foto)
            <img src="{{ $karyawan->foto_url }}"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;aspect-ratio:1/1;border:3px solid var(--teal);box-shadow:0 0 24px rgba(0,201,167,.3);display:block;"
                 alt="Foto Profil"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--navy-light));display:none;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;border:3px solid var(--teal);box-shadow:0 0 24px rgba(0,201,167,.3);aspect-ratio:1/1;">
              {{ strtoupper(substr($karyawan?->nama_lengkap ?? $user->username, 0, 1)) }}
            </div>
            <form action="{{ route($user->role . '.profil.foto.delete') }}" method="POST" style="position:absolute;top:-5px;right:-5px;">
                @csrf @method('DELETE')
                <button type="submit" style="width:26px;height:26px;border-radius:50%;background:var(--red);border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.65rem;color:white;box-shadow:var(--shadow-sm);" title="Hapus Foto">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
          @else
            <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--navy-light));display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;border:3px solid var(--teal);box-shadow:0 0 24px rgba(0,201,167,.3);aspect-ratio:1/1;">
              {{ strtoupper(substr($karyawan?->nama_lengkap ?? $user->username, 0, 1)) }}
            </div>
          @endif
          @if($karyawan)
          <label for="fotoInput" style="position:absolute;bottom:0;right:0;width:30px;height:30px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;color:var(--navy);box-shadow:var(--shadow-sm);border:2px solid var(--bg-card);" title="Ubah Foto">
            <i class="fa-solid fa-camera"></i>
          </label>
          @endif
        </div>

        <h3 style="margin-bottom:4px;">{{ $karyawan?->nama_lengkap ?? $user->username }}</h3>
        <div class="text-muted text-sm" style="margin-bottom:12px;">{{ $karyawan?->jabatan ?? 'Administrator' }}</div>
        <span class="badge badge-{{ $user->role === 'karyawan' ? 'teal' : ($user->role === 'hrd' ? 'blue' : 'amber') }}">
          {{ ucfirst($user->role) }}
        </span>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-user" style="color:var(--teal);width:16px;"></i>
            <span class="text-sm">ID: {{ $user->username }}</span>
          </div>
          @if($karyawan)
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-phone" style="color:var(--teal);width:16px;"></i>
            <span class="text-sm">{{ $karyawan->nomor_telepon ?? '—' }}</span>
          </div>
          @endif
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-calendar" style="color:var(--teal);width:16px;"></i>
            <span class="text-sm">Terdaftar {{ $user->created_at->translatedFormat('d M Y') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- QR Code Pribadi (Untuk Karyawan & HRD) --}}
    @if(($user->role === 'karyawan' || $user->role === 'hrd') && $qrCode)
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-qrcode text-teal"></i>
        <h3>QR Code Saya</h3>
      </div>
      <div class="card-body" style="text-align:center;">
          <div id="qr-container" style="background:#fff;padding:12px;border-radius:10px;display:inline-block;margin-bottom:12px;box-shadow:var(--shadow-sm);">
            {!! $qrImage !!}
          </div>
          <div class="text-xs text-muted" style="margin-bottom:12px;">
            QR Code unik untuk identifikasi Anda
          </div>
          <div style="display:flex;gap:8px;justify-content:center;">
            <button type="button" onclick="downloadQrPng()" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-download"></i> Unduh PNG
            </button>
          </div>
      </div>
    </div>
    @endif

  </div>

  {{-- RIGHT: Edit Forms --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Tab --}}
    <div class="tabs">
      <button class="tab active" onclick="switchTab('data',this)">Informasi Akun</button>
      <button class="tab"        onclick="switchTab('password',this)">Ubah Password</button>
    </div>

    {{-- Data Pribadi --}}
    <div id="tab-data" class="card">
      <div class="card-header">
        <i class="fa-solid fa-user text-teal"></i>
        <h3>Update Informasi</h3>
      </div>
      <div class="card-body">

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
          <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:20px;">
          <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
        @endif

        @if($karyawan)
        <form method="POST" action="{{ route($user->role . '.profil.update') }}" enctype="multipart/form-data">
          @csrf @method('PUT')

          <input type="file" id="fotoInput" name="foto" accept="image/*" style="display:none;" onchange="this.form.submit()">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama_lengkap" class="form-control"
                     value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Telepon</label>
              <input type="tel" name="nomor_telepon" class="form-control"
                     value="{{ old('nomor_telepon', $karyawan->nomor_telepon) }}"
                     placeholder="08xxxxxxxxxx">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="{{ $user->username }}" disabled
                     style="opacity:.6;cursor:not-allowed;">
            </div>
            <div class="form-group">
              <label class="form-label">Jabatan</label>
              <input type="text" class="form-control" value="{{ $karyawan->jabatan ?? ($user->role === 'karyawan' ? '—' : 'Administrator') }}" disabled
                     style="opacity:.6;cursor:not-allowed;">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </form>
        @else
        <div class="text-center py-4">
          <p class="text-muted">Gunakan tab <strong>Ubah Password</strong> untuk mengamankan akun Anda.</p>
        </div>
        @endif
      </div>
    </div>

    {{-- Ubah Password --}}
    <div id="tab-password" class="card" style="display:none;">
      <div class="card-header">
        <i class="fa-solid fa-lock text-teal"></i>
        <h3>Ganti Password</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route($user->role . '.password.update') }}">
          @csrf @method('PATCH')

          <div class="form-group">
            <label class="form-label">Password Saat Ini</label>
            <input type="password" name="password_lama" class="form-control" required>
          </div>

          <div class="form-group">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control" required minlength="8">
          </div>

          <div class="form-group">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="password_confirmation" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-key"></i> Update Password
          </button>
        </form>
      </div>
    </div>

    {{-- Statistik Singkat (Karyawan & HRD) --}}
    @if(($user->role === 'karyawan' || $user->role === 'hrd') && !empty($stats))
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-chart-bar text-teal"></i>
        <h3>Statistik Bulan Ini</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(80px, 1fr));gap:10px;text-align:center;">
          <div style="background:rgba(0,201,167,.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba(0,201,167,.15);">
            <div style="font-size:1.4rem;font-weight:800;color:var(--teal);">{{ $stats['hadir'] }}</div>
            <div class="text-xs text-muted">Hadir</div>
          </div>
          <div style="background:rgba(0,224,150,.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba(0,224,150,.15);">
            <div style="font-size:1.4rem;font-weight:800;color:var(--green);">{{ $stats['tepat_waktu'] }}</div>
            <div class="text-xs text-muted">Tepat</div>
          </div>
          <div style="background:rgba(255,171,64,.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba(255,171,64,.15);">
            <div style="font-size:1.4rem;font-weight:800;color:var(--amber);">{{ $stats['terlambat'] }}</div>
            <div class="text-xs text-muted">Lambat</div>
          </div>
          <div style="background:rgba(0,123,255,.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba(0,123,255,.15);">
            <div style="font-size:1.4rem;font-weight:800;color:var(--blue);">{{ $stats['izin'] }}</div>
            <div class="text-xs text-muted">Izin</div>
          </div>
          <div style="background:rgba(255,71,87,.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba(255,71,87,.15);">
            <div style="font-size:1.4rem;font-weight:800;color:var(--red);">{{ $stats['alpa'] }}</div>
            <div class="text-xs text-muted">Alpa</div>
          </div>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
</div>

<script>
function switchTab(tab, btn) {
  document.getElementById('tab-data').style.display     = tab === 'data'     ? 'block' : 'none';
  document.getElementById('tab-password').style.display = tab === 'password' ? 'block' : 'none';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}

/* ── Download QR as PNG (converts inline SVG via Canvas) ── */
function downloadQrPng() {
  var container = document.getElementById('qr-container');
  if (!container) return;
  var svg = container.querySelector('svg');
  if (!svg) { alert('QR Code tidak ditemukan.'); return; }

  var serializer = new XMLSerializer();
  var svgString = serializer.serializeToString(svg);
  var svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
  var url = URL.createObjectURL(svgBlob);

  var img = new Image();
  img.onload = function() {
    var canvas = document.createElement('canvas');
    var size = 512;
    canvas.width = size;
    canvas.height = size;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, size, size);
    ctx.drawImage(img, 0, 0, size, size);
    URL.revokeObjectURL(url);

    canvas.toBlob(function(blob) {
      if (!blob) { alert('Gagal membuat PNG.'); return; }
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'qr-profil.png';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }, 'image/png');
  };
  img.onerror = function() {
    URL.revokeObjectURL(url);
    alert('Gagal memuat QR Code.');
  };
  img.src = url;
}
</script>
@endsection

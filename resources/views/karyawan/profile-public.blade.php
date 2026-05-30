<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil {{ $karyawan->nama_lengkap }}</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',system-ui,sans-serif; background:linear-gradient(135deg,#f0fdfa 0%,#ecfeff 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
    .card { background:#fff; border-radius:24px; box-shadow:0 20px 60px rgba(0,0,0,.1); padding:40px; max-width:420px; width:100%; text-align:center; }
    .avatar { width:120px; height:120px; border-radius:50%; object-fit:cover; margin:0 auto 20px; border:4px solid #14b8a6; box-shadow:0 0 30px rgba(20,184,166,.3); }
    .avatar-placeholder { width:120px; height:120px; border-radius:50%; background:linear-gradient(135deg,#14b8a6,#0ea5e9); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:3rem; font-weight:800; color:#fff; border:4px solid #14b8a6; box-shadow:0 0 30px rgba(20,184,166,.3); }
    h1 { font-size:1.75rem; color:#0f172a; margin-bottom:8px; }
    .role { display:inline-block; background:linear-gradient(135deg,#14b8a6,#0ea5e9); color:#fff; padding:6px 16px; border-radius:9999px; font-size:.875rem; font-weight:600; margin-bottom:20px; }
    .info { text-align:left; background:#f8fafc; border-radius:16px; padding:24px; margin-top:24px; }
    .info-item { display:flex; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #e2e8f0; }
    .info-item:last-child { border-bottom:none; }
    .info-item i { width:20px; color:#14b8a6; font-size:1.1rem; }
    .info-item span { color:#475569; font-size:.95rem; }
    .status { display:flex; gap:8px; align-items:center; }
    .status-dot { width:10px; height:10px; border-radius:50%; }
    .status-dot.aktif { background:#22c55e; }
    .status-dot.nonaktif { background:#ef4444; }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <div class="card">
    @if($karyawan->foto)
      <img src="{{ asset('storage/'.$karyawan->foto) }}" class="avatar" alt="Foto {{ $karyawan->nama_lengkap }}">
    @else
      <div class="avatar-placeholder">
        {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
      </div>
    @endif

    <h1>{{ $karyawan->nama_lengkap }}</h1>
    <span class="role">{{ $karyawan->jabatan ?? 'Karyawan' }}</span>

    <div class="info">
      <div class="info-item">
        <i class="fa-solid fa-phone"></i>
        <span>{{ $karyawan->nomor_telepon ?? '—' }}</span>
      </div>
      <div class="info-item">
        <i class="fa-solid fa-calendar"></i>
        <span>Bergabung {{ $karyawan->created_at?->translatedFormat('F Y') ?? '—' }}</span>
      </div>
      <div class="info-item">
        <i class="fa-solid fa-circle"></i>
        <div class="status">
          <span class="status-dot {{ $karyawan->status === 'aktif' ? 'aktif' : 'nonaktif' }}"></span>
          <span>{{ ucfirst($karyawan->status ?? 'aktif') }}</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

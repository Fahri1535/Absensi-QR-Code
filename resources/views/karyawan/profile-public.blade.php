<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil {{ $karyawan->nama_lengkap }}</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    html { font-size:16px; scroll-behavior:smooth; background-color:#0F172A; }
    body {
      font-family:'DM Sans','Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
      background:#0F172A;
      color:#F1F5F9;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
      position:relative;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    body::before {
      content:'';position:fixed;inset:0;z-index:0;pointer-events:none;
      background-image:
        linear-gradient(rgba(37,99,235,0.04) 1px,transparent 1px),
        linear-gradient(90deg,rgba(37,99,235,0.04) 1px,transparent 1px);
      background-size:48px 48px;
    }
    .card {
      background:rgba(30,41,59,0.85);
      border-radius:14px;
      box-shadow:0 8px 32px rgba(0,0,0,0.4);
      padding:40px;
      max-width:420px;
      width:100%;
      text-align:center;
      border:1px solid rgba(59,130,246,0.18);
      position:relative;
      z-index:1;
    }
    .avatar {
      width:120px;
      height:120px;
      border-radius:50%;
      object-fit:cover;
      margin:0 auto 20px;
      border:3px solid #00C9A7;
      box-shadow:0 0 24px rgba(0,201,167,.3);
    }
    .avatar-placeholder {
      width:120px;
      height:120px;
      border-radius:50%;
      background:linear-gradient(135deg,#00C9A7,#3B82F6);
      display:flex;
      align-items:center;
      justify-content:center;
      margin:0 auto 20px;
      font-size:3rem;
      font-weight:800;
      color:#fff;
      border:3px solid #00C9A7;
      box-shadow:0 0 24px rgba(0,201,167,.3);
    }
    h1 {
      font-size:1.75rem;
      color:#F1F5F9;
      margin-bottom:8px;
      font-weight:700;
    }
    .role {
      display:inline-block;
      background:linear-gradient(135deg,#00C9A7,#3B82F6);
      color:#fff;
      padding:6px 16px;
      border-radius:9999px;
      font-size:.875rem;
      font-weight:600;
      margin-bottom:20px;
    }
    .info {
      text-align:left;
      background:rgba(15,23,42,0.6);
      border-radius:8px;
      padding:24px;
      margin-top:24px;
      border:1px solid rgba(59,130,246,0.18);
    }
    .info-item {
      display:flex;
      gap:12px;
      align-items:center;
      padding:12px 0;
      border-bottom:1px solid rgba(59,130,246,0.18);
    }
    .info-item:last-child { border-bottom:none; }
    .info-item i { width:20px; color:#00C9A7; font-size:1.1rem; }
    .info-item span { color:#94A3B8; font-size:.95rem; }
    .status { display:flex; gap:8px; align-items:center; }
    .status-dot { width:10px; height:10px; border-radius:50%; }
    .status-dot.aktif { background:#00E096; }
    .status-dot.nonaktif { background:#FF5370; }
  </style>
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

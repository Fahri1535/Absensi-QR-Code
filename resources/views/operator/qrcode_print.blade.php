<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cetak QR — {{ $tipe }}</title>
  <style>
    body { font-family: system-ui, sans-serif; text-align: center; padding: 24px; }
    .box { display: inline-block; padding: 24px; border: 1px solid #ccc; border-radius: 12px; }
    h1 { font-size: 1.1rem; margin-bottom: 8px; }
    p { color: #555; font-size: .85rem; }
  </style>
</head>
<body onload="window.print()">
  <div class="box">
    <h1>QR Presensi {{ ucfirst($tipe) }}</h1>
    <p>Pindai dengan kamera — akan dibuka halaman login jika belum masuk.</p>
    <div style="background:#fff;padding:16px;display:inline-block;margin-top:12px;">
      {!! $image !!}
    </div>
    <p style="margin-top:16px;font-size:.75rem;word-break:break-all;">{{ $qr->presensiScanUrl() }}</p>
  </div>
</body>
</html>

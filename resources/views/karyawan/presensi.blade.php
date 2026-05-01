@extends('layouts.app')

@section('title', 'Presensi QR Code')
@section('page-title', 'Presensi QR Code')

@push('styles')
<style>
.presensi-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
  align-items: start;
}

@media (max-width: 900px) {
  .presensi-grid { grid-template-columns: 1fr; }
}

/* QR Scanner Styles */
.scanner-container {
  position: relative;
  width: 100%; aspect-ratio: 1;
  background: #000; border-radius: 16px; overflow: hidden;
  max-width: 400px; margin: 0 auto;
}

#qr-video { width: 100%; height: 100%; object-fit: cover; display: block; }

.scan-overlay {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
}

.scan-area {
  width: 65%; aspect-ratio: 1;
  position: relative;
  box-shadow: 0 0 0 9999px rgba(0,0,0,.55);
  border-radius: 4px;
}

.corner {
  position: absolute; width: 28px; height: 28px;
  border-color: var(--teal); border-style: solid;
}
.corner.tl { top:-2px; left:-2px; border-width:3px 0 0 3px; border-radius:4px 0 0 0; }
.corner.tr { top:-2px; right:-2px; border-width:3px 3px 0 0; border-radius:0 4px 0 0; }
.corner.bl { bottom:-2px; left:-2px; border-width:0 0 3px 3px; border-radius:0 0 0 4px; }
.corner.br { bottom:-2px; right:-2px; border-width:0 3px 3px 0; border-radius:0 0 4px 0; }

.scan-laser {
  position: absolute; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--teal), transparent);
  box-shadow: 0 0 8px var(--teal);
  animation: laser 2.5s ease-in-out infinite;
}
@keyframes laser { 0%,100%{top:4%} 50%{top:94%} }

.scanner-placeholder {
  width: 100%; aspect-ratio: 1; max-width: 400px; margin: 0 auto;
  background: var(--card-bg); border: 2px dashed var(--border);
  border-radius: 16px; display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 14px;
}
.scanner-placeholder i { font-size: 3rem; color: var(--muted); }
.scanner-placeholder p { color: var(--muted); font-size: .9rem; text-align: center; }

/* Success animation */
.success-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 300;
  background: rgba(0,0,0,.8); backdrop-filter: blur(8px);
  align-items: center; justify-content: center;
}
.success-overlay.show { display: flex; animation: fadeIn .3s ease; }

.success-box {
  background: var(--navy-mid); border: 1px solid var(--teal);
  border-radius: 24px; padding: 40px; text-align: center;
  max-width: 340px; width: 90%;
  animation: slideUp .4s cubic-bezier(.4,0,.2,1);
}
.success-icon {
  width: 80px; height: 80px; border-radius: 50%;
  background: var(--teal-glow); border: 2px solid var(--teal);
  display: flex; align-items: center; justify-content: center;
  font-size: 2.5rem; margin: 0 auto 20px;
  animation: popIn .5s .1s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes popIn { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }

/* Window Status Bar */
.window-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px;
  background: rgba(0,0,0,.2); border-radius: var(--radius-sm);
  margin-bottom: 16px;
}

.window-indicator {
  display: flex; align-items: center; gap: 8px; font-size: .85rem;
}
.window-dot { width: 10px; height: 10px; border-radius: 50%; animation: pulse 2s infinite; }
.window-dot.open  { background: var(--green); box-shadow: 0 0 8px var(--green); }
.window-dot.closed { background: var(--red); box-shadow: 0 0 8px var(--red); animation: none; }
.window-dot.soon  { background: var(--amber); box-shadow: 0 0 8px var(--amber); }
</style>
@endpush

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Presensi QR Code</span></div>
  <h1>Presensi QR Code</h1>
  <p class="text-muted">Arahkan kamera ke QR Code yang tertempel di kantor untuk mencatat presensi.</p>
</div>

<div class="presensi-grid">

  {{-- LEFT: Scanner --}}
  <div>

    {{-- Window Status --}}
    <div class="card mb-4">
      <div class="card-body-sm">
        @php
          $now        = now();
          $jamMasuk   = \Carbon\Carbon::parse($jadwal->jam_masuk ?? '08:00');
          $toleransi  = $jadwal->toleransi_menit ?? 5;
          $batasMasuk = $jamMasuk->copy()->addMinutes($toleransi)->addHours(1); // 1 jam window
          $jamPulang  = \Carbon\Carbon::parse($jadwal->jam_pulang ?? '17:00');
          $batasPulang = $jamPulang->copy()->addHours(1);

          // Determine active window
          $sudahMasuk  = (bool)($presensiHariIni?->jam_datang);
          $sudahPulang = (bool)($presensiHariIni?->jam_pulang);

          $windowMasukOpen  = !$sudahMasuk && $now->between($jamMasuk->copy()->subMinutes(15), $batasMasuk);
          $windowPulangOpen = $sudahMasuk && !$sudahPulang && $now->between($jamPulang->copy()->subMinutes(30), $batasPulang);
        @endphp

        <div class="window-bar">
          <div class="window-indicator">
            <div class="window-dot {{ $windowMasukOpen ? 'open' : ($sudahMasuk ? 'closed' : 'closed') }}"></div>
            <span>Presensi Masuk</span>
          </div>
          <div>
            <span style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--teal);">
              {{ $jamMasuk->format('H:i') }} – {{ $batasMasuk->format('H:i') }}
            </span>
            @if($sudahMasuk)
              <span class="badge badge-green" style="margin-left:8px;">✓ Selesai</span>
            @endif
          </div>
        </div>

        <div class="window-bar" style="margin-bottom:0;">
          <div class="window-indicator">
            <div class="window-dot {{ $windowPulangOpen ? 'open' : ($sudahPulang ? 'closed' : 'closed') }}"></div>
            <span>Presensi Pulang</span>
          </div>
          <div>
            <span style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--teal);">
              {{ $jamPulang->format('H:i') }} – {{ $batasPulang->format('H:i') }}
            </span>
            @if($sudahPulang)
              <span class="badge badge-green" style="margin-left:8px;">✓ Selesai</span>
            @endif
          </div>
        </div>

      </div>
    </div>

    {{-- Scanner Card --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-qrcode text-teal"></i>
        <h3>
          @if(!$sudahMasuk)
            Scan QR — Presensi Masuk
          @elseif(!$sudahPulang)
            Scan QR — Presensi Pulang
          @else
            Presensi Hari Ini Lengkap
          @endif
        </h3>
        <div class="card-actions">
          <div id="scanner-status" class="badge badge-muted">Kamera Mati</div>
        </div>
      </div>
      <div class="card-body">

        @if($sudahMasuk && $sudahPulang)
          {{-- All done --}}
          <div style="text-align:center; padding:40px;">
            <div style="font-size:4rem; margin-bottom:16px;">🎉</div>
            <h3 style="margin-bottom:8px;">Presensi Hari Ini Lengkap!</h3>
            <p class="text-muted">Anda telah melakukan presensi masuk dan pulang.</p>
            <div style="display:flex;justify-content:center;gap:16px;margin-top:20px;">
              <div style="text-align:center;">
                <div style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;color:var(--teal);">
                  {{ \Carbon\Carbon::parse($presensiHariIni->jam_datang)->format('H:i') }}
                </div>
                <div class="text-xs text-muted">Masuk</div>
              </div>
              <div style="color:var(--muted); line-height:2.5rem;">→</div>
              <div style="text-align:center;">
                <div style="font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;color:var(--green);">
                  {{ \Carbon\Carbon::parse($presensiHariIni->jam_pulang)->format('H:i') }}
                </div>
                <div class="text-xs text-muted">Pulang</div>
              </div>
            </div>
          </div>

        @else
          {{-- Scanner --}}
          <div id="scanner-off" class="scanner-placeholder">
            <i class="fa-solid fa-camera"></i>
            <p>Klik tombol di bawah untuk<br>mengaktifkan kamera</p>
          </div>

          <div id="scanner-on" style="display:none;">
            <div class="scanner-container">
              <video id="qr-video" playsinline></video>
              <div class="scan-overlay">
                <div class="scan-area">
                  <div class="corner tl"></div>
                  <div class="corner tr"></div>
                  <div class="corner bl"></div>
                  <div class="corner br"></div>
                  <div class="scan-laser"></div>
                </div>
              </div>
            </div>
          </div>

          <div id="scanner-error" class="alert alert-danger" style="display:none;margin-top:16px;">
            <i class="fa-solid fa-circle-xmark"></i>
            <span id="scanner-error-msg">Terjadi kesalahan</span>
          </div>

          <div id="scan-result" class="alert" style="display:none; margin-top:16px;"></div>

          <div style="display:flex;gap:10px;margin-top:20px; justify-content:center;">
            <button id="btn-start-scan" class="btn btn-primary btn-lg" onclick="startScanner()">
              <i class="fa-solid fa-camera"></i> Aktifkan Kamera
            </button>
            <button id="btn-stop-scan" class="btn btn-outline" style="display:none;" onclick="stopScanner()">
              <i class="fa-solid fa-stop"></i> Berhenti
            </button>
          </div>

          <p class="text-muted text-sm" style="text-align:center; margin-top:12px;">
            <i class="fa-solid fa-circle-info"></i>
            Pastikan QR Code terlihat jelas dalam bingkai kamera
          </p>
        @endif

      </div>
    </div>
  </div>

  {{-- RIGHT: Info Panel --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Clock --}}
    <div class="card">
      <div class="card-body clock-card">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);">Waktu Sekarang</div>
        <div class="clock-display" id="live-clock">--:--</div>
        <div class="clock-date" id="live-date">—</div>
        <hr class="divider">
        <div class="attendance-status">
          <div class="att-dot">
            <div class="dot {{ $sudahMasuk ? 'done' : '' }}"></div>
            <div class="label">Masuk</div>
          </div>
          <div style="flex:1;height:2px;background:var(--border);margin-top:5px;align-self:center;"></div>
          <div class="att-dot">
            <div class="dot {{ $sudahPulang ? 'done' : '' }}"></div>
            <div class="label">Pulang</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Jadwal Info --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-calendar-days text-teal"></i>
        <h3>Jadwal Kerja Hari Ini</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="text-muted text-sm">Jam Masuk</span>
            <span style="font-weight:600;color:var(--teal);">{{ $jadwal?->jam_masuk ?? '08:00' }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="text-muted text-sm">Jam Pulang</span>
            <span style="font-weight:600;color:var(--green);">{{ $jadwal?->jam_pulang ?? '17:00' }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="text-muted text-sm">Toleransi</span>
            <span style="font-weight:600;color:var(--amber);">{{ $jadwal?->toleransi_menit ?? 5 }} menit</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="text-muted text-sm">Hari Kerja</span>
            <span style="font-weight:600;font-size:.8rem;">{{ $jadwal?->hari_kerja ?? 'Senin – Jumat' }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Cara Presensi --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-circle-question text-teal"></i>
        <h3>Cara Presensi</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:flex;flex-direction:column;gap:14px;">
          @foreach([
            ['1', 'Klik tombol "Aktifkan Kamera"', 'fa-camera'],
            ['2', 'Izinkan akses kamera browser', 'fa-shield-halved'],
            ['3', 'Arahkan ke QR Code di kantor', 'fa-qrcode'],
            ['4', 'Tunggu konfirmasi presensi', 'fa-circle-check'],
          ] as [$no, $text, $icon])
          <div style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:28px;height:28px;border-radius:50%;background:var(--teal-glow);border:1px solid rgba(0,201,167,.25);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--teal);flex-shrink:0;">{{ $no }}</div>
            <div>
              <i class="fa-solid {{ $icon }}" style="color:var(--muted);margin-right:6px;"></i>
              <span style="font-size:.85rem;">{{ $text }}</span>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Success Modal --}}
<div class="success-overlay" id="success-overlay">
  <div class="success-box">
    <div class="success-icon" id="success-icon">✅</div>
    <h2 id="success-title">Presensi Berhasil!</h2>
    <p class="text-muted" id="success-desc" style="margin:10px 0 4px;">Jam masuk tercatat</p>
    <div style="font-family:'Syne',sans-serif;font-size:2.5rem;font-weight:800;color:var(--teal);margin:10px 0;" id="success-time">--:--</div>
    <div class="badge badge-green" id="success-status" style="margin-bottom:24px;">Tepat Waktu</div>
    <button class="btn btn-primary btn-full" onclick="document.getElementById('success-overlay').classList.remove('show'); window.location.reload();">
      OK, Tutup
    </button>
  </div>
</div>

@endsection

@push('scripts')
{{-- jsQR library for QR decoding --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
let videoStream = null;
let scanning    = false;
let animFrame   = null;

// Live clock
function tick() {
  const now = new Date();
  document.getElementById('live-clock').textContent =
    now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
  document.getElementById('live-date').textContent =
    now.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
}
tick(); setInterval(tick, 1000);

async function startScanner() {
  document.getElementById('scanner-error').style.display = 'none';
  document.getElementById('scan-result').style.display    = 'none';

  try {
    videoStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment', width: {ideal:640}, height: {ideal:640} }
    });

    const video = document.getElementById('qr-video');
    video.srcObject = videoStream;
    await video.play();

    document.getElementById('scanner-off').style.display  = 'none';
    document.getElementById('scanner-on').style.display   = 'block';
    document.getElementById('btn-start-scan').style.display = 'none';
    document.getElementById('btn-stop-scan').style.display  = 'inline-flex';
    document.getElementById('scanner-status').textContent = 'Scanning...';
    document.getElementById('scanner-status').className   = 'badge badge-teal';

    scanning = true;
    scanFrame();

  } catch(e) {
    showError('Tidak dapat mengakses kamera: ' + e.message);
  }
}

function stopScanner() {
  scanning = false;
  if(animFrame) cancelAnimationFrame(animFrame);
  if(videoStream) videoStream.getTracks().forEach(t => t.stop());

  document.getElementById('scanner-on').style.display   = 'none';
  document.getElementById('scanner-off').style.display  = 'flex';
  document.getElementById('btn-start-scan').style.display = 'inline-flex';
  document.getElementById('btn-stop-scan').style.display  = 'none';
  document.getElementById('scanner-status').textContent = 'Kamera Mati';
  document.getElementById('scanner-status').className   = 'badge badge-muted';
}

function scanFrame() {
  if(!scanning) return;
  const video  = document.getElementById('qr-video');
  if(video.readyState === video.HAVE_ENOUGH_DATA) {
    const canvas = document.createElement('canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(img.data, img.width, img.height, {inversionAttempts:'dontInvert'});
    if(code) {
      scanning = false;
      submitPresensi(code.data);
      return;
    }
  }
  animFrame = requestAnimationFrame(scanFrame);
}

async function submitPresensi(qrData) {
  document.getElementById('scanner-status').textContent = 'Memproses...';
  document.getElementById('scanner-status').className   = 'badge badge-amber';

  try {
    const res = await fetch('{{ route("karyawan.presensi.scan") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ qr_data: qrData })
    });

    const data = await res.json();

    if(data.success) {
      stopScanner();
      // Show success modal
      document.getElementById('success-title').textContent = data.type === 'masuk' ? 'Presensi Masuk Berhasil!' : 'Presensi Pulang Berhasil!';
      document.getElementById('success-desc').textContent  = data.type === 'masuk' ? 'Jam masuk Anda tercatat' : 'Jam pulang Anda tercatat';
      document.getElementById('success-time').textContent  = data.jam;
      document.getElementById('success-status').textContent = data.status_label;
      document.getElementById('success-status').className  = 'badge badge-' + (data.status === 'tepat_waktu' ? 'green' : 'amber');
      document.getElementById('success-overlay').classList.add('show');
    } else {
      showScanResult('danger', data.message || 'Presensi gagal diproses.');
      setTimeout(() => { scanning = true; scanFrame(); }, 2000);
    }
  } catch(e) {
    showScanResult('danger', 'Terjadi kesalahan koneksi. Coba lagi.');
    setTimeout(() => { scanning = true; scanFrame(); }, 2000);
  }
}

function showError(msg) {
  const el = document.getElementById('scanner-error');
  document.getElementById('scanner-error-msg').textContent = msg;
  el.style.display = 'flex';
}

function showScanResult(type, msg) {
  const el = document.getElementById('scan-result');
  el.className = 'alert alert-' + type;
  el.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'circle-xmark'}"></i> ${msg}`;
  el.style.display = 'flex';
}
</script>
<style>
  body, .card, .card-body, .page-content, 
  .table, .table th, .table td, .badge, .btn,
  h1, h2, h3, h4, p, span, div {
    font-family: 'DM Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
    letter-spacing: normal !important;
  }
</style>

@endpush

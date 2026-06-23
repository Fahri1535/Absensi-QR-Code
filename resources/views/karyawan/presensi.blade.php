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
  background: var(--bg-soft); border-radius: var(--radius-sm);
  margin-bottom: 16px;
}

.window-indicator {
  display: flex; align-items: center; gap: 8px; font-size: .85rem;
}
.window-dot { width: 10px; height: 10px; border-radius: 50%; animation: pulse 2s infinite; }
.window-dot.open  { background: var(--green); box-shadow: 0 0 8px var(--green); }
.window-dot.closed { background: var(--red); box-shadow: 0 0 8px var(--red); animation: none; }
.window-dot.soon  { background: var(--amber); box-shadow: 0 0 8px var(--amber); }

/* ── Mobile (tablet & phone) ───────────────────────────────── */
/* Hasil scan & window-bar harus tetap nyaman dibaca di layar sempit.
   Termasuk pesan error dinamis (jam masuk/pulang) yang bisa panjang. */
@media (max-width: 900px) {
  .presensi-grid { grid-template-columns: 1fr; gap: 16px; }
  .scanner-container,
  .scanner-placeholder { max-width: 100%; }
}

@media (max-width: 640px) {
  /* Window-bar: tumpuk vertikal, jam di bawah label agar tidak terpotong */
  .window-bar {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
    padding: 12px 14px;
  }
  .window-bar > div:last-child {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
  }
  /* Waktu window: kecilkan sedikit agar muat "15:15 – 16:40" + badge */
  .window-bar span[style*="font-weight:700"] { font-size: .85rem; }

  /* Hasil scan (alert dinamis): pastikan teks panjang terbungkus rapi */
  #scan-result {
    font-size: .82rem;
    line-height: 1.5;
    align-items: flex-start;
    word-break: break-word;
    overflow-wrap: anywhere;
  }
  #scan-result i { margin-top: 2px; }

  /* Tombol scan: minimal 48px (rekomendasi touch target a11y) & full-width */
  #btn-start-scan,
  #btn-stop-scan {
    min-height: 48px !important;
    flex: 1 1 100%;
    justify-content: center;
  }
}

/* Layar sangat sempit (≤400px) — paksa tombol presensi full-width bertumpuk */
@media (max-width: 400px) {
  #btn-start-scan,
  #btn-stop-scan { flex: 1 1 100%; }
}

/* Sukses modal: pastikan tidak overflow di ponsel kecil */
@media (max-width: 480px) {
  .success-box { padding: 28px 20px; max-width: 92%; }
  .success-icon { width: 64px; height: 64px; font-size: 2rem; }
  .success-box h2 { font-size: 1.25rem; }
}
</style>
@endpush

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Presensi QR Code</span></div>
  <h1>Presensi QR Code</h1>
  <p class="text-muted">Anda bisa memindai QR dari aplikasi kamera atau Google Lens — tautan akan mengarahkan ke login jika belum masuk. Pastikan izin lokasi aktif jika Operator mengatur radius kantor.</p>
</div>

@if(!empty($sedangAlpaHariIni))
<div class="alert alert-danger alert-permanent" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-user-xmark" style="font-size:1.5rem;"></i>
    <div>
      <strong>Anda tercatat alpa hari ini!</strong><br>
      Anda tidak hadir tanpa keterangan dan jam presensi masuk telah berakhir. Presensi untuk hari ini tidak dapat diproses. Silakan hubungi HRD untuk klarifikasi ketidakhadiran Anda.
    </div>
  </div>
</div>
@endif

@if(!empty($izinPending))
<div class="alert alert-warning alert-permanent" style="margin-bottom:16px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-clock" style="font-size:1.5rem;"></i>
    <div>
      <strong>Anda memiliki izin yang menunggu persetujuan!</strong><br>
      Izin {{ str_replace('_', ' ', $izinPending->jenis_izin) }} dari {{ $izinPending->tanggal_mulai->format('d M Y') }} sampai {{ $izinPending->tanggal_selesai->format('d M Y') }} masih dalam proses. Presensi tetap dapat dilakukan selama izin belum disetujui.
    </div>
  </div>
</div>
@endif

@if(!empty($geoRequired))
<div class="alert alert-success alert-permanent" style="margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;">
  <i class="fa-solid fa-location-dot" style="margin-top:2px;"></i>
  <span><strong>Lokasi wajib:</strong> presensi hanya dapat dilakukan dalam radius kantor yang ditentukan di menu Operator → Jadwal Kerja &amp; Lokasi Kantor.</span>
</div>
@endif

@if(!empty($pendingQrToken) && empty($sedangAlpaHariIni) && empty($sedangIzin))
<div class="alert alert-success" style="margin-bottom:16px;display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
  <span><i class="fa-solid fa-link"></i> Anda membuka halaman dari tautan QR. Izinkan lokasi jika diminta, lalu gunakan tombol di bawah atau aktifkan kamera untuk memindai ulang.</span>
  <button type="button" class="btn btn-primary btn-sm" onclick="processPendingQrFromLink()">
    <i class="fa-solid fa-fingerprint"></i> Presensi dengan QR tautan
  </button>
</div>
@endif

<div class="presensi-grid">

  {{-- LEFT: Scanner --}}
  <div>

    {{-- Window Status --}}
    <div class="card mb-4">
      <div class="card-body-sm">
        @php
          $now   = now();
          $win   = $jadwal->presensiWindows($now);
          $windowMasukBuka  = $win['masuk_buka'];
          $windowMasukTutup = $win['masuk_tutup'];
          $windowPulangBuka  = $win['pulang_buka'];
          $windowPulangTutup = $win['pulang_tutup'];

          // Determine active window
          $sudahMasuk  = (bool)($presensiHariIni?->jam_datang);
          $sudahPulang = (bool)($presensiHariIni?->jam_pulang);

          $windowMasukOpen  = !$sudahMasuk && $now->between($windowMasukBuka, $windowMasukTutup);
          $windowPulangOpen = $sudahMasuk && !$sudahPulang && $now->between($windowPulangBuka, $windowPulangTutup);
        @endphp

        <div class="window-bar">
          <div class="window-indicator">
            <div class="window-dot {{ ($sedangAlpaHariIni || $sedangIzin || !$windowMasukOpen) ? 'closed' : 'open' }}"></div>
            <span>Presensi Masuk</span>
          </div>
          <div>
            <span style="font-family:'DM Sans',sans-serif;font-size:.9rem;font-weight:700;color:var(--teal);">
              {{ $windowMasukBuka->format('H:i') }} – {{ $windowMasukTutup->format('H:i') }}
            </span>
            @if($sedangAlpaHariIni)
              <span class="badge badge-red" style="margin-left:8px;">🚫 Alpa</span>
            @elseif($sedangIzin)
              <span class="badge badge-amber" style="margin-left:8px;">🚫 {{ ucfirst(str_replace('_', ' ', $sedangIzin->jenis_izin)) }}</span>
            @elseif($sudahMasuk)
              <span class="badge badge-green" style="margin-left:8px;">✓ Selesai</span>
            @endif
          </div>
        </div>

        <div class="window-bar" style="margin-bottom:0;">
          <div class="window-indicator">
            <div class="window-dot {{ ($sedangAlpaHariIni || $sedangIzin || !$windowPulangOpen) ? 'closed' : 'open' }}"></div>
            <span>Presensi Pulang</span>
          </div>
          <div>
            <span style="font-family:'DM Sans',sans-serif;font-size:.9rem;font-weight:700;color:var(--teal);">
              {{ $windowPulangBuka->format('H:i') }} – {{ $windowPulangTutup->format('H:i') }}
            </span>
            @if($sedangAlpaHariIni)
              <span class="badge badge-red" style="margin-left:8px;">🚫 Tidak Tersedia</span>
            @elseif($sedangIzin)
              <span class="badge badge-amber" style="margin-left:8px;">🚫 Tidak Tersedia</span>
            @elseif($sudahPulang)
              <span class="badge badge-green" style="margin-left:8px;">✓ Selesai</span>
            @endif
          </div>
        </div>

      </div>
    </div>

    {{-- Scanner Card --}}
    <div class="card" {{ ($sedangAlpaHariIni || $sedangIzin) ? 'style=pointer-events:none;opacity:0.7;' : '' }}>
      <div class="card-header">
        <i class="fa-solid fa-qrcode text-teal"></i>
        <h3>
          @if($sedangAlpaHariIni)
            Presensi Tidak Tersedia
          @elseif($sedangIzin)
            Presensi Tidak Tersedia
          @elseif(!$sudahMasuk)
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

        @if($sedangAlpaHariIni)
          {{-- Tercatat alpa hari ini — scanner diblokir total, tidak bisa diinteraksi --}}
          <div style="text-align:center; padding:40px 24px;">
            <div style="font-size:4rem; margin-bottom:16px;">🚫</div>
            <h3 style="margin-bottom:8px;">Presensi Tidak Dapat Dilakukan</h3>
            <p class="text-muted" style="margin-bottom:20px;">
              Anda tercatat <strong>Alpa</strong> hari ini (tidak hadir tanpa keterangan) dan jam presensi masuk telah berakhir.
              Sistem tidak dapat memproses presensi untuk hari ini.
            </p>
            <div class="alert alert-danger" style="text-align:left;max-width:380px;margin:0 auto;">
              <i class="fa-solid fa-circle-info"></i>
              <span>Hubungi HRD jika Anda merasa ini adalah kesalahan, misalnya karena kendala teknis saat presensi.</span>
            </div>
          </div>

        @elseif($sedangIzin)
          {{-- Sedang izin — scanner diblokir --}}
          @php
            $jenisIzinLabel = ucfirst(str_replace('_', ' ', $sedangIzin->jenis_izin));
            $jenisIzinEmoji = match($sedangIzin->jenis_izin) {
              'sakit'       => '🤒',
              'cuti'        => '🌴',
              'tugas_luar'  => '💼',
              default       => '🏖',
            };
            $tglMulai  = \Carbon\Carbon::parse($sedangIzin->tanggal_mulai)->isoFormat('D MMM YYYY');
            $tglSelesai = \Carbon\Carbon::parse($sedangIzin->tanggal_selesai)->isoFormat('D MMM YYYY');
          @endphp
          <div style="text-align:center; padding:40px 24px;">
            <div style="font-size:4rem; margin-bottom:16px;">{{ $jenisIzinEmoji }}</div>
            <h3 style="margin-bottom:8px;">Presensi Tidak Dapat Dilakukan</h3>
            <p class="text-muted" style="margin-bottom:20px;">
              Anda tercatat <strong>{{ $jenisIzinLabel }}</strong> hari ini
              ({{ $tglMulai }}
              @if($tglMulai !== $tglSelesai)
                – {{ $tglSelesai }}
              @endif
              ), sehingga tidak dapat melakukan presensi.
            </p>
            @if($sedangIzin->keterangan)
              <div class="alert alert-info" style="text-align:left;max-width:340px;margin:0 auto;">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ $sedangIzin->keterangan }}</span>
              </div>
            @endif
          </div>

        @elseif($sudahMasuk && $sudahPulang)
          {{-- All done --}}
          <div style="text-align:center; padding:40px;">
            <div style="font-size:4rem; margin-bottom:16px;">🎉</div>
            <h3 style="margin-bottom:8px;">Presensi Hari Ini Lengkap!</h3>
            <p class="text-muted">Anda telah melakukan presensi masuk dan pulang.</p>
            <div style="display:flex;justify-content:center;gap:16px;margin-top:20px;">
              <div style="text-align:center;">
                <div style="font-family:'DM Sans',sans-serif;font-size:1.5rem;font-weight:800;color:var(--teal);">
                  {{ \Carbon\Carbon::parse($presensiHariIni->jam_datang)->format('H:i') }}
                </div>
                <div class="text-xs text-muted">Masuk</div>
              </div>
              <div style="color:var(--muted); line-height:2.5rem;">→</div>
              <div style="text-align:center;">
                <div style="font-family:'DM Sans',sans-serif;font-size:1.5rem;font-weight:800;color:var(--green);">
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

          {{-- Scan result container (class alert ditambahkan dinamis agar tidak kena auto-dismiss app.js) --}}
          <div id="scan-result" style="display:none; margin-top:16px;"></div>

          <div style="display:flex;gap:10px;margin-top:20px; justify-content:center; flex-wrap:wrap;">
            <button id="btn-start-scan" class="btn btn-primary btn-lg" onclick="startScanner()" touch-action="manipulation" style="min-height:48px;">
              <i class="fa-solid fa-camera"></i> Aktifkan Kamera
            </button>
            <button id="btn-stop-scan" class="btn btn-outline" onclick="stopScanner()" touch-action="manipulation" style="display:none;min-height:48px;">
              <i class="fa-solid fa-stop"></i> Berhenti
            </button>
          </div>

          <p class="text-muted text-sm" style="text-align:center; margin-top:12px;">
            <i class="fa-solid fa-circle-info"></i>
            QR cetak berisi tautan — bisa dibuka dari Google Lens lalu login; setelah masuk, gunakan tombol presensi dari tautan atau pindai lagi dengan kamera di halaman ini.
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
            <span style="font-weight:600;color:var(--teal);">{{ $jadwal?->jam_masuk ? \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i') : '08:00' }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span class="text-muted text-sm">Jam Pulang</span>
            <span style="font-weight:600;color:var(--green);">{{ $jadwal?->jam_pulang ? \Carbon\Carbon::parse($jadwal->jam_pulang)->format('H:i') : '17:00' }}</span>
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

    @include('partials.kontak-admin-card')

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
    <div style="font-family:'DM Sans',sans-serif;font-size:2.5rem;font-weight:800;color:var(--teal);margin:10px 0;" id="success-time">--:--</div>
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
const GEO_REQUIRED = @json(!empty($geoRequired));
const PENDING_QR_TOKEN = @json($pendingQrToken ?? null);
const PRESENSI_BLOCKED = @json(!empty($sedangAlpaHariIni) || !empty($sedangIzin));

let videoStream = null;
let scanning    = false;
let animFrame   = null;
let isStarting  = false; // Flag to prevent concurrent starts

function normalizeQrPayload(raw) {
  raw = String(raw || '').trim();
  if (!raw) return raw;
  if (raw.includes('http://') || raw.includes('https://')) {
    try {
      const u = new URL(raw);
      const t = u.searchParams.get('t');
      if (t) return t;
    } catch (e) { /* ignore */ }
  }
  return raw;
}

function getPositionOptional() {
  return new Promise((resolve, reject) => {
    if (!GEO_REQUIRED) {
      resolve({ latitude: null, longitude: null });
      return;
    }
    if (!navigator.geolocation) {
      reject(new Error('Peramban tidak mendukung lokasi GPS.'));
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => resolve({
        latitude: pos.coords.latitude,
        longitude: pos.coords.longitude,
      }),
      () => reject(new Error('Izin lokasi diperlukan untuk presensi dalam radius kantor.')),
      { enableHighAccuracy: true, timeout: 18000, maximumAge: 0 }
    );
  });
}

document.addEventListener('DOMContentLoaded', () => {
  if (PENDING_QR_TOKEN && !GEO_REQUIRED && !PRESENSI_BLOCKED) {
    processPendingQrFromLink();
  }
});

async function processPendingQrFromLink() {
  if (!PENDING_QR_TOKEN || PRESENSI_BLOCKED) return;
  try {
    const loc = await getPositionOptional();
    await submitPresensiWithCoords(PENDING_QR_TOKEN, loc.latitude, loc.longitude);
  } catch (e) {
    showScanResult('danger', e.message || 'Gagal memproses QR dari tautan.');
  }
}

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
  if (isStarting) return;
  isStarting = true;

  console.log('[Scanner] Starting scanner...');
  document.getElementById('scanner-error').style.display = 'none';
  document.getElementById('scan-result').style.display    = 'none';

  // Check HTTPS (required for getUserMedia)
  if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    showError('Akses kamera hanya tersedia di HTTPS. Pastikan Anda mengakses situs melalui HTTPS.');
    isStarting = false;
    return;
  }

  // 1. Cleanup existing stream thoroughly
  cleanupStream();

  // Small delay to let OS fully release camera hardware
  await new Promise(r => setTimeout(r, 300));

  try {
    console.log('[Scanner] Requesting camera permission...');
    // Try back camera first, fallback to front
    let stream;
    try {
      console.log('[Scanner] Trying back camera first...');
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' }, width: {ideal:640}, height: {ideal:640} }
      });
    } catch (backErr) {
      console.log('[Scanner] Back camera failed, trying front camera...', backErr);
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: {ideal:640}, height: {ideal:640} }
      });
    }

    console.log('[Scanner] Camera permission granted!');
    videoStream = stream;
    const video = document.getElementById('qr-video');
    video.srcObject = stream;
    await video.play();
    console.log('[Scanner] Video playing!');

    document.getElementById('scanner-off').style.display  = 'none';
    document.getElementById('scanner-on').style.display   = 'block';
    document.getElementById('btn-start-scan').style.display = 'none';
    document.getElementById('btn-stop-scan').style.display  = 'inline-flex';
    document.getElementById('scanner-status').textContent = 'Scanning...';
    document.getElementById('scanner-status').className   = 'badge badge-teal';

    scanning = true;
    scanFrame();

  } catch(e) {
    console.error('[Scanner] Error:', e);
    let msg = e.message;
    if (e.name === 'NotReadableError' || e.name === 'TrackStartError') {
      // Retry once after delay — camera may still be releasing
      try {
        await new Promise(r => setTimeout(r, 800));
        cleanupStream();
        const retryStream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: 'environment' }, width: {ideal:640}, height: {ideal:640} }
        });
        videoStream = retryStream;
        const video = document.getElementById('qr-video');
        video.srcObject = retryStream;
        await video.play();

        document.getElementById('scanner-off').style.display  = 'none';
        document.getElementById('scanner-on').style.display   = 'block';
        document.getElementById('btn-start-scan').style.display = 'none';
        document.getElementById('btn-stop-scan').style.display  = 'inline-flex';
        document.getElementById('scanner-status').textContent = 'Scanning...';
        document.getElementById('scanner-status').className   = 'badge badge-teal';

        scanning = true;
        scanFrame();
      } catch (retryErr) {
        msg = 'Kamera masih digunakan oleh proses lain. Tutup aplikasi kamera atau browser lain, lalu coba lagi.';
        showError(msg);
      }
    } else if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
      msg = 'Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser Anda.';
      showError(msg);
    } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
      msg = 'Perangkat kamera tidak ditemukan. Pastikan perangkat Anda memiliki kamera.';
      showError(msg);
    } else {
      showError(msg);
    }
  } finally {
    isStarting = false;
  }
}

/** Fully release camera stream and reset video element */
function cleanupStream() {
  if (videoStream) {
    videoStream.getTracks().forEach(t => { t.stop(); t.enabled = false; });
    videoStream = null;
  }
  const video = document.getElementById('qr-video');
  if (video) {
    video.pause();
    video.srcObject = null;
    video.removeAttribute('src');
    video.load();
  }
}

function stopScanner() {
  scanning = false;
  if(animFrame) { cancelAnimationFrame(animFrame); animFrame = null; }
  cleanupStream();

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
      void submitPresensi(code.data);
      return;
    }
  }
  animFrame = requestAnimationFrame(scanFrame);
}

async function submitPresensi(qrDataRaw) {
  const qrData = normalizeQrPayload(qrDataRaw);
  document.getElementById('scanner-status').textContent = 'Memproses...';
  document.getElementById('scanner-status').className   = 'badge badge-amber';

  try {
    const loc = await getPositionOptional();
    await submitPresensiWithCoords(qrData, loc.latitude, loc.longitude);
  } catch (e) {
    showScanResult('danger', e.message || 'Lokasi atau koneksi gagal.');
    // Delay lebih lama (10 detik) agar pesan error sempat terbaca
    setTimeout(() => { scanning = true; scanFrame(); }, 10000);
  }
}

async function submitPresensiWithCoords(qrData, latitude, longitude) {
  let res;
  try {
    // Gunakan rute relatif (tanpa domain) agar tidak terkena CORS jika domain akses berbeda dengan APP_URL
    const scanRoute = '{{ route(auth()->user()->role . ".presensi.scan", [], false) }}';
    res = await fetch(scanRoute, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ qr_data: qrData, latitude, longitude })
    });
  } catch (err) {
    throw new Error('Koneksi gagal. Periksa internet Anda.');
  }

  const data = await res.json().catch(() => ({}));

  if (data.success) {
    stopScanner();
    document.getElementById('success-title').textContent = data.type === 'masuk' ? 'Presensi Masuk Berhasil!' : 'Presensi Pulang Berhasil!';
    document.getElementById('success-desc').textContent  = data.type === 'masuk' ? 'Jam masuk Anda tercatat' : 'Jam pulang Anda tercatat';
    document.getElementById('success-time').textContent  = data.jam;
    document.getElementById('success-status').textContent = data.status_label;
    let badge = 'green';
    if (data.type === 'masuk') badge = data.status === 'tepat_waktu' ? 'green' : 'amber';
    else badge = data.status === 'normal' ? 'green' : 'amber';
    document.getElementById('success-status').className  = 'badge badge-' + badge;
    document.getElementById('success-overlay').classList.add('show');
  } else {
    showScanResult('danger', data.message || 'Presensi gagal diproses.');
    // Delay lebih lama (10 detik) agar pesan error sempat terbaca
    setTimeout(() => { scanning = true; scanFrame(); }, 10000);
  }
}

function showError(msg) {
  const el = document.getElementById('scanner-error');
  if (el) {
    document.getElementById('scanner-error-msg').textContent = msg;
    el.style.display = 'flex';
  }
}

function showScanResult(type, msg) {
  const el = document.getElementById('scan-result');
  if (!el) return;

  const icon = type === 'success' ? 'circle-check' : 'circle-xmark';
  // Escape teks dari server (msg) sebelum disisipkan via innerHTML
  const safe = String(msg || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  el.className = 'alert alert-' + type;
  // Jadikan dua baris: baris pertama ringkas, baris kedua detail jam.
  // Pesan server memakai pola "<Kalimat>. <Jam masuk/pulang>: HH:MM. <Scan ...>."
  // sehingga dibagi per kalimat untuk keterbacaan.
  const lines = safe.split('. ').filter(Boolean).map(s => s.trim()).filter(Boolean);
  const body = lines.length > 1
    ? `<strong>${lines[0]}.</strong><br><span style="font-size:.82rem;opacity:.9;">${lines.slice(1).join('. ')}.</span>`
    : safe;

  el.innerHTML = `<i class="fa-solid fa-${icon}" style="margin-top:2px;"></i> <span style="line-height:1.45;">${body}</span>`;
  el.style.display = 'flex';
  el.style.opacity = '1';
  el.style.transform = 'none';
  
  // Scroll into view agar terlihat di layar kecil
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
@endpush


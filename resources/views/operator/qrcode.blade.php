@extends('layouts.app')
@section('title','Kelola QR Code')
@section('page-title','Kelola QR Code')

@push('styles')
<style>
.qr-card {
  background: var(--card-bg); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 28px;
  text-align: center; transition: all var(--transition);
}
.qr-card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,201,167,.12); }

.qr-white-box {
  background: #fff; padding: 16px; border-radius: 12px;
  display: inline-block; box-shadow: 0 4px 16px rgba(0,0,0,.2);
  margin: 16px 0;
}

.time-window-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px;
}
.time-win-item {
  background: rgba(0,201,167,.06); border: 1px solid rgba(0,201,167,.15);
  border-radius: var(--radius-sm); padding: 12px;
  text-align: center;
}
.time-win-item .tw-time { font-family: 'Syne',sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--teal); }
.time-win-item .tw-label { font-size: .7rem; color: var(--muted); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Kelola QR Code</span></div>
  <h1>Kelola QR Code Presensi</h1>
  <p class="text-muted">QR berisi tautan ke aplikasi (bukan teks mentah) agar bisa dibuka dari Google Lens — pengguna yang belum login akan diarahkan ke halaman masuk terlebih dahulu. Pastikan <strong>APP_URL</strong> di file .env sesuai domain akses (mis. http://127.0.0.1:8000).</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;" class="stagger">

  {{-- QR Code Masuk --}}
  <div class="card">
    <div class="card-header">
      <div style="width:10px;height:10px;border-radius:50%;background:var(--teal);box-shadow:0 0 8px var(--teal);"></div>
      <h3>QR Code Presensi Masuk</h3>
      <div class="card-actions">
        <span class="badge badge-{{ $qrMasuk?->is_active ? 'green' : 'red' }}">
          {{ $qrMasuk?->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
      </div>
    </div>
    <div class="card-body" style="text-align:center;">
      <div class="qr-white-box">
        {!! $qrMasukImage ?? '<div style="width:180px;height:180px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:2rem;">📋</div>' !!}
      </div>

      <div style="font-size:.78rem;color:var(--muted);margin-bottom:8px;">Kode QR</div>
      <div style="font-family:'Courier New',monospace;font-size:.8rem;color:var(--teal);background:rgba(0,201,167,.08);border-radius:6px;padding:6px 12px;display:inline-block;margin-bottom:16px;">
        {{ substr($qrMasuk?->kode_qr ?? 'QR-MASUK-XXXX', 0, 24) }}...
      </div>

      <div class="time-window-grid">
        <div class="time-win-item">
          <div class="tw-time">{{ $jadwal?->jam_masuk ?? '08:00' }}</div>
          <div class="tw-label">Jam Buka</div>
        </div>
        <div class="time-win-item">
          @php
            $batas = \Carbon\Carbon::parse($jadwal?->jam_masuk ?? '08:00')->addMinutes(($jadwal?->toleransi_menit ?? 5) + 60);
          @endphp
          <div class="tw-time">{{ $batas->format('H:i') }}</div>
          <div class="tw-label">Jam Tutup</div>
        </div>
      </div>

      <div style="display:flex;gap:8px;margin-top:16px;justify-content:center;">
        <a href="{{ route('operator.qrcode.download', ['type'=>'masuk']) }}" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-download"></i> Unduh
        </a>
        <a href="{{ route('operator.qrcode.print', ['type'=>'masuk']) }}" class="btn btn-outline btn-sm" target="_blank">
          <i class="fa-solid fa-print"></i> Cetak
        </a>
        <form method="POST" action="{{ route('operator.qrcode.toggle', ['type'=>'masuk']) }}">
          @csrf @method('PATCH')
          <button class="btn btn-{{ $qrMasuk?->is_active ? 'danger' : 'outline' }} btn-sm" type="submit">
            <i class="fa-solid fa-{{ $qrMasuk?->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
            {{ $qrMasuk?->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- QR Code Pulang --}}
  <div class="card">
    <div class="card-header">
      <div style="width:10px;height:10px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);"></div>
      <h3>QR Code Presensi Pulang</h3>
      <div class="card-actions">
        <span class="badge badge-{{ $qrPulang?->is_active ? 'green' : 'red' }}">
          {{ $qrPulang?->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
      </div>
    </div>
    <div class="card-body" style="text-align:center;">
      <div class="qr-white-box">
        {!! $qrPulangImage ?? '<div style="width:180px;height:180px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:2rem;">🏠</div>' !!}
      </div>

      <div style="font-size:.78rem;color:var(--muted);margin-bottom:8px;">Kode QR</div>
      <div style="font-family:'Courier New',monospace;font-size:.8rem;color:var(--green);background:rgba(0,224,150,.08);border-radius:6px;padding:6px 12px;display:inline-block;margin-bottom:16px;">
        {{ substr($qrPulang?->kode_qr ?? 'QR-PULANG-XXXX', 0, 24) }}...
      </div>

      <div class="time-window-grid">
        <div class="time-win-item" style="border-color:rgba(0,224,150,.15);">
          @php $bukaPulang = \Carbon\Carbon::parse($jadwal?->jam_pulang ?? '17:00')->subMinutes(30); @endphp
          <div class="tw-time" style="color:var(--green);">{{ $bukaPulang->format('H:i') }}</div>
          <div class="tw-label">Jam Buka</div>
        </div>
        <div class="time-win-item" style="border-color:rgba(0,224,150,.15);">
          @php $tutupPulang = \Carbon\Carbon::parse($jadwal?->jam_pulang ?? '17:00')->addHour(); @endphp
          <div class="tw-time" style="color:var(--green);">{{ $tutupPulang->format('H:i') }}</div>
          <div class="tw-label">Jam Tutup</div>
        </div>
      </div>

      <div style="display:flex;gap:8px;margin-top:16px;justify-content:center;">
        <a href="{{ route('operator.qrcode.download', ['type'=>'pulang']) }}" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-download"></i> Unduh
        </a>
        <a href="{{ route('operator.qrcode.print', ['type'=>'pulang']) }}" class="btn btn-outline btn-sm" target="_blank">
          <i class="fa-solid fa-print"></i> Cetak
        </a>
        <form method="POST" action="{{ route('operator.qrcode.toggle', ['type'=>'pulang']) }}">
          @csrf @method('PATCH')
          <button class="btn btn-{{ $qrPulang?->is_active ? 'danger' : 'outline' }} btn-sm" type="submit">
            <i class="fa-solid fa-{{ $qrPulang?->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
            {{ $qrPulang?->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

{{-- Jadwal Kerja & Toleransi --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="stagger">

  {{-- Edit Jadwal --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-clock text-teal"></i>
      <h3>Pengaturan Jadwal Kerja</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('operator.jadwal.update') }}">
        @csrf @method('PATCH')
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jam Masuk</label>
            <input type="time" name="jam_masuk" class="form-control" value="{{ $jadwal?->jam_masuk ?? '08:00' }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jam Pulang</label>
            <input type="time" name="jam_pulang" class="form-control" value="{{ $jadwal?->jam_pulang ?? '17:00' }}" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Toleransi Keterlambatan (menit)</label>
            <input type="number" name="toleransi_menit" class="form-control"
                   value="{{ $jadwal?->toleransi_menit ?? 5 }}" min="0" max="60" required>
          </div>
          <div class="form-group">
            <label class="form-label">Hari Kerja</label>
            <input type="text" name="hari_kerja" class="form-control"
                   value="{{ $jadwal?->hari_kerja ?? 'Senin - Jumat' }}"
                   placeholder="contoh: Senin - Jumat">
          </div>
        </div>
        <hr class="divider" style="margin:16px 0;">
        <p class="text-muted text-sm" style="margin-bottom:12px;">
          <strong>Radius kantor (opsional):</strong> jika diisi, karyawan harus mengizinkan lokasi dan berada dalam jarak tertentu dari titik kantor untuk presensi. Kosongkan ketiga field untuk menonaktifkan.
        </p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Lintang kantor</label>
            <input type="number" step="any" name="kantor_latitude" class="form-control"
                   value="{{ old('kantor_latitude', $jadwal?->kantor_latitude) }}"
                   placeholder="-6.XXXXXX">
          </div>
          <div class="form-group">
            <label class="form-label">Bujur kantor</label>
            <input type="number" step="any" name="kantor_longitude" class="form-control"
                   value="{{ old('kantor_longitude', $jadwal?->kantor_longitude) }}"
                   placeholder="106.XXXXXX">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Radius izin (meter)</label>
          <input type="number" name="radius_meter" class="form-control" min="10" max="50000"
                 value="{{ old('radius_meter', $jadwal?->radius_meter) }}"
                 placeholder="mis. 150">
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal
        </button>
      </form>
    </div>
  </div>

  {{-- Window Aktif Ringkasan --}}
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-circle-info text-teal"></i>
      <h3>Ringkasan Window Presensi</h3>
    </div>
    <div class="card-body">
      @php
        $jm  = \Carbon\Carbon::parse($jadwal?->jam_masuk ?? '08:00');
        $jp  = \Carbon\Carbon::parse($jadwal?->jam_pulang ?? '17:00');
        $tol = $jadwal?->toleransi_menit ?? 5;
      @endphp
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div style="background:rgba(0,201,167,.06);border:1px solid rgba(0,201,167,.15);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px;">Presensi Masuk</div>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="text-align:center;">
              <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--teal);">{{ $jm->subMinutes(15)->format('H:i') }}</div>
              <div class="text-xs text-muted">Buka (15 min sebelum)</div>
            </div>
            <div style="flex:1;height:2px;background:linear-gradient(90deg,var(--teal),var(--amber));border-radius:1px;"></div>
            <div style="text-align:center;">
              <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--amber);">{{ $jm->addMinutes(60+$tol)->format('H:i') }}</div>
              <div class="text-xs text-muted">Tutup</div>
            </div>
          </div>
          <div class="text-xs text-muted" style="margin-top:8px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--amber);"></i>
            Toleransi keterlambatan: <strong style="color:var(--amber);">{{ $tol }} menit</strong>
          </div>
        </div>

        <div style="background:rgba(0,224,150,.06);border:1px solid rgba(0,224,150,.15);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px;">Presensi Pulang</div>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="text-align:center;">
              <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--green);">{{ $jp->subMinutes(30)->format('H:i') }}</div>
              <div class="text-xs text-muted">Buka (30 min sebelum)</div>
            </div>
            <div style="flex:1;height:2px;background:linear-gradient(90deg,var(--green),var(--teal));border-radius:1px;"></div>
            <div style="text-align:center;">
              <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--teal);">{{ $jp->addHour()->format('H:i') }}</div>
              <div class="text-xs text-muted">Tutup (1 jam setelah)</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

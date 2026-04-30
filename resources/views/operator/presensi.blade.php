@extends('layouts.app')
@section('title', 'Data Presensi')
@section('page-title', 'Data Presensi')

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Data Presensi</span></div>
  <h1>Data Presensi Karyawan</h1>
  <p class="text-muted">Monitor dan kelola data kehadiran seluruh karyawan.</p>
</div>

{{-- ── Stat Cards ───────────────────────────────────────────── --}}
<div class="stat-grid stagger">
  <div class="stat-card">
    <div class="stat-icon teal"><i class="fa-solid fa-users"></i></div>
    <div class="stat-info">
      <div class="stat-label">Total Karyawan</div>
      <div class="stat-value">{{ $totalKaryawan }}</div>
      <div class="stat-delta">karyawan aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-info">
      <div class="stat-label">Hadir</div>
      <div class="stat-value">{{ $totalHadir }}</div>
      <div class="stat-delta pos">sudah presensi masuk</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-label">Terlambat</div>
      <div class="stat-value">{{ $totalTerlambat }}</div>
      <div class="stat-delta neg">hari ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-right-from-bracket"></i></div>
    <div class="stat-info">
      <div class="stat-label">Sudah Pulang</div>
      <div class="stat-value">{{ $totalPulang }}</div>
      <div class="stat-delta pos">presensi pulang</div>
    </div>
  </div>
</div>

{{-- ── Filter ───────────────────────────────────────────────── --}}
<div class="card mb-4">
  <div class="card-body-sm">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">

      <div style="flex:1;min-width:140px;">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control"
               value="{{ $tanggal }}" max="{{ today()->toDateString() }}">
      </div>

      <div style="flex:1;min-width:160px;">
        <label class="form-label">Karyawan</label>
        <select name="karyawan_id" class="form-control">
          <option value="">— Semua Karyawan —</option>
          @foreach($listKaryawan as $k)
            <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>
              {{ $k->nama_lengkap }}
            </option>
          @endforeach
        </select>
      </div>

      <div style="min-width:140px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="">— Semua Status —</option>
          <option value="tepat_waktu" {{ $status === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
          <option value="terlambat"   {{ $status === 'terlambat'   ? 'selected' : '' }}>Terlambat</option>
        </select>
      </div>

      <div style="display:flex;gap:8px;align-self:flex-end;">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>
        <a href="{{ route('operator.presensi') }}" class="btn btn-outline">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>

    </form>
  </div>
</div>

{{-- ── Tabel Presensi ───────────────────────────────────────── --}}
<div class="card animate-slideup">
  <div class="card-header">
    <i class="fa-solid fa-calendar-check text-teal"></i>
    <h3>
      Presensi —
      {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
    </h3>
    <div class="card-actions" style="margin-left:auto;">
      <span class="text-muted text-sm">{{ $presensiList->total() }} data</span>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Karyawan</th>
          <th>Jam Masuk</th>
          <th>Status Masuk</th>
          <th>Jam Pulang</th>
          <th>Status Pulang</th>
          <th>Durasi Kerja</th>
        </tr>
      </thead>
      <tbody>
        @forelse($presensiList as $i => $p)
        @php
          // Status masuk badge
          $bcMasuk = ['tepat_waktu' => 'green', 'terlambat' => 'amber'][$p->status_masuk] ?? 'muted';
          $blMasuk = ['tepat_waktu' => 'Tepat Waktu', 'terlambat' => 'Terlambat'][$p->status_masuk] ?? '—';

          // Status pulang badge
          $bcPulang = ['normal' => 'teal', 'lebih_awal' => 'red'][$p->status_pulang] ?? 'muted';
          $blPulang = ['normal' => 'Normal', 'lebih_awal' => 'Lebih Awal'][$p->status_pulang] ?? '—';

          // Hitung durasi kerja
          $durasi = '—';
          if ($p->jam_datang && $p->jam_pulang) {
              $masuk  = \Carbon\Carbon::parse($p->jam_datang);
              $pulang = \Carbon\Carbon::parse($p->jam_pulang);
              $diff   = $masuk->diff($pulang);
              $durasi = $diff->format('%H:%I') . ' jam';
          }
        @endphp
        <tr>
          <td class="text-muted text-xs">{{ $presensiList->firstItem() + $i }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--blue-light),#1e40af);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;color:#fff;">
                {{ strtoupper(substr($p->karyawan?->nama_lengkap ?? '?', 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:600;font-size:.88rem;">{{ $p->karyawan?->nama_lengkap ?? '—' }}</div>
                <div class="text-xs text-muted">{{ $p->karyawan?->jabatan ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($p->jam_datang)
              <span style="font-family:'Syne',sans-serif;font-weight:700;">
                {{ \Carbon\Carbon::parse($p->jam_datang)->format('H:i') }}
              </span>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td>
            @if($p->status_masuk)
              <span class="badge badge-{{ $bcMasuk }}">{{ $blMasuk }}</span>
            @else
              <span class="text-muted text-sm">—</span>
            @endif
          </td>
          <td>
            @if($p->jam_pulang)
              <span style="font-family:'Syne',sans-serif;font-weight:700;">
                {{ \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') }}
              </span>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td>
            @if($p->status_pulang)
              <span class="badge badge-{{ $bcPulang }}">{{ $blPulang }}</span>
            @else
              <span class="text-muted text-sm">—</span>
            @endif
          </td>
          <td>
            <span class="font-mono text-sm">{{ $durasi }}</span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:48px 20px;color:var(--text-secondary);">
            <i class="fa-solid fa-calendar-xmark" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4;"></i>
            Tidak ada data presensi untuk
            <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($presensiList->hasPages())
  <div class="card-footer">
    <div class="text-muted text-sm">
      Menampilkan {{ $presensiList->firstItem() }}–{{ $presensiList->lastItem() }}
      dari {{ $presensiList->total() }} data
    </div>
    <div style="margin-left:auto;">
      {{ $presensiList->appends(request()->query())->links() }}
    </div>
  </div>
  @endif

</div>

@endsection

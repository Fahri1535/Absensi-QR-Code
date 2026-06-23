@extends('layouts.app')
@section('title', 'Data Presensi')
@section('page-title', 'Data Presensi')

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Data Presensi</span></div>
  <h1>Data Presensi Karyawan</h1>
  <p class="text-muted">Monitor dan kelola data kehadiran seluruh karyawan.</p>
</div>

<div class="animate-slideup">
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
      <div class="stat-delta pos">presensi fisik</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow"><i class="fa-solid fa-hourglass-half"></i></div>
    <div class="stat-info">
      <div class="stat-label">Pending</div>
      <div class="stat-value">{{ $totalPending }}</div>
      <div class="stat-delta">belum absen pulang</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-file-medical"></i></div>
    <div class="stat-info">
      <div class="stat-label">Izin</div>
      <div class="stat-value">{{ $totalIzin }}</div>
      <div class="stat-delta">sudah disetujui</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-user-xmark"></i></div>
    <div class="stat-info">
      <div class="stat-label">Alpa</div>
      <div class="stat-value">{{ $totalAlpa }}</div>
      <div class="stat-delta neg">hari ini</div>
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
               value="{{ $tanggal }}" max="{{ today()->toDateString() }}" style="cursor: pointer;">
      </div>

      <div style="flex:1;min-width:160px;">
        <label class="form-label">Karyawan</label>
        <select name="karyawan_id" class="form-control" style="cursor: pointer;">
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
        <select name="status" class="form-control" style="cursor: pointer;">
          <option value="">— Semua Status —</option>
          <option value="tepat_waktu" {{ $status === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
          <option value="terlambat"   {{ $status === 'terlambat'   ? 'selected' : '' }}>Terlambat</option>
          <option value="pending"     {{ $status === 'pending'     ? 'selected' : '' }}>Pending</option>
          <option value="alpa"        {{ $status === 'alpa'        ? 'selected' : '' }}>Alpa</option>
          <option value="izin"        {{ $status === 'izin'        ? 'selected' : '' }}>Izin</option>
          <option value="sakit"       {{ $status === 'sakit'       ? 'selected' : '' }}>Sakit</option>
          <option value="cuti"        {{ $status === 'cuti'        ? 'selected' : '' }}>Cuti</option>
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
<div class="card">
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
          <th>Masuk</th>
          <th>Pulang</th>
          <th>Durasi</th>
          <th>Status</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($presensiList as $i => $p)
        @php
          $isIzin = $p->is_izin ?? false;
          $isAlpa = $p->is_alpa ?? false;
          $isPending = $p->is_pending ?? false;
          $status = $p->display_status ?? '—';
          
          if ($isIzin) {
              $sc = match($status) {
                  'sakit' => 'blue',
                  'cuti'  => 'indigo',
                  'pulang_cepat' => 'orange',
                  'lembur' => 'teal',
                  default => 'purple'
              };
              $sl = ucfirst(str_replace('_', ' ', $status));
          } elseif ($isAlpa) {
              $sc = 'red';
              $sl = 'Alpa';
          } elseif ($isPending) {
              $sc = 'yellow';
              $sl = 'Pending';
          } else {
              $sc = ['tepat_waktu'=>'green','terlambat'=>'amber','pulang_awal'=>'red'][$status] ?? 'muted';
              $sl = ['tepat_waktu'=>'Tepat Waktu','terlambat'=>'Terlambat','pulang_awal'=>'Pulang Awal'][$status] ?? ucfirst($status);
          }

          $durasi = ($p->jam_datang && $p->jam_pulang)
            ? \Carbon\Carbon::parse($p->jam_datang)->diff(\Carbon\Carbon::parse($p->jam_pulang))->format('%H:%I')
            : '—';
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
            {{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}
          </td>
          <td>
            {{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}
          </td>
          <td>
            <span class="font-mono text-sm">{{ $durasi }}</span>
          </td>
          <td>
            <span class="badge badge-{{ $sc }}">{{ $sl }}</span>
          </td>
          <td class="text-muted text-xs">
            {{ $p->keterangan ?? '—' }}
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
</div>

@endsection


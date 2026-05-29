@extends('layouts.app')
@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('hrd.karyawan') }}" style="color:var(--blue-light);">Data Karyawan</a> / <span>Detail</span>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h1>{{ $karyawan->nama_lengkap }}</h1>
    <a href="{{ route('hrd.karyawan') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="stagger">
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-user text-teal"></i>
      <h3>Profil</h3>
    </div>
    <div class="card-body-sm">
      <div style="display:flex;flex-direction:column;gap:10px;font-size:.9rem;">
        <div style="display:flex;justify-content:space-between;"><span class="text-muted">Username</span><span class="font-mono">{{ $karyawan->user?->username ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;"><span class="text-muted">Jabatan</span><span>{{ $karyawan->jabatan ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;"><span class="text-muted">Telepon</span><span>{{ $karyawan->nomor_telepon ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;"><span class="text-muted">Kode</span><span class="font-mono text-sm">{{ $karyawan->kode_karyawan ?? '—' }}</span></div>
        <div style="display:flex;justify-content:space-between;"><span class="text-muted">Status</span>
          <span class="badge badge-{{ $karyawan->status === 'aktif' ? 'green' : 'red' }}">{{ ucfirst($karyawan->status) }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-circle-info text-teal"></i>
      <h3>Catatan</h3>
    </div>
    <div class="card-body-sm">
      <p class="text-muted text-sm" style="line-height:1.6;">
        Pengubahan akun, password, dan penambahan karyawan dilakukan melalui menu Operator.
      </p>
    </div>
  </div>
</div>

<div class="card mt-6">
  <div class="card-header">
    <i class="fa-solid fa-clock-rotate-left text-teal"></i>
    <h3>Riwayat Presensi Terbaru</h3>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Masuk</th>
          <th>Pulang</th>
          <th>Status Masuk</th>
        </tr>
      </thead>
      <tbody>
        @forelse($karyawan->presensi as $p)
        <tr>
          <td>{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</td>
          <td>{{ $p->jam_datang ? \Carbon\Carbon::parse($p->jam_datang)->format('H:i') : '—' }}</td>
          <td>{{ $p->jam_pulang ? \Carbon\Carbon::parse($p->jam_pulang)->format('H:i') : '—' }}</td>
          <td>
            @php
              $st = match($p->status_masuk ?? '') {
                'tepat_waktu' => ['green', 'Tepat Waktu'],
                'terlambat' => ['amber', 'Terlambat'],
                default => ['muted', '—'],
              };
            @endphp
            <span class="badge badge-{{ $st[0] }}">{{ $st[1] }}</span>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:var(--text-secondary);padding:24px;">Belum ada presensi</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

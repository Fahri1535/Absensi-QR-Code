@extends('layouts.app')
@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Data Karyawan</span></div>
  <h1>Data Karyawan</h1>
  <p class="text-muted">Daftar pegawai dengan akun role karyawan (hanya lihat; penambahan/ubah data oleh Operator).</p>
</div>

<div class="card" style="margin-bottom:16px;">
  <div class="card-body-sm">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari</label>
        <input type="text" name="cari" class="form-control" placeholder="Nama atau username..."
               value="{{ request('cari') }}">
      </div>
      <div style="min-width:140px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="">— Semua —</option>
          <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
          <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <a href="{{ route('hrd.karyawan') }}" class="btn btn-outline"><i class="fa-solid fa-xmark"></i></a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Karyawan</th>
          <th>Username</th>
          <th>Jabatan</th>
          <th>No. Telepon</th>
          <th>Status</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($karyawanList ?? [] as $i => $k)
        <tr>
          <td class="text-muted text-xs">{{ $karyawanList->firstItem() + $i }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:38px;height:38px;border-radius:50%;
                          background:linear-gradient(135deg,var(--blue-primary),var(--blue-secondary));
                          display:flex;align-items:center;justify-content:center;
                          font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
              </div>
              <div style="font-weight:600;">{{ $k->nama_lengkap }}</div>
            </div>
          </td>
          <td class="font-mono text-sm">{{ $k->user?->username ?? '—' }}</td>
          <td class="text-muted text-sm">{{ $k->jabatan ?? '—' }}</td>
          <td>{{ $k->nomor_telepon ?? '—' }}</td>
          <td>
            <span class="badge badge-{{ $k->status === 'aktif' ? 'green' : 'red' }}">{{ ucfirst($k->status) }}</span>
          </td>
          <td style="text-align:right;">
            <a href="{{ route('hrd.karyawan.show', $k->id) }}" class="btn btn-ghost btn-sm">
              <i class="fa-solid fa-eye"></i> Detail
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--text-secondary);">
            Tidak ada data karyawan
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if(isset($karyawanList) && $karyawanList->hasPages())
  <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
    <span class="text-muted text-sm">Total: {{ $karyawanList->total() }}</span>
    {{ $karyawanList->appends(request()->query())->links() }}
  </div>
  @endif
</div>
@endsection

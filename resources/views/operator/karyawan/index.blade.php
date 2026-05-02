@extends('layouts.app')
@section('title','Data Karyawan')
@section('page-title','Data Karyawan')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Data Karyawan</span></div>
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h1>Data Karyawan</h1>
    <a href="{{ route('operator.karyawan.create') }}" class="btn btn-primary">
      <i class="fa-solid fa-user-plus"></i> Tambah Karyawan
    </a>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:16px;">
  <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Search & Filter --}}
<div class="card" style="margin-bottom:16px;">
  <div class="card-body-sm">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari Karyawan</label>
        <div style="position:relative;">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:.85rem;">
            <i class="fa-solid fa-magnifying-glass"></i>
          </span>
          <input type="text" name="cari" class="form-control" style="padding-left:36px;"
                 placeholder="Nama atau username..." value="{{ request('cari') }}">
        </div>
      </div>
      <div style="min-width:140px;">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="">— Semua —</option>
          <option value="aktif"    {{ request('status') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
          <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
      </div>
      <div style="align-self:flex-end;display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <a href="{{ route('operator.karyawan') }}" class="btn btn-outline"><i class="fa-solid fa-xmark"></i></a>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="card">
  <div class="table-wrap">
    <table>
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
              <div>
                <div style="font-weight:600;">{{ $k->nama_lengkap }}</div>
                <div class="text-xs text-muted">{{ $k->jabatan ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td class="font-mono text-sm">{{ $k->user?->username ?? '—' }}</td>
          <td class="text-muted text-sm">{{ $k->jabatan ?? '—' }}</td>
          <td>{{ $k->nomor_telepon ?? '—' }}</td>
          <td>
            <span class="badge badge-{{ $k->status === 'aktif' ? 'green' : 'red' }}">
              {{ ucfirst($k->status) }}
            </span>
          </td>
          <td>
            <div style="display:flex;gap:6px;justify-content:flex-end;">
              <a href="{{ route('operator.karyawan.show', $k->id) }}" class="btn btn-ghost btn-sm" title="Detail">
                <i class="fa-solid fa-eye"></i>
              </a>
              <a href="{{ route('operator.karyawan.edit', $k->id) }}" class="btn btn-outline btn-sm" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="{{ route('operator.karyawan.destroy', $k->id) }}"
                    onsubmit="return confirm('Hapus karyawan {{ addslashes($k->nama_lengkap) }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit" title="Hapus">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--text-secondary);">
            <i class="fa-solid fa-users-slash" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4;"></i>
            Tidak ada data karyawan
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if(isset($karyawanList) && $karyawanList->hasPages())
  <div class="card-footer">
    <div class="text-muted text-sm">Total: {{ $karyawanList->total() }} karyawan</div>
    <div style="margin-left:auto;">{{ $karyawanList->appends(request()->query())->links() }}</div>
  </div>
  @endif
</div>
@endsection

{{-- ===================================================
     DATA KARYAWAN — resources/views/operator/karyawan/index.blade.php
     =================================================== --}}
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

{{-- Search & Filter --}}
<div class="card mb-6">
  <div class="card-body-sm">
    <form method="GET" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;">
        <label class="form-label">Cari Karyawan</label>
        <div style="position:relative;">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;"><i class="fa-solid fa-magnifying-glass"></i></span>
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
      <div style="align-self:end;display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <a href="{{ route('operator.karyawan') }}" class="btn btn-outline"><i class="fa-solid fa-xmark"></i></a>
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="card animate-slideup">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Karyawan</th>
          <th>Username</th>
          <th>No. Telepon</th>
          <th>Status</th>
          <th>Bergabung</th>
          <th style="text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($karyawanList ?? [] as $i => $k)
        <tr>
          <td class="text-muted text-xs">{{ $karyawanList->firstItem() + $i }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:12px;">
              <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--navy-light));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:600;">{{ $k->nama_lengkap }}</div>
                <div class="text-xs text-muted">{{ $k->jabatan ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td class="font-mono text-sm">{{ $k->user?->username ?? '—' }}</td>
          <td>{{ $k->nomor_telepon ?? '—' }}</td>
          <td>
            <span class="badge badge-{{ $k->status === 'aktif' ? 'green' : 'red' }}">
              {{ ucfirst($k->status) }}
            </span>
          </td>
          <td class="text-muted text-sm">{{ $k->created_at?->format('d M Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;justify-content:flex-end;">
              <a href="{{ route('operator.karyawan.show', $k->id) }}" class="btn btn-ghost btn-sm" title="Detail">
                <i class="fa-solid fa-eye"></i>
              </a>
              <a href="{{ route('operator.karyawan.edit', $k->id) }}" class="btn btn-outline btn-sm" title="Edit">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="{{ route('operator.karyawan.destroy', $k->id) }}"
                    onsubmit="return confirm('Hapus karyawan {{ $k->nama_lengkap }}?')">
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
          <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
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
    <div class="text-muted text-sm">{{ $karyawanList->total() }} karyawan</div>
    <div style="margin-left:auto;">{{ $karyawanList->appends(request()->query())->links() }}</div>
  </div>
  @endif
</div>
@endsection


{{-- ===================================================
     FORM TAMBAH / EDIT KARYAWAN
     resources/views/operator/karyawan/form.blade.php
     =================================================== --}}
@extends('layouts.app')
@section('title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('page-title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    Beranda / <a href="{{ route('operator.karyawan') }}">Data Karyawan</a> /
    <span>{{ isset($karyawan) ? 'Edit' : 'Tambah' }}</span>
  </div>
  <h1>{{ isset($karyawan) ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}</h1>
</div>

<div style="max-width:640px;">
  <div class="card animate-slideup">
    <div class="card-header">
      <i class="fa-solid fa-user-{{ isset($karyawan) ? 'pen' : 'plus' }} text-teal"></i>
      <h3>{{ isset($karyawan) ? 'Ubah Data' : 'Data Karyawan Baru' }}</h3>
    </div>
    <div class="card-body">

      @if($errors->any())
      <div class="alert alert-danger" style="margin-bottom:20px;">
        @foreach($errors->all() as $e)
        <div><i class="fa-solid fa-circle-xmark"></i> {{ $e }}</div>
        @endforeach
      </div>
      @endif

      <form method="POST"
            action="{{ isset($karyawan) ? route('operator.karyawan.update', $karyawan->id) : route('operator.karyawan.store') }}"
            enctype="multipart/form-data">
        @csrf
        @if(isset($karyawan)) @method('PUT') @endif

        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid var(--border);">
          Data Akun
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Username <span style="color:var(--red);">*</span></label>
            <input type="text" name="username" class="form-control"
                   value="{{ old('username', $karyawan?->user?->username) }}"
                   {{ isset($karyawan) ? 'readonly style=opacity:.6' : '' }} required>
            @if(isset($karyawan))
            <div class="text-xs text-muted" style="margin-top:4px;">Username tidak dapat diubah</div>
            @endif
          </div>
          <div class="form-group">
            <label class="form-label">
              Password {{ isset($karyawan) ? '<span class="text-muted" style="font-size:.72rem;">(kosongkan jika tidak diubah)</span>' : '<span style="color:var(--red);">*</span>' }}
            </label>
            <div style="position:relative;">
              <input type="password" name="password" id="pwInput" class="form-control"
                     placeholder="{{ isset($karyawan) ? 'Biarkan kosong...' : 'Min. 8 karakter' }}"
                     {{ isset($karyawan) ? '' : 'required' }} minlength="8">
              <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);"
                    onclick="const i=document.getElementById('pwInput');i.type=i.type==='password'?'text':'password'">
                <i class="fa-solid fa-eye"></i>
              </span>
            </div>
          </div>
        </div>

        <hr class="divider">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:16px;">
          Data Pribadi
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nama Lengkap <span style="color:var(--red);">*</span></label>
            <input type="text" name="nama_lengkap" class="form-control"
                   value="{{ old('nama_lengkap', $karyawan?->nama_lengkap) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jabatan</label>
            <input type="text" name="jabatan" class="form-control"
                   value="{{ old('jabatan', $karyawan?->jabatan) }}"
                   placeholder="contoh: Staff Administrasi">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nomor Telepon</label>
            <input type="tel" name="nomor_telepon" class="form-control"
                   value="{{ old('nomor_telepon', $karyawan?->nomor_telepon) }}"
                   placeholder="08xxxxxxxxxx">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="aktif"    {{ old('status', $karyawan?->status ?? 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
              <option value="nonaktif" {{ old('status', $karyawan?->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Foto (opsional)</label>
          <input type="file" name="foto" class="form-control" accept="image/*">
          @if($karyawan?->foto)
          <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
            <img src="{{ asset('storage/'.$karyawan->foto) }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <span class="text-xs text-muted">Foto saat ini · Upload baru untuk mengganti</span>
          </div>
          @endif
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> {{ isset($karyawan) ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
          </button>
          <a href="{{ route('operator.karyawan') }}" class="btn btn-outline">
            <i class="fa-solid fa-xmark"></i> Batal
          </a>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

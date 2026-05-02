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
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-user-{{ isset($karyawan) ? 'pen' : 'plus' }}" style="color:var(--blue-light);"></i>
      <h3>{{ isset($karyawan) ? 'Ubah Data' : 'Data Karyawan Baru' }}</h3>
    </div>
    <div class="card-body">

      @if($errors->any())
      <div class="alert alert-danger" style="margin-bottom:20px;">
        <div><i class="fa-solid fa-circle-xmark"></i> <strong>Periksa kembali input:</strong></div>
        @foreach($errors->all() as $e)
        <div style="margin-top:4px;">• {{ $e }}</div>
        @endforeach
      </div>
      @endif

      <form method="POST"
            action="{{ isset($karyawan) ? route('operator.karyawan.update', $karyawan->id) : route('operator.karyawan.store') }}"
            enctype="multipart/form-data">
        @csrf
        @if(isset($karyawan)) @method('PUT') @endif

        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                    color:var(--text-secondary);margin-bottom:16px;padding-bottom:8px;
                    border-bottom:1px solid var(--border);">
          Data Akun
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Username <span style="color:var(--red);">*</span></label>
            <input type="text" name="username" class="form-control"
                   value="{{ old('username', $karyawan?->user?->username) }}"
                   {{ isset($karyawan) ? 'readonly' : 'required' }}
                   style="{{ isset($karyawan) ? 'opacity:.6;' : '' }}">
            @if(isset($karyawan))
            <div class="text-xs text-muted" style="margin-top:4px;">Username tidak dapat diubah</div>
            @endif
          </div>
          <div class="form-group">
            <label class="form-label">
              Password
              @if(isset($karyawan))
                <span class="text-muted" style="font-size:.72rem;font-weight:400;">(kosongkan jika tidak diubah)</span>
              @else
                <span style="color:var(--red);">*</span>
              @endif
            </label>
            <div style="position:relative;">
              <input type="password" name="password" id="pwInput" class="form-control"
                     placeholder="{{ isset($karyawan) ? 'Biarkan kosong...' : 'Min. 6 karakter' }}"
                     {{ isset($karyawan) ? '' : 'required' }} minlength="6">
              <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                           cursor:pointer;color:var(--text-secondary);"
                    onclick="const i=document.getElementById('pwInput');i.type=i.type==='password'?'text':'password'">
                <i class="fa-solid fa-eye"></i>
              </span>
            </div>
          </div>
        </div>

        <hr class="divider">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                    color:var(--text-secondary);margin-bottom:16px;">
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
          <label class="form-label">Foto <span class="text-muted" style="font-weight:400;">(opsional)</span></label>
          <input type="file" name="foto" class="form-control" accept="image/*">
          @if($karyawan?->foto)
          <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
            <img src="{{ asset('storage/'.$karyawan->foto) }}"
                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <span class="text-xs text-muted">Foto saat ini · Upload baru untuk mengganti</span>
          </div>
          @endif
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i>
            {{ isset($karyawan) ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
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

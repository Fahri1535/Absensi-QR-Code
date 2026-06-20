@extends('layouts.app')
@section('title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('page-title', isset($karyawan) ? 'Edit Karyawan' : 'Tambah Karyawan')

@push('styles')
<style>
  .pw-wrap { position: relative; }
  .pw-toggle {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; padding: 4px; cursor: pointer;
    color: var(--text-secondary); font-size: .9rem; transition: color .2s;
    display: flex; align-items: center; justify-content: center;
  }
  .pw-toggle:hover { color: var(--teal); }
  .pw-wrap .form-control { padding-right: 42px; }
</style>
@endpush

@section('content')
@php $karyawan = $karyawan ?? null; @endphp
<div class="page-header">
  <div class="breadcrumb">
    Beranda / <a href="{{ route('operator.karyawan') }}">Data Karyawan &amp; HRD</a> /
    <span>{{ isset($karyawan) ? 'Edit' : 'Tambah' }}</span>
  </div>
  <h1>{{ isset($karyawan) ? 'Edit Data Karyawan &amp; HRD' : 'Tambah Karyawan &amp; HRD Baru' }}</h1>
</div>

<div style="max-width:640px;">
  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-user-{{ isset($karyawan) ? 'pen' : 'plus' }}" style="color:var(--blue-light);"></i>
      <h3>{{ isset($karyawan) ? 'Ubah Data' : 'Data Karyawan &amp; HRD Baru' }}</h3>
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
                   value="{{ old('username', $karyawan?->user?->username) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Role <span style="color:var(--red);">*</span></label>
            <select name="role" class="form-control" required>
              <option value="karyawan" {{ old('role', $karyawan?->user?->role) === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
              <option value="hrd"      {{ old('role', $karyawan?->user?->role) === 'hrd'      ? 'selected' : '' }}>HRD</option>
            </select>
          </div>
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
          <div class="pw-wrap">
            <input type="password" id="pwInput" name="password" class="form-control"
                   placeholder="{{ isset($karyawan) ? 'Biarkan kosong...' : 'Min. 6 karakter' }}"
                   {{ isset($karyawan) ? '' : 'required' }} minlength="6">
            <button type="button" class="pw-toggle" onclick="togglePw()" tabindex="-1" aria-label="Tampilkan password">
              <i class="fa-solid fa-eye" id="pwEye"></i>
            </button>
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
            <img src="{{ $karyawan->foto_url }}"
                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;aspect-ratio:1/1;">
            <span class="text-xs text-muted">Foto saat ini · Upload baru untuk mengganti</span>
          </div>
          @endif
        </div>

        <hr class="divider">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;
                    color:var(--text-secondary);margin-bottom:16px;">
          Jadwal Kerja (Kosongkan untuk menggunakan jadwal global)
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jam Masuk</label>
            <input type="time" name="jam_masuk" class="form-control"
                   value="{{ old('jam_masuk', $karyawan?->jam_masuk ? substr($karyawan->jam_masuk, 0, 5) : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Jam Pulang</label>
            <input type="time" name="jam_pulang" class="form-control"
                   value="{{ old('jam_pulang', $karyawan?->jam_pulang ? substr($karyawan->jam_pulang, 0, 5) : '') }}">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Toleransi Keterlambatan (Menit)</label>
          <input type="number" name="toleransi_menit" class="form-control" min="0"
                 value="{{ old('toleransi_menit', $karyawan?->toleransi_menit) }}">
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

<script>
function togglePw() {
  const inp = document.getElementById('pwInput');
  const eye = document.getElementById('pwEye');
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  eye.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}
</script>
@endsection

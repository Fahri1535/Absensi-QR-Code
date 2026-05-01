@extends('layouts.app')
@section('title','Profil Saya')
@section('page-title','Profil Saya')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Profil Saya</span></div>
  <h1>Profil Saya</h1>
  <p class="text-muted">Kelola informasi akun dan data pribadi Anda.</p>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Avatar Card --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    <div class="card">
      <div class="card-body" style="text-align:center;padding:32px 24px;">
        {{-- Avatar --}}
        <div style="position:relative;display:inline-block;margin-bottom:16px;">
          @if($karyawan?->foto)
            <img src="{{ asset('storage/'.$karyawan->foto) }}"
                 style="width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid var(--teal);box-shadow:0 0 24px rgba(0,201,167,.3);"
                 alt="Foto Profil">
          @else
            <div style="width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--navy-light));display:flex;align-items:center;justify-content:center;font-size:2.2rem;font-weight:700;border:3px solid var(--teal);box-shadow:0 0 24px rgba(0,201,167,.3);">
              {{ strtoupper(substr($karyawan?->nama_lengkap ?? auth()->user()->username, 0, 1)) }}
            </div>
          @endif
          <label for="fotoInput" style="position:absolute;bottom:0;right:0;width:26px;height:26px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.65rem;color:var(--navy);box-shadow:0 2px 8px rgba(0,0,0,.3);">
            <i class="fa-solid fa-pen"></i>
          </label>
        </div>

        <h3 style="margin-bottom:4px;">{{ $karyawan?->nama_lengkap ?? auth()->user()->username }}</h3>
        <div class="text-muted text-sm" style="margin-bottom:12px;">{{ $karyawan?->jabatan ?? '—' }}</div>
        <span class="badge badge-{{ auth()->user()->role === 'karyawan' ? 'teal' : 'amber' }}">
          {{ ucfirst(auth()->user()->role) }}
        </span>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-phone" style="color:var(--teal);width:16px;"></i>
            <span class="text-sm">{{ $karyawan?->nomor_telepon ?? '—' }}</span>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-calendar" style="color:var(--teal);width:16px;"></i>
            <span class="text-sm">Bergabung {{ $karyawan?->created_at?->translatedFormat('M Y') ?? '—' }}</span>
          </div>
          <div style="display:flex;gap:10px;align-items:center;">
            <i class="fa-solid fa-circle" style="color:{{ $karyawan?->status === 'aktif' ? 'var(--green)' : 'var(--red)' }};width:16px;font-size:.55rem;"></i>
            <span class="text-sm">{{ ucfirst($karyawan?->status ?? 'aktif') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- QR Code Pribadi --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-qrcode text-teal"></i>
        <h3>QR Code Saya</h3>
      </div>
      <div class="card-body" style="text-align:center;">
        @if($qrCode)
          <div style="background:#fff;padding:12px;border-radius:10px;display:inline-block;margin-bottom:12px;box-shadow:0 4px 16px rgba(0,0,0,.2);">
            {!! $qrImage !!}
          </div>
          <div class="text-xs text-muted" style="margin-bottom:12px;">
            QR Code unik untuk identifikasi Anda
          </div>
          <div style="display:flex;gap:8px;justify-content:center;">
            <a href="{{ route('karyawan.qrcode.download') }}" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-download"></i> Unduh
            </a>
          </div>
        @else
          <div class="text-muted text-sm" style="padding:16px 0;">
            QR Code belum tersedia.<br>Hubungi operator.
          </div>
        @endif
      </div>
    </div>

  </div>

  {{-- RIGHT: Edit Forms --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Tab --}}
    <div class="tabs">
      <button class="tab active" onclick="switchTab('data',this)">Data Pribadi</button>
      <button class="tab"        onclick="switchTab('password',this)">Ubah Password</button>
    </div>

    {{-- Data Pribadi --}}
    <div id="tab-data" class="card">
      <div class="card-header">
        <i class="fa-solid fa-user text-teal"></i>
        <h3>Informasi Pribadi</h3>
      </div>
      <div class="card-body">

        @if(session('profil_success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
          <i class="fa-solid fa-circle-check"></i> {{ session('profil_success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('karyawan.profil.update') }}" enctype="multipart/form-data">
          @csrf @method('PUT')

          {{-- Hidden foto input --}}
          <input type="file" id="fotoInput" name="foto" accept="image/*" style="display:none;" onchange="previewFoto(this)">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama_lengkap" class="form-control"
                     value="{{ old('nama_lengkap', $karyawan?->nama_lengkap) }}" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Telepon</label>
              <input type="tel" name="nomor_telepon" class="form-control"
                     value="{{ old('nomor_telepon', $karyawan?->nomor_telepon) }}"
                     placeholder="08xxxxxxxxxx">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled
                     style="opacity:.6;cursor:not-allowed;">
              <div class="text-xs text-muted" style="margin-top:4px;">Username tidak dapat diubah</div>
            </div>
            <div class="form-group">
              <label class="form-label">Jabatan</label>
              <input type="text" class="form-control" value="{{ $karyawan?->jabatan ?? '—' }}" disabled
                     style="opacity:.6;cursor:not-allowed;">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </form>
      </div>
    </div>

    {{-- Ubah Password --}}
    <div id="tab-password" class="card" style="display:none;">
      <div class="card-header">
        <i class="fa-solid fa-lock text-teal"></i>
        <h3>Ubah Password</h3>
      </div>
      <div class="card-body">

        @if(session('password_success'))
        <div class="alert alert-success" style="margin-bottom:20px;">
          <i class="fa-solid fa-circle-check"></i> {{ session('password_success') }}
        </div>
        @endif
        @if(session('password_error'))
        <div class="alert alert-danger" style="margin-bottom:20px;">
          <i class="fa-solid fa-circle-xmark"></i> {{ session('password_error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('karyawan.password.update') }}">
          @csrf @method('PATCH')

          <div class="form-group">
            <label class="form-label">Password Saat Ini</label>
            <div style="position:relative;">
              <input type="password" name="password_lama" id="pw1" class="form-control"
                     placeholder="Masukkan password saat ini" required>
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);"
                    onclick="togglePw('pw1',this)"><i class="fa-solid fa-eye"></i></span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Password Baru</label>
            <div style="position:relative;">
              <input type="password" name="password_baru" id="pw2" class="form-control"
                     placeholder="Minimal 8 karakter" required minlength="8">
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);"
                    onclick="togglePw('pw2',this)"><i class="fa-solid fa-eye"></i></span>
            </div>
            {{-- Strength bar --}}
            <div style="height:4px;background:var(--border);border-radius:2px;margin-top:8px;overflow:hidden;">
              <div id="pw-strength-bar" style="height:100%;width:0%;border-radius:2px;transition:all .3s;"></div>
            </div>
            <div id="pw-strength-label" class="text-xs" style="margin-top:4px;color:var(--muted);"></div>
          </div>

          <div class="form-group">
            <label class="form-label">Konfirmasi Password Baru</label>
            <div style="position:relative;">
              <input type="password" name="password_baru_confirmation" id="pw3" class="form-control"
                     placeholder="Ulangi password baru" required>
              <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);"
                    onclick="togglePw('pw3',this)"><i class="fa-solid fa-eye"></i></span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-key"></i> Ubah Password
          </button>
        </form>
      </div>
    </div>

    {{-- Statistik Singkat --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-chart-bar text-teal"></i>
        <h3>Statistik Presensi Bulan Ini</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;text-align:center;">
          @foreach([
            ['Hadir', $stats['hadir']??0, 'teal'],
            ['Tepat Waktu', $stats['tepat_waktu']??0, 'green'],
            ['Terlambat', $stats['terlambat']??0, 'amber'],
            ['Alpha', $stats['alpha']??0, 'red'],
          ] as [$label, $val, $col])
          <div style="background:rgba({{ $col==='teal'?'0,201,167':($col==='green'?'0,224,150':($col==='amber'?'255,171,64':'255,83,112')) }},.08);border-radius:var(--radius-sm);padding:14px 8px;border:1px solid rgba({{ $col==='teal'?'0,201,167':($col==='green'?'0,224,150':($col==='amber'?'255,171,64':'255,83,112')) }},.15);">
            <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--{{ $col }});">{{ $val }}</div>
            <div class="text-xs text-muted">{{ $label }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
  document.getElementById('tab-data').style.display     = tab === 'data'     ? 'block' : 'none';
  document.getElementById('tab-password').style.display = tab === 'password' ? 'block' : 'none';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}

function togglePw(id, el) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  el.querySelector('i').className = inp.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

function previewFoto(input) {
  if(input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.querySelector('.profile-avatar img') || document.querySelector('[style*="border-radius:50%"]');
    };
    reader.readAsDataURL(input.files[0]);
    // Auto submit on change
    input.closest('form') && input.closest('form').submit();
  }
}

// Password strength
document.getElementById('pw2')?.addEventListener('input', function() {
  const val = this.value;
  let score = 0;
  if(val.length >= 8) score++;
  if(/[A-Z]/.test(val)) score++;
  if(/[0-9]/.test(val)) score++;
  if(/[^A-Za-z0-9]/.test(val)) score++;

  const bar   = document.getElementById('pw-strength-bar');
  const label = document.getElementById('pw-strength-label');
  const levels = [
    [0,'0%','transparent',''],
    [1,'25%','var(--red)','Lemah'],
    [2,'50%','var(--amber)','Sedang'],
    [3,'75%','var(--teal)','Kuat'],
    [4,'100%','var(--green)','Sangat Kuat'],
  ];
  const [,w,color,text] = levels[score];
  bar.style.width = w; bar.style.background = color;
  label.textContent = text; label.style.color = color;
});
</script>

<style>
  body, .card, .card-body, .page-content, 
  .table, .table th, .table td, .badge, .btn,
  h1, h2, h3, h4, p, span, div {
    font-family: 'DM Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
    letter-spacing: normal !important;
  }
</style>

@endpush

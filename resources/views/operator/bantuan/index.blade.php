@extends('layouts.app')
@section('title', 'Kelola Bantuan & Kontak')
@section('page-title', 'Kelola Bantuan')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Kelola Bantuan</span></div>
  <h1>Kelola Bantuan &amp; Kontak Admin</h1>
  <p class="text-muted">Perbarui informasi kontak dan jam operasional yang muncul di halaman Bantuan untuk seluruh karyawan.</p>
</div>

<style>
  .help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
  }
  .form-label-desc {
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: normal;
    display: block;
    margin-top: 2px;
  }
</style>

<form action="{{ route('operator.bantuan.update') }}" method="POST" class="animate-slideup">
  @csrf
  @method('PATCH')

  {{-- ── Pengaturan Umum ───────────────────────────────────── --}}
  <div class="card mb-6">
    <div class="card-header">
      <i class="fa-solid fa-clock text-teal"></i>
      <h3>Pengaturan Layanan</h3>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Jam Operasional</label>
          <input type="text" name="jam_operasional" class="form-control" value="{{ $jamOp }}" placeholder="Senin–Jumat, 08.00–17.00 WIB" required>
          <span class="form-label-desc">Waktu layanan bantuan aktif</span>
        </div>
        <div class="form-group">
          <label class="form-label">SLA (Lama Respons)</label>
          <input type="text" name="sla" class="form-control" value="{{ $sla }}" placeholder="Respons dalam 1×24 jam kerja.">
          <span class="form-label-desc">Perkiraan waktu tunggu balasan</span>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Data Kontak ───────────────────────────────────────── --}}
  <div class="help-grid">
    @foreach($bantuan as $b)
    <div class="card">
      <div class="card-header">
        <i class="fa-solid {{ $b->slug === 'hrd' ? 'fa-user-tie text-amber' : 'fa-user-gear text-teal' }}"></i>
        <h3>Kontak Tim {{ strtoupper($b->slug) }}</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Nama Tampilan</label>
          <input type="text" name="kontak[{{ $b->slug }}][nama]" class="form-control" value="{{ $b->nama }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Peran</label>
          <input type="text" name="kontak[{{ $b->slug }}][deskripsi]" class="form-control" value="{{ $b->deskripsi }}">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="kontak[{{ $b->slug }}][telepon]" class="form-control" value="{{ $b->telepon }}" placeholder="021-xxxxxx">
          </div>
          <div class="form-group">
            <label class="form-label">WhatsApp</label>
            <input type="text" name="kontak[{{ $b->slug }}][whatsapp]" class="form-control" value="{{ $b->whatsapp }}" placeholder="+62812xxxxxx">
            <span class="form-label-desc">Gunakan format +62</span>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Alamat Email</label>
          <input type="email" name="kontak[{{ $b->slug }}][email]" class="form-control" value="{{ $b->email }}" placeholder="admin@perusahaan.com">
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div style="display:flex; justify-content:flex-end; padding: 20px 0;">
    <button type="submit" class="btn btn-primary btn-lg" style="min-width: 200px;">
      <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
    </button>
  </div>
</form>
@endsection

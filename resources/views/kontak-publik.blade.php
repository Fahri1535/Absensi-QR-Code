@extends('layouts.guest-kontak')
@section('title', 'Kontak & Bantuan')

@section('content')
<div style="max-width:960px;margin:0 auto;">
  <div style="margin-bottom:24px;display:flex;flex-wrap:wrap;justify-content:space-between;gap:14px;align-items:center;">
    <div>
      <div class="breadcrumb" style="margin-bottom:6px;"><a href="{{ route('login') }}" style="color:var(--blue-light);text-decoration:none;">Login</a> / <span>Kontak Admin</span></div>
      <h1 style="font-family:'DM Sans',sans-serif;font-size:1.65rem;font-weight:800;margin:0;line-height:1.2;color:var(--text-primary);">Kontak &amp; Bantuan</h1>
      <p class="text-muted" style="max-width:640px;line-height:1.5;margin-top:8px;font-size:.92rem;margin-bottom:0;">
        Anda belum masuk aplikasi — halaman ini menampilkan informasi kontak yang sama seperti menu <strong style="font-weight:600;color:var(--text-primary);">Bantuan &amp; Kontak</strong> setelah login.
      </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="{{ route('login') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-right-to-bracket"></i> Masuk aplikasi</a>
    </div>
  </div>

  @include('partials.bantuan-kontak-blocks')

  <p class="text-muted text-xs" style="margin-top:28px;text-align:center;line-height:1.5;">
    Kontak ini dikelola oleh tim Operator sistem. Jika nomor/email kosong atau tidak aktif, harap melapor secara langsung.
  </p>
</div>
@endsection


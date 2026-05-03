@extends('layouts.app')
@section('title', 'Bantuan & Kontak Admin')
@section('page-title', 'Bantuan & Kontak')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Bantuan</span></div>
  <h1>Bantuan &amp; Kontak Admin</h1>
  <p class="text-muted" style="max-width:660px;line-height:1.5;margin-top:6px;">
    Hubungi pihak terkait jika Anda mengalami kendala teknis (presensi, QR, lokasi) atau pertanyaan terkait izin dan HRD.
  </p>
</div>

@include('partials.bantuan-kontak-blocks')
@endsection

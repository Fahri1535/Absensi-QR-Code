@extends('layouts.app')
@section('title', 'Jadwal Kerja')
@section('page-title', 'Jadwal Kerja')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Jadwal Kerja</span></div>
  <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:12px;">
    <div>
      <h1>Jadwal Kerja &amp; Lokasi Kantor</h1>
      <p class="text-muted" style="max-width:640px;margin-top:6px;line-height:1.5;">
        Atur jam kerja global, toleransi keterlambatan, serta titik geografis untuk validasi jarak presensi. Pengaturan ini berlaku untuk semua karyawan.
      </p>
    </div>
    <a href="{{ route('operator.qrcode') }}" class="btn btn-outline btn-sm" style="flex-shrink:0;">
      <i class="fa-solid fa-qrcode"></i> Kelola QR Code
    </a>
  </div>
</div>

@include('operator.partials.jadwal-kerja-settings')
@endsection

@push('scripts')
@include('operator.partials.office-map-script')
@endpush

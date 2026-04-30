{{-- ===================================================
     NOTIFIKASI — resources/views/notifikasi.blade.php
     =================================================== --}}
@extends('layouts.app')
@section('title','Notifikasi')
@section('page-title','Notifikasi')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Notifikasi</span></div>
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <h1>Notifikasi</h1>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
      @csrf
      <button class="btn btn-outline btn-sm" type="submit">
        <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
      </button>
    </form>
    @endif
  </div>
</div>

<div style="max-width:680px;">
  @forelse($notifikasi as $notif)
  @php
    $icons = [
      'presensi_berhasil' => ['fa-circle-check', 'teal'],
      'izin_disetujui'    => ['fa-file-circle-check', 'green'],
      'izin_ditolak'      => ['fa-file-circle-xmark', 'red'],
    ];
    [$ico, $col] = $icons[$notif->jenis] ?? ['fa-bell', 'muted'];
  @endphp
  <div class="card" style="margin-bottom:10px;{{ !$notif->is_read ? 'border-color:rgba(0,201,167,.3);' : '' }}">
    <div style="display:flex;gap:16px;padding:16px 20px;align-items:flex-start;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba({{ $col==='teal'?'0,201,167':($col==='green'?'0,224,150':'255,83,112') }},.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa-solid {{ $ico }}" style="color:var(--{{ $col }});font-size:1rem;"></i>
      </div>
      <div style="flex:1;">
        <div style="font-size:.9rem;{{ !$notif->is_read ? 'font-weight:600;' : 'color:var(--muted);' }}">
          {{ $notif->pesan }}
        </div>
        <div class="text-xs text-muted" style="margin-top:4px;">
          {{ $notif->created_at->translatedFormat('l, d F Y · H:i') }}
          · {{ $notif->created_at->diffForHumans() }}
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        @if(!$notif->is_read)
          <div style="width:8px;height:8px;border-radius:50%;background:var(--teal);flex-shrink:0;"></div>
          <form method="POST" action="{{ route('notifikasi.baca', $notif->id) }}">
            @csrf @method('PATCH')
            <button class="btn btn-ghost btn-sm" type="submit" title="Tandai dibaca" style="padding:4px 8px;">
              <i class="fa-solid fa-check"></i>
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:60px 20px;color:var(--muted);">
    <i class="fa-solid fa-bell-slash" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4;"></i>
    <div>Belum ada notifikasi</div>
  </div>
  @endforelse

  @if($notifikasi->hasPages())
  <div style="margin-top:16px;">{{ $notifikasi->links() }}</div>
  @endif
</div>
@endsection


{{-- ===================================================
     PROFIL — resources/views/karyawan/profil.blade.php
     =================================================== --}}

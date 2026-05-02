@extends('layouts.app')
@section('title','Notifikasi')
@section('page-title','Notifikasi')

@section('content')
@php
  $warnaBg = [
    'teal'   => '0,201,167',
    'green'  => '0,224,150',
    'red'    => '255,83,112',
    'amber'  => '245,158,11',
    'muted'  => '148,163,184',
    'blue'   => '59,130,246',
  ];
@endphp
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
    $w = in_array($notif->warna ?? '', array_keys($warnaBg), true) ? $notif->warna : 'teal';
    $rgb = $warnaBg[$w] ?? $warnaBg['teal'];
    $raw = trim((string) ($notif->ikon ?: 'fa-bell'));
    $raw = preg_replace('/^(fa-solid|fa-regular|fa-brands)\\s+/i', '', $raw);
    if ($raw === '') {
      $raw = 'fa-bell';
    } elseif (! str_starts_with($raw, 'fa-')) {
      $raw = 'fa-' . $raw;
    }
  @endphp
  <div class="card notif-card" style="margin-bottom:10px;{{ !$notif->is_read ? 'border-color:rgba(37,99,235,.35);' : '' }}">
    <div style="display:flex;gap:16px;padding:16px 20px;align-items:flex-start;">
      <div style="width:42px;height:42px;border-radius:10px;background:rgba({{ $rgb }},.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa-solid {{ $raw }}" style="font-size:1rem;color:var(--text-primary);opacity:.85;"></i>
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:.92rem;font-weight:600;{{ !$notif->is_read ? '' : 'color:var(--text-secondary);' }}">
          {{ $notif->judul }}
        </div>
        <div style="font-size:.88rem;margin-top:6px;line-height:1.45;color:var(--text-primary);{{ !$notif->is_read ? '' : 'opacity:.9;' }}">
          {{ $notif->pesan }}
        </div>
        <div class="text-xs text-muted" style="margin-top:8px;">
          {{ $notif->created_at->translatedFormat('l, d F Y · H:i') }}
          · {{ $notif->created_at->diffForHumans() }}
        </div>
        @if($notif->link)
        <div style="margin-top:10px;">
          <a href="{{ $notif->link }}" class="btn btn-ghost btn-sm" style="padding:4px 10px;font-size:.78rem;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka tautan terkait
          </a>
        </div>
        @endif
      </div>
      <div style="display:flex;align-items:flex-start;gap:8px;flex-shrink:0;">
        @if(!$notif->is_read)
          <div style="width:8px;height:8px;border-radius:50%;background:var(--blue-light);flex-shrink:0;margin-top:6px;"></div>
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

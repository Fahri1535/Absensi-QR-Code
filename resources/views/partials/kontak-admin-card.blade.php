@php
  $cfg = config('bantuan', []);
  $jamOp = $cfg['jam_operasional'] ?? '';
  $opWa = isset($cfg['kontak']['operator']['whatsapp']) ? trim((string) $cfg['kontak']['operator']['whatsapp']) : '';
  $hrdWa = isset($cfg['kontak']['hrd']['whatsapp']) ? trim((string) $cfg['kontak']['hrd']['whatsapp']) : '';
  $waPrefer = $opWa ?: $hrdWa;
  $waDigits = $waPrefer !== '' ? preg_replace('/\D+/', '', $waPrefer) : '';
@endphp
<div class="card" style="border-color:rgba(37,99,235,.35);background:rgba(37,99,235,.06);">
  <div class="card-header">
    <i class="fa-solid fa-headset" style="color:var(--blue-light);"></i>
    <h3>Butuh bantuan?</h3>
  </div>
  <div class="card-body-sm">
    <p class="text-sm text-muted" style="margin:0 0 12px;line-height:1.45;">
      Jika ada kendala teknis atau administrasi izin, lihat kontak admin dan panduan lengkap di halaman bantuan.
    </p>
    @if($jamOp !== '')
    <div class="text-xs text-muted" style="margin-bottom:12px;"><i class="fa-solid fa-clock" style="margin-right:6px;opacity:.8;"></i> {{ $jamOp }}</div>
    @endif
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
      <a href="{{ route('bantuan') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-address-book"></i> Kontak Admin
      </a>
      @if($waDigits !== '')
      <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
        <i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp cepat
      </a>
      @endif
    </div>
  </div>
</div>

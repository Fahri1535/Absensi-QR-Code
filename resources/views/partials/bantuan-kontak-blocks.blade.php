@php
  // Data sekarang dikirim dari Controller (BantuanController)
  // Jika variabel tidak ada (misal di halaman publik), ambil dari database atau config
  if (!isset($kontaks) || !isset($jamOp)) {
      $bantuan = \App\Models\Bantuan::all();
      $jamOp = \App\Models\BantuanSetting::where('key', 'jam_operasional')->first()?->value ?? config('bantuan.jam_operasional', '—');
      $sla = \App\Models\BantuanSetting::where('key', 'sla')->first()?->value ?? config('bantuan.sla', '');
      
      if ($bantuan->isEmpty()) {
          $kontaks = collect(config('bantuan.kontak', []));
      } else {
          $kontaks = $bantuan->keyBy('slug')->map(fn($item) => [
              'nama' => $item->nama,
              'deskripsi' => $item->deskripsi,
              'telepon' => $item->telepon,
              'whatsapp' => $item->whatsapp,
              'email' => $item->email,
          ]);
      }
  }

  $waDigits = static fn (?string $n): ?string =>
      $n && preg_replace('/\D+/', '', $n) !== '' ? preg_replace('/\D+/', '', $n) : null;
@endphp

<div class="card" style="margin-bottom:20px;border-color:rgba(0,201,167,.35);background:rgba(0,201,167,.05);">
  <div class="card-body-sm" style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="width:40px;height:40px;border-radius:10px;background:rgba(0,201,167,.18);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-clock text-teal"></i></span>
      <div>
        <div class="text-xs text-muted" style="text-transform:uppercase;letter-spacing:.08em;">Jam layanan umum</div>
        <div style="font-weight:600;margin-top:2px;">{{ $jamOp }}</div>
      </div>
    </div>
    @if($sla)
    <div style="flex:1;min-width:200px;padding-left:4px;color:var(--text-secondary);font-size:.88rem;line-height:1.45;">
      <i class="fa-solid fa-life-ring text-teal" style="margin-right:8px;"></i>{{ $sla }}
    </div>
    @endif
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-bottom:24px;" class="stagger">

  @foreach($kontaks as $slug => $row)
    @continue(empty($row) || (! is_array($row)))
    @php
      $telRaw = isset($row['telepon']) ? trim((string) $row['telepon']) : '';
      $waRaw = isset($row['whatsapp']) ? trim((string) $row['whatsapp']) : '';
      $mail = isset($row['email']) ? trim((string) $row['email']) : '';
      $nama = isset($row['nama']) ? (string) $row['nama'] : ucfirst((string) $slug);
      $desk = isset($row['deskripsi']) ? (string) $row['deskripsi'] : '';
      $waHref = $waDigits($waRaw);
    @endphp
    <div class="card animate-slideup" style="border-color:rgba(59,130,246,.22);{{ $slug === 'hrd' ? 'border-color:rgba(255,171,64,.35);' : '' }}">
      <div class="card-header">
        <i class="fa-solid {{ $slug === 'hrd' ? 'fa-user-tie text-amber' : 'fa-user-gear text-teal' }}"></i>
        <div>
          <h3 style="font-size:.98rem;margin:0;">{{ $nama }}</h3>
          @if($desk)<div class="text-xs text-muted" style="margin-top:4px;line-height:1.35;">{{ $desk }}</div>@endif
        </div>
      </div>
      <div class="card-body-sm" style="display:flex;flex-direction:column;gap:10px;">
        @if($telRaw !== '')
        <a href="tel:{{ preg_replace('/[^\d\+]/', '', $telRaw) }}" class="btn btn-outline btn-sm btn-full" style="justify-content:flex-start;text-align:left;">
          <i class="fa-solid fa-phone" style="width:22px;"></i> {{ $telRaw }}
        </a>
        @endif
        @if($waHref)
        <a href="https://wa.me/{{ $waHref }}" class="btn btn-outline btn-sm btn-full" target="_blank" rel="noopener" style="justify-content:flex-start;text-align:left;">
          <i class="fa-brands fa-whatsapp" style="width:22px;color:#25d366;"></i> WhatsApp
        </a>
        @elseif($telRaw === '' && $mail === '')
        <div class="text-sm text-muted" style="padding:8px 0;">Hubungi pusat atau isi kontak admin di file <code style="font-size:.78rem;">.env</code> (pakai ADMIN_OPERASI_* / ADMIN_HRD_*).</div>
        @endif
        @if($mail !== '')
        {{-- Tanpa target=_blank — mailto di tab baru sering kosong atau tidak ada respons --}}
        <a href="mailto:{{ $mail }}" class="btn btn-ghost btn-sm btn-full" style="justify-content:flex-start;text-align:left;">
          <i class="fa-solid fa-envelope" style="width:22px;"></i> {{ $mail }}
        </a>
        @endif
      </div>
    </div>
  @endforeach

</div>

<div class="card animate-slideup">
  <div class="card-header">
    <i class="fa-solid fa-clipboard-check text-teal"></i>
    <h3>Saat menghubungi admin, siapkan</h3>
  </div>
  <div class="card-body-sm">
    <ul style="margin:0;padding-left:22px;display:flex;flex-direction:column;gap:10px;color:var(--text-secondary);font-size:.9rem;line-height:1.5;">
      <li>Nama lengkap Anda di sistem</li>
      <li>Username login aplikasi presensi</li>
      <li>Tanggal &amp; perkiraan jam kejadian (misal presensi gagal)</li>
      <li>Jenis kendala (QR tidak terbaca, lokasi menolak, status izin, dll.)</li>
      <li>Screenshot atau pesan error di layar — jika ada</li>
    </ul>
    <hr class="divider" style="margin:18px 0;">
    <div class="text-muted text-sm" style="line-height:1.5;">
      <strong style="color:var(--text-primary);">Teknis aplikasi atau QR presensi</strong> — hubungi <strong style="color:var(--text-primary);">Operator</strong>.
      <strong style="color:var(--text-primary);">Izin atau kebijakan SDM</strong> — hubungi <strong style="color:var(--text-primary);">HRD</strong>.
    </div>
  </div>
</div>

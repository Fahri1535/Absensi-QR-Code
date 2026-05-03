@extends('layouts.app')
@section('title','Persetujuan Izin')
@section('page-title','Persetujuan Izin')

@section('content')
<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Persetujuan Izin</span></div>
  <h1>Persetujuan Pengajuan Izin</h1>
  <p class="text-muted">Tinjau dan setujui atau tolak pengajuan izin karyawan.</p>
</div>

{{-- Tab Filter --}}
<div class="tabs" style="margin-bottom:20px;">
  {{-- FIXED: gunakan ?status= bukan parameter route --}}
  <a class="tab {{ !request('status') || request('status') === 'pending' ? 'active' : '' }}"
     href="{{ route('hrd.izin', ['status'=>'pending']) }}">
    Pending
    @if(($countPending ?? 0) > 0)<span class="nav-badge" style="margin-left:4px;">{{ $countPending }}</span>@endif
  </a>
  <a class="tab {{ request('status') === 'disetujui' ? 'active' : '' }}"
     href="{{ route('hrd.izin', ['status'=>'disetujui']) }}">Disetujui</a>
  <a class="tab {{ request('status') === 'ditolak' ? 'active' : '' }}"
     href="{{ route('hrd.izin', ['status'=>'ditolak']) }}">Ditolak</a>
  <a class="tab {{ request('status') === 'semua' ? 'active' : '' }}"
     href="{{ route('hrd.izin', ['status'=>'semua']) }}">Semua</a>
</div>

@if(request('status','pending') === 'pending' && $izinList->isNotEmpty())
<div class="alert alert-warning" style="margin-bottom:20px;">
  <i class="fa-solid fa-triangle-exclamation"></i>
  Terdapat <strong>{{ $izinList->total() }}</strong> pengajuan yang menunggu persetujuan Anda.
</div>
@endif

{{-- Cards untuk Pending --}}
@if(request('status','pending') === 'pending')

<div style="display:flex;flex-direction:column;gap:14px;" class="stagger">
  @forelse($izinList as $izin)
  @php
    $emoji  = ['izin'=>'🏖','sakit'=>'🤒','cuti'=>'🌴','tugas_luar'=>'💼','alpa'=>'❌'][$izin->jenis_izin] ?? '📄';
    $durasi = \Carbon\Carbon::parse($izin->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($izin->tanggal_selesai)) + 1;
  @endphp
  <div class="card" style="border-color:rgba(255,171,64,.25);">
    <div style="padding:20px 24px;">
      <div style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;">

        <div style="width:52px;height:52px;border-radius:12px;background:rgba(255,171,64,.12);border:1px solid rgba(255,171,64,.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
          {{ $emoji }}
        </div>

        <div style="flex:1;min-width:200px;">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <h3 style="font-size:1rem;">{{ $izin->karyawan?->nama_lengkap }}</h3>
            <span class="badge badge-amber">Pending</span>
            <span class="text-xs text-muted">{{ $izin->created_at->diffForHumans() }}</span>
          </div>

          <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:10px;">
            <div>
              <div class="text-xs text-muted">Jenis</div>
              <div style="font-weight:600;font-size:.88rem;">{{ ucfirst(str_replace('_',' ',$izin->jenis_izin)) }}</div>
            </div>
            <div>
              <div class="text-xs text-muted">Periode</div>
              <div style="font-weight:600;font-size:.88rem;">
                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                @if($izin->tanggal_mulai != $izin->tanggal_selesai)
                  — {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}
                @endif
              </div>
            </div>
            <div>
              <div class="text-xs text-muted">Durasi</div>
              <div style="font-weight:600;font-size:.88rem;">{{ $durasi }} hari</div>
            </div>
          </div>

          @if($izin->keterangan)
          <div style="background:var(--bg-soft);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:12px;">
            <div class="text-xs text-muted" style="margin-bottom:4px;">Keterangan:</div>
            <div style="font-size:.88rem;">{{ $izin->keterangan }}</div>
          </div>
          @endif

          @if($izin->lampiran)
          <div style="margin-bottom:12px;">
            <a href="{{ asset('storage/'.$izin->lampiran) }}" target="_blank"
               class="btn btn-ghost btn-sm" style="padding:6px 12px;">
              <i class="fa-solid fa-file-arrow-down" style="color:var(--teal);"></i>
              Lihat Lampiran
            </a>
          </div>
          @endif

          {{-- FIXED: input name = 'status' (sesuai IzinController yang sudah difix) --}}
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <form method="POST" action="{{ route('hrd.izin.approve', $izin->id) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="disetujui">
              <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-circle-check"></i> Setujui
              </button>
            </form>

            <button class="btn btn-danger" type="button"
                    onclick="showTolakModal({{ $izin->id }})">
              <i class="fa-solid fa-circle-xmark"></i> Tolak
            </button>

            <span class="text-xs text-muted">
              <i class="fa-solid fa-circle-info"></i>
              Notifikasi otomatis dikirim ke karyawan
            </span>
          </div>
        </div>

      </div>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:60px;color:var(--muted);">
    <i class="fa-solid fa-clipboard-check" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4;"></i>
    Tidak ada pengajuan pending
  </div>
  @endforelse
</div>

{{-- Table view untuk status lain --}}
@else

<div class="card animate-slideup">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Karyawan</th>
          <th>Jenis</th>
          <th>Periode</th>
          <th>Keterangan</th>
          <th>Status</th>
          <th>Diproses</th>
        </tr>
      </thead>
      <tbody>
        @forelse($izinList as $izin)
        @php
          $emoji = ['izin'=>'🏖','sakit'=>'🤒','cuti'=>'🌴','tugas_luar'=>'💼','alpa'=>'❌'][$izin->jenis_izin] ?? '📄';
          $sc    = ['pending'=>'amber','disetujui'=>'green','ditolak'=>'red'][$izin->status] ?? 'muted';
        @endphp
        <tr>
          <td style="font-weight:600;">{{ $izin->karyawan?->nama_lengkap }}</td>
          <td>{{ $emoji }} {{ ucfirst(str_replace('_',' ',$izin->jenis_izin)) }}</td>
          <td>
            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
            @if($izin->tanggal_mulai != $izin->tanggal_selesai)
              <br><span class="text-xs text-muted">s.d. {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}</span>
            @endif
          </td>
          <td style="max-width:180px;">
            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px;" title="{{ $izin->keterangan }}">
              {{ $izin->keterangan ?? '—' }}
            </div>
          </td>
          <td><span class="badge badge-{{ $sc }}">{{ ucfirst($izin->status) }}</span></td>
          <td class="text-muted text-sm">{{ $izin->updated_at->format('d M Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted);">Tidak ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($izinList->hasPages())
  <div class="card-footer">{{ $izinList->appends(request()->query())->links() }}</div>
  @endif
</div>

@endif

{{-- Tolak Modal --}}
<div id="tolak-modal" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(15,23,42,.72);backdrop-filter:blur(5px);align-items:center;justify-content:center;padding:20px;" role="dialog" aria-modal="true" aria-labelledby="tolak-modal-title">
  <div style="background:var(--bg-card);border:1px solid var(--border-strong);border-radius:20px;width:100%;max-width:480px;box-shadow:var(--shadow);">
    <div style="padding:22px 24px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
      <i class="fa-solid fa-circle-xmark" style="color:var(--red);"></i>
      <h3 id="tolak-modal-title" style="flex:1;font-size:1.05rem;margin:0;color:var(--text-primary);">Tolak Pengajuan Izin</h3>
      <button type="button" onclick="closeTolakModal()" style="width:32px;height:32px;border-radius:8px;background:var(--bg-input);border:1px solid var(--border);color:var(--text-secondary);cursor:pointer;">✕</button>
    </div>
    <form method="POST" id="tolak-form" action="">
      @csrf @method('PATCH')
      <input type="hidden" name="status" value="ditolak">
      <div style="padding:24px;">
        <div class="form-group">
          <label class="form-label">Alasan Penolakan (opsional)</label>
          <textarea name="catatan_hrd" class="form-control" rows="3"
                    placeholder="Tuliskan alasan penolakan..."></textarea>
        </div>
        <div class="alert alert-warning">
          <i class="fa-solid fa-triangle-exclamation"></i>
          Karyawan akan menerima notifikasi bahwa izinnya ditolak.
        </div>
      </div>
      <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn btn-outline" onclick="closeTolakModal()">Batal</button>
        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-circle-xmark"></i> Tolak Izin</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  var approveTpl = @json(route('hrd.izin.approve', ['id' => 999987654321]));
  window.showTolakModal = function (id) {
    var form = document.getElementById('tolak-form');
    if (form) form.action = approveTpl.replace('999987654321', String(id));
    var modal = document.getElementById('tolak-modal');
    if (modal) modal.style.display = 'flex';
  };
  window.closeTolakModal = function () {
    var modal = document.getElementById('tolak-modal');
    if (modal) modal.style.display = 'none';
  };
  var modal = document.getElementById('tolak-modal');
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeTolakModal();
    });
  }
})();
</script>
@endpush
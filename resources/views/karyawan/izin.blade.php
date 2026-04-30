@extends('layouts.app')

@section('title', 'Pengajuan Izin')
@section('page-title', 'Pengajuan Izin')

@section('content')

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Pengajuan Izin</span></div>
  <h1>Pengajuan Izin</h1>
  <p class="text-muted">Ajukan izin, cuti, atau sakit. Persetujuan dari HRD akan dikirim via notifikasi.</p>
</div>

<div style="display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start;">

  {{-- LEFT: Form + Riwayat --}}
  <div>
    {{-- Tab --}}
    <div class="tabs" style="margin-bottom:20px;">
      <button class="tab active" onclick="switchTab('form', this)">Ajukan Baru</button>
      <button class="tab"        onclick="switchTab('riwayat', this)">Riwayat Pengajuan</button>
    </div>

    {{-- Form Tab --}}
    <div id="tab-form">
      <div class="card">
        <div class="card-header">
          <i class="fa-solid fa-file-circle-plus text-teal"></i>
          <h3>Form Pengajuan Izin</h3>
        </div>
        <div class="card-body">

          @if($errors->any())
          <div class="alert alert-danger" style="margin-bottom:20px;">
            <div>
              @foreach($errors->all() as $e)
              <div><i class="fa-solid fa-circle-xmark"></i> {{ $e }}</div>
              @endforeach
            </div>
          </div>
          @endif

          <form method="POST" action="{{ route('karyawan.izin.store') }}" enctype="multipart/form-data" id="izinForm">
            @csrf

            {{-- Jenis Izin --}}
            <div class="form-group">
              <label class="form-label">Jenis Izin <span style="color:var(--red);">*</span></label>
              <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                @foreach([
                  ['izin',       '🏖', 'Izin',        'Keperluan pribadi'],
                  ['sakit',      '🤒', 'Sakit',       'Tidak masuk sakit'],
                  ['cuti',       '🌴', 'Cuti',        'Cuti tahunan'],
                  ['tugas_luar', '💼', 'Tugas Luar',  'Dinas luar kantor'],
                  ['alpa',       '❌', 'Tanpa Keterangan', ''],
                ] as [$val, $emoji, $label, $sub])
                <label class="izin-option" style="cursor:pointer;">
                  <input type="radio" name="jenis_izin" value="{{ $val }}" style="display:none;"
                    {{ old('jenis_izin') === $val ? 'checked' : '' }}
                    onchange="document.querySelectorAll('.izin-option').forEach(e=>e.classList.remove('selected')); this.closest('.izin-option').classList.add('selected'); toggleSakit('{{ $val }}');">
                  <div class="izin-card {{ old('jenis_izin') === $val ? 'selected' : '' }}">
                    <span style="font-size:1.4rem;">{{ $emoji }}</span>
                    <div style="font-weight:600;font-size:.85rem;">{{ $label }}</div>
                    @if($sub)<div style="font-size:.72rem;color:var(--muted);">{{ $sub }}</div>@endif
                  </div>
                </label>
                @endforeach
              </div>
            </div>

            {{-- Tanggal --}}
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Tanggal Mulai <span style="color:var(--red);">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control"
                       value="{{ old('tanggal_mulai', date('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}" required>
              </div>
              <div class="form-group">
                <label class="form-label">Tanggal Selesai <span style="color:var(--red);">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control"
                       value="{{ old('tanggal_selesai', date('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}" required>
              </div>
            </div>

            {{-- Keterangan --}}
            <div class="form-group">
              <label class="form-label">Keterangan / Alasan <span style="color:var(--red);">*</span></label>
              <textarea name="keterangan" class="form-control" rows="4"
                        placeholder="Tuliskan alasan izin Anda..." required>{{ old('keterangan') }}</textarea>
            </div>

            {{-- Bukti (untuk sakit) --}}
            <div class="form-group" id="bukti-group" style="{{ old('jenis_izin') === 'sakit' ? '' : 'display:none;' }}">
              <label class="form-label">Bukti Pendukung <span style="color:var(--muted);">(Opsional)</span></label>
              <div style="position:relative;">
                <input type="file" name="bukti_pendukung" id="buktiInput"
                       accept=".jpg,.jpeg,.png,.pdf"
                       style="position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;"
                       onchange="previewFile(this)">
                <div id="bukti-placeholder" style="border:2px dashed var(--border);border-radius:var(--radius-sm);padding:24px;text-align:center;color:var(--muted);">
                  <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                  <div style="font-size:.85rem;">Klik atau seret file ke sini</div>
                  <div class="text-xs" style="margin-top:4px;">JPG, PNG, PDF — maks 2MB</div>
                </div>
                <div id="bukti-preview" style="display:none;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;display:none;align-items:center;gap:12px;">
                  <i class="fa-solid fa-file-check" style="color:var(--teal);font-size:1.2rem;"></i>
                  <span id="bukti-filename" style="font-size:.85rem;"></span>
                  <button type="button" onclick="clearFile()" class="btn btn-ghost btn-sm" style="margin-left:auto;">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
              <p class="text-xs text-muted" style="margin-top:6px;">
                <i class="fa-solid fa-circle-info"></i> Untuk izin sakit, sertakan surat dokter jika ada.
              </p>
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
              <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Izin
              </button>
              <button type="reset" class="btn btn-outline" onclick="resetForm()">
                <i class="fa-solid fa-rotate-left"></i> Reset
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>

    {{-- Riwayat Tab --}}
    <div id="tab-riwayat" style="display:none;">
      <div class="card">
        <div class="card-header">
          <i class="fa-solid fa-clock-rotate-left text-teal"></i>
          <h3>Riwayat Pengajuan Izin</h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Jenis</th>
                <th>Periode</th>
                <th>Durasi</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($izinList as $izin)
              @php
                $emoji = ['izin'=>'🏖','sakit'=>'🤒','cuti'=>'🌴','tugas_luar'=>'💼','alpa'=>'❌'][$izin->jenis_izin] ?? '📄';
                $durasi = \Carbon\Carbon::parse($izin->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($izin->tanggal_selesai)) + 1;
                $sc = ['pending'=>'amber','disetujui'=>'green','ditolak'=>'red'][$izin->status] ?? 'muted';
              @endphp
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span>{{ $emoji }}</span>
                    <span style="font-weight:600;">{{ ucfirst(str_replace('_',' ',$izin->jenis_izin)) }}</span>
                  </div>
                </td>
                <td>
                  <div>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}</div>
                  @if($izin->tanggal_mulai !== $izin->tanggal_selesai)
                  <div class="text-xs text-muted">s.d. {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}</div>
                  @endif
                </td>
                <td>{{ $durasi }} hari</td>
                <td style="max-width:160px;">
                  <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;" title="{{ $izin->keterangan }}">
                    {{ $izin->keterangan }}
                  </div>
                </td>
                <td><span class="badge badge-{{ $sc }}">{{ ucfirst($izin->status) }}</span></td>
                <td>
                  @if($izin->status === 'pending')
                  <form method="POST" action="{{ route('karyawan.izin.cancel', $izin->id) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa-solid fa-xmark"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted);">Belum ada riwayat pengajuan izin</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($izinList->hasPages())
    <div class="card-footer">{{ $izinList->links() }}</div>
@endif
      </div>
    </div>

  </div>

  {{-- RIGHT: Info Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Status Cuti --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-calendar-days text-teal"></i>
        <h3>Sisa Cuti Tahun Ini</h3>
      </div>
      <div class="card-body" style="text-align:center;">
        <div style="font-family:'Syne',sans-serif;font-size:3rem;font-weight:800;color:var(--teal);">
          {{ $sisaCuti ?? 12 }}
        </div>
        <div class="text-muted text-sm">hari dari {{ $totalCuti ?? 12 }} hari</div>
        <div style="background:var(--border);height:6px;border-radius:3px;margin-top:14px;overflow:hidden;">
          <div style="height:100%;border-radius:3px;background:var(--teal);width:{{ (($sisaCuti??12)/($totalCuti??12))*100 }}%;"></div>
        </div>
      </div>
    </div>

    {{-- Rekap Izin Bulan Ini --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-chart-bar text-teal"></i>
        <h3>Rekap Bulan Ini</h3>
      </div>
      <div class="card-body-sm">
        @foreach([
          ['Izin','🏖',$rekapBulan['izin']??0],
          ['Sakit','🤒',$rekapBulan['sakit']??0],
          ['Cuti','🌴',$rekapBulan['cuti']??0],
          ['Tugas Luar','💼',$rekapBulan['tugas_luar']??0],
        ] as [$label,$emoji,$count])
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
          <div style="display:flex;align-items:center;gap:8px;">
            <span>{{ $emoji }}</span>
            <span style="font-size:.85rem;">{{ $label }}</span>
          </div>
          <span style="font-family:'Syne',sans-serif;font-weight:700;color:var(--teal);">{{ $count }} hari</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Kebijakan --}}
    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-circle-info text-teal"></i>
        <h3>Ketentuan Izin</h3>
      </div>
      <div class="card-body-sm">
        <div style="display:flex;flex-direction:column;gap:10px;">
          @foreach([
            'Pengajuan minimal 1 hari sebelumnya (kecuali sakit mendadak)',
            'Izin sakit dengan 2+ hari wajib menyertakan surat dokter',
            'Cuti tahunan diberikan 12 hari per tahun',
            'Persetujuan izin dilakukan oleh HRD',
            'Notifikasi status pengajuan dikirim otomatis',
          ] as $item)
          <div style="display:flex;gap:8px;font-size:.82rem;color:var(--muted);">
            <i class="fa-solid fa-circle-dot" style="color:var(--teal);margin-top:3px;flex-shrink:0;font-size:.65rem;"></i>
            {{ $item }}
          </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>

</div>

@endsection

@push('styles')
<style>
.izin-card {
  border: 1px solid var(--border);
  border-radius: var(--radius-sm); padding: 14px 10px;
  text-align: center; background: var(--card-bg);
  transition: all var(--transition); cursor: pointer;
  display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.izin-card:hover, .izin-card.selected {
  border-color: var(--teal);
  background: var(--teal-glow);
  box-shadow: 0 0 0 2px rgba(0,201,167,.2);
}
</style>
@endpush

@push('scripts')
<script>
function switchTab(tab, btn) {
  document.getElementById('tab-form').style.display    = tab === 'form'    ? 'block' : 'none';
  document.getElementById('tab-riwayat').style.display = tab === 'riwayat' ? 'block' : 'none';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}

function toggleSakit(val) {
  document.getElementById('bukti-group').style.display = val === 'sakit' ? 'block' : 'none';
}

function previewFile(input) {
  if (input.files && input.files[0]) {
    document.getElementById('bukti-placeholder').style.display = 'none';
    const prev = document.getElementById('bukti-preview');
    prev.style.display = 'flex';
    document.getElementById('bukti-filename').textContent = input.files[0].name;
  }
}

function clearFile() {
  document.getElementById('buktiInput').value = '';
  document.getElementById('bukti-placeholder').style.display = 'block';
  document.getElementById('bukti-preview').style.display = 'none';
}

function resetForm() {
  document.querySelectorAll('.izin-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('bukti-group').style.display = 'none';
  clearFile();
}

document.getElementById('izinForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
});
</script>

<style>
  body, .card, .card-body, .page-content, 
  .table, .table th, .table td, .badge, .btn,
  h1, h2, h3, h4, p, span, div {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif !important;
    letter-spacing: normal !important;
  }
</style>

@endpush

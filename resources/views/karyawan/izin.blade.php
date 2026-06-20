@extends('layouts.app')

@section('title', 'Pengajuan Izin')
@section('page-title', 'Pengajuan Izin')

@section('content')

@php
  $role = auth()->user()->role;
  $storeRoute = $role === 'hrd' ? 'hrd.izin_pribadi.store' : 'karyawan.izin.store';
  $cancelRoute = $role === 'hrd' ? 'hrd.izin_pribadi.cancel' : 'karyawan.izin.cancel';
@endphp

<div class="page-header">
  <div class="breadcrumb">Beranda / <span>Pengajuan Izin</span></div>
  <h1>Pengajuan Izin</h1>
  <p class="text-muted">Ajukan izin, cuti, atau sakit. Persetujuan dari HRD akan dikirim via notifikasi. Satu karyawan maksimal satu pengajuan per hari kalender.</p>
</div>

@if(isset($sedangAlpaHariIni) && $sedangAlpaHariIni)
<div class="alert alert-danger alert-permanent" style="margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-user-xmark" style="font-size:1.5rem;"></i>
    <div>
      <strong>Anda tercatat alpa hari ini!</strong><br>
      Anda tidak dapat mengajukan izin untuk hari ini. Silakan lakukan presensi atau hubungi HRD untuk klarifikasi ketidakhadiran Anda.
    </div>
  </div>
</div>
@endif

@if(isset($sedangIzin) && $sedangIzin)
<div class="alert alert-info alert-permanent" style="margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-calendar-check" style="font-size:1.5rem;"></i>
    <div>
      <strong>Anda sedang dalam periode izin!</strong><br>
      Izin {{ str_replace('_', ' ', $sedangIzin->jenis_izin) }} berlaku dari {{ $sedangIzin->tanggal_mulai->format('d M Y') }} sampai {{ $sedangIzin->tanggal_selesai->format('d M Y') }}.
    </div>
  </div>
</div>
@endif

@if(isset($izinPending) && $izinPending)
<div class="alert alert-warning alert-permanent" style="margin-bottom:20px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-clock" style="font-size:1.5rem;"></i>
    <div>
      <strong>Anda memiliki izin yang menunggu persetujuan!</strong><br>
      Izin {{ str_replace('_', ' ', $izinPending->jenis_izin) }} dari {{ $izinPending->tanggal_mulai->format('d M Y') }} sampai {{ $izinPending->tanggal_selesai->format('d M Y') }} masih dalam proses. Silakan tunggu hingga izin tersebut diproses sebelum mengajukan izin baru.
    </div>
  </div>
</div>
@endif

<div class="animate-slideup">
<div class="izin-main-grid">

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

          @if(session('success'))
          <div class="alert alert-success" style="margin-bottom:20px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
          </div>
          @endif

          @php
            $formDisabled = (isset($izinPending) && $izinPending)
                          || (isset($sedangIzin) && $sedangIzin)
                          || (isset($sedangAlpaHariIni) && $sedangAlpaHariIni);
          @endphp
          <form method="POST" action="{{ route($storeRoute) }}" enctype="multipart/form-data" id="izinForm" {{ $formDisabled ? 'style=pointer-events:none;opacity:0.6;' : '' }}>
            @csrf

            {{-- Jenis Izin --}}
            <div class="form-group">
              <label class="form-label">Jenis Izin <span style="color:var(--red);">*</span></label>
              
              @if($role === 'hrd')
                {{-- HRD: Semua izin di satu kategori "Otomatis Disetujui" --}}
                <div>
                  <div style="font-size:.85rem;color:var(--teal);font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-check"></i> Otomatis Disetujui
                  </div>
                  <div class="izin-type-grid">
                    @foreach([
                      ['izin',       '🏖', 'Izin',        'Keperluan pribadi'],
                      ['sakit',      '🤒', 'Sakit',       'Tidak masuk sakit'],
                      ['cuti',       '🌴', 'Cuti',        'Cuti tahunan'],
                      ['pulang_cepat','🏃', 'Pulang Cepat', 'Izin keluar awal'],
                      ['lembur',     '⏰', 'Lembur',       'Kerja tambahan'],
                      ['tugas_luar', '💼', 'Tugas Luar',  'Dinas luar kantor'],
                    ] as [$val, $emoji, $label, $sub])
                    <label class="izin-option" style="{{ $formDisabled ? 'cursor:not-allowed;' : 'cursor:pointer;' }}">
                      <input type="radio" name="jenis_izin" value="{{ $val }}" style="display:none;"
                        {{ old('jenis_izin') === $val ? 'checked' : '' }}
                        onchange="document.querySelectorAll('.izin-card').forEach(function(c){c.classList.remove('selected');}); var card=this.closest('.izin-option')&&this.closest('.izin-option').querySelector('.izin-card'); if(card) card.classList.add('selected'); toggleSakit(this.value);"
                        {{ $formDisabled ? 'disabled' : '' }}>
                      <div class="izin-card {{ old('jenis_izin') === $val ? 'selected' : '' }}">
                        <span style="font-size:1.4rem;">{{ $emoji }}</span>
                        <div style="font-weight:600;font-size:.85rem;">{{ $label }}</div>
                        @if($sub)<div style="font-size:.72rem;color:var(--muted);">{{ $sub }}</div>@endif
                      </div>
                    </label>
                    @endforeach
                  </div>
                </div>
              @else
                {{-- Karyawan: Tetap dua kategori --}}
                <div style="margin-bottom:16px;">
                  <div style="font-size:.85rem;color:var(--teal);font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-check"></i> Membutuhkan Persetujuan
                  </div>
                  <div class="izin-type-grid">
                    @foreach([
                      ['izin',       '🏖', 'Izin',        'Keperluan pribadi'],
                      ['sakit',      '🤒', 'Sakit',       'Tidak masuk sakit'],
                      ['cuti',       '🌴', 'Cuti',        'Cuti tahunan'],
                      ['pulang_cepat','🏃', 'Pulang Cepat', 'Izin keluar awal'],
                    ] as [$val, $emoji, $label, $sub])
                    <label class="izin-option" style="{{ $formDisabled ? 'cursor:not-allowed;' : 'cursor:pointer;' }}">
                      <input type="radio" name="jenis_izin" value="{{ $val }}" style="display:none;"
                        {{ old('jenis_izin') === $val ? 'checked' : '' }}
                        onchange="document.querySelectorAll('.izin-card').forEach(function(c){c.classList.remove('selected');}); var card=this.closest('.izin-option')&&this.closest('.izin-option').querySelector('.izin-card'); if(card) card.classList.add('selected'); toggleSakit(this.value);"
                        {{ $formDisabled ? 'disabled' : '' }}>
                      <div class="izin-card {{ old('jenis_izin') === $val ? 'selected' : '' }}">
                        <span style="font-size:1.4rem;">{{ $emoji }}</span>
                        <div style="font-weight:600;font-size:.85rem;">{{ $label }}</div>
                        @if($sub)<div style="font-size:.72rem;color:var(--muted);">{{ $sub }}</div>@endif
                      </div>
                    </label>
                    @endforeach
                  </div>
                </div>
                
                <div>
                  <div style="font-size:.85rem;color:var(--muted);font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-xmark"></i> Tidak Membutuhkan Persetujuan
                  </div>
                  <div class="izin-type-grid">
                    @foreach([
                      ['lembur',     '⏰', 'Lembur',       'Kerja tambahan'],
                      ['tugas_luar', '💼', 'Tugas Luar',  'Dinas luar kantor'],
                    ] as [$val, $emoji, $label, $sub])
                    <label class="izin-option" style="{{ $formDisabled ? 'cursor:not-allowed;' : 'cursor:pointer;' }}">
                      <input type="radio" name="jenis_izin" value="{{ $val }}" style="display:none;"
                        {{ old('jenis_izin') === $val ? 'checked' : '' }}
                        onchange="document.querySelectorAll('.izin-card').forEach(function(c){c.classList.remove('selected');}); var card=this.closest('.izin-option')&&this.closest('.izin-option').querySelector('.izin-card'); if(card) card.classList.add('selected'); toggleSakit(this.value);"
                        {{ $formDisabled ? 'disabled' : '' }}>
                      <div class="izin-card {{ old('jenis_izin') === $val ? 'selected' : '' }}">
                        <span style="font-size:1.4rem;">{{ $emoji }}</span>
                        <div style="font-weight:600;font-size:.85rem;">{{ $label }}</div>
                        @if($sub)<div style="font-size:.72rem;color:var(--muted);">{{ $sub }}</div>@endif
                      </div>
                    </label>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            {{-- Tanggal --}}
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Tanggal Mulai <span style="color:var(--red);">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control"
                       value="{{ old('tanggal_mulai', date('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}" required
                       {{ $formDisabled ? 'disabled' : '' }}>
              </div>
              <div class="form-group">
                <label class="form-label">Tanggal Selesai <span style="color:var(--red);">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control"
                       value="{{ old('tanggal_selesai', date('Y-m-d')) }}"
                       min="{{ date('Y-m-d') }}" required
                       {{ $formDisabled ? 'disabled' : '' }}>
              </div>
            </div>

            {{-- Keterangan --}}
            <div class="form-group">
              <label class="form-label">Keterangan / Alasan <span style="color:var(--red);">*</span></label>
              <textarea name="keterangan" class="form-control" rows="4"
                        placeholder="Tuliskan alasan izin Anda..." required
                        {{ $formDisabled ? 'disabled' : '' }}>{{ old('keterangan') }}</textarea>
            </div>

            {{-- Bukti (untuk sakit) --}}
            <div class="form-group" id="bukti-group" style="{{ old('jenis_izin') === 'sakit' ? '' : 'display:none;' }}">
              <label class="form-label">Bukti Pendukung <span style="color:var(--red);" id="buktiLabelSpan">(Wajib untuk sakit > 2 hari)</span></label>
              <div style="position:relative;">
                <input type="file" name="lampiran" id="buktiInput"
                       accept=".jpg,.jpeg,.png,.pdf"
                       style="position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;"
                       onchange="previewFile(this)"
                       {{ $formDisabled ? 'disabled' : '' }}>
                <div id="bukti-placeholder" style="border:2px dashed var(--border);border-radius:var(--radius-sm);padding:24px;text-align:center;color:var(--muted);">
                  <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                  <div style="font-size:.85rem;">Klik atau seret file ke sini</div>
                  <div class="text-xs" style="margin-top:4px;">JPG, PNG, PDF — maks 5MB</div>
                </div>
                <div id="bukti-preview" style="display:none;background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;display:none;align-items:center;gap:12px;">
                  <i class="fa-solid fa-file-check" style="color:var(--teal);font-size:1.2rem;"></i>
                  <span id="bukti-filename" style="font-size:.85rem;"></span>
                  <button type="button" onclick="clearFile()" class="btn btn-ghost btn-sm" style="margin-left:auto;" {{ $formDisabled ? 'disabled' : '' }}>
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>
              <p class="text-xs text-muted" style="margin-top:6px;">
                <i class="fa-solid fa-circle-info"></i> Izin sakit lebih dari 2 hari wajib menyertakan surat dokter.
              </p>
            </div>

            <div class="izin-btn-row" style="display:flex;gap:10px;margin-top:4px;">
              <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" {{ $formDisabled ? 'disabled' : '' }}>
                <i class="fa-solid fa-paper-plane"></i> Ajukan Izin
              </button>
              <button type="reset" class="btn btn-outline" onclick="resetForm()" {{ $formDisabled ? 'disabled' : '' }}>
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
              @forelse($riwayatIzin as $izin)
              @php
                $emoji = [
                    'izin'=>'🏖',
                    'sakit'=>'🤒',
                    'cuti'=>'🌴',
                    'pulang_cepat'=>'🏃',
                    'lembur'=>'⏰',
                    'tugas_luar'=>'💼'
                ][$izin->jenis_izin] ?? '📄';
                $tMulai = $izin->tanggal_mulai;
                $tAkhir = $izin->tanggal_selesai;
                $durasi = $tMulai->diffInDays($tAkhir) + 1;
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
                  <div>{{ $tMulai->format('d M Y') }}</div>
                  @if(!$tMulai->isSameDay($tAkhir))
                  <div class="text-xs text-muted">s.d. {{ $tAkhir->format('d M Y') }}</div>
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
                  <form method="POST" action="{{ route($cancelRoute, $izin->id) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
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
        @if(isset($riwayatIzin) && $riwayatIzin->hasPages())
        <div class="card-footer">{{ $riwayatIzin->links() }}</div>
        @endif
      </div>
    </div>

  </div>

  {{-- RIGHT: Info Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    @include('partials.kontak-admin-card')

    {{-- Status Cuti --}}
    <div class="card" style="border-left: 4px solid var(--teal); background: linear-gradient(to bottom right, rgba(0,201,167,0.05), transparent);">
      <div class="card-header">
        <i class="fa-solid fa-calendar-days text-teal"></i>
        <h3>Sisa Cuti Tahun Ini</h3>
      </div>
      <div class="card-body" style="text-align:center;">
        <div style="font-family:'DM Sans',sans-serif;font-size:3rem;font-weight:800;color:var(--teal);">
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
          <span style="font-family:'DM Sans',sans-serif;font-weight:700;color:var(--teal);">{{ $count }} hari</span>
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
          @if($role === 'hrd')
            @foreach([
              'Pengajuan minimal 1 hari sebelumnya (kecuali sakit mendadak)',
              'Izin sakit dengan 2+ hari wajib menyertakan surat dokter',
              'Cuti tahunan diberikan 12 hari per tahun',
              'Semua pengajuan izin otomatis disetujui',
              'Notifikasi status pengajuan dikirim otomatis',
            ] as $item)
              <div style="display:flex;gap:8px;font-size:.82rem;color:var(--muted);">
                <i class="fa-solid fa-circle-dot" style="color:var(--teal);margin-top:3px;flex-shrink:0;font-size:.65rem;"></i>
                {{ $item }}
              </div>
            @endforeach
          @else
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
          @endif
        </div>
      </div>
    </div>

  </div>

</div>
</div>

@endsection

@push('styles')
<style>
/* Main 2-column layout */
.izin-main-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 24px;
  align-items: start;
}

/* Izin type cards grid */
.izin-type-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

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

/* ── Mobile: stack everything vertically ── */
@media (max-width: 1024px) {
  .izin-main-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 768px) {
  .izin-main-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .izin-type-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  /* Tabs: allow horizontal scroll on small screens */
  .tabs {
    overflow-x: auto;
    flex-wrap: nowrap;
    -webkit-overflow-scrolling: touch;
  }
  .tab {
    flex-shrink: 0;
    font-size: .8rem;
    padding: 8px 12px;
  }
  /* Buttons stack */
  .izin-btn-row {
    flex-direction: column;
    align-items: stretch;
  }
  .izin-btn-row .btn {
    width: 100%;
    justify-content: center;
  }
}
@media (max-width: 480px) {
  .izin-type-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  .izin-card {
    padding: 10px 6px;
  }
  .izin-card span[style*="font-size:1.4rem"] {
    font-size: 1.2rem !important;
  }
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
  updateBuktiLabel();
}

function updateBuktiLabel() {
  const jenisIzin = document.querySelector('input[name="jenis_izin"]:checked')?.value;
  const tglMulai = document.querySelector('input[name="tanggal_mulai"]')?.value;
  const tglSelesai = document.querySelector('input[name="tanggal_selesai"]')?.value;
  const labelSpan = document.getElementById('buktiLabelSpan');
  
  if (jenisIzin === 'sakit' && tglMulai && tglSelesai) {
    const mulai = new Date(tglMulai);
    const selesai = new Date(tglSelesai);
    const diffTime = Math.abs(selesai - mulai);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    
    if (diffDays > 2) {
      labelSpan.textContent = '(Wajib untuk sakit > 2 hari)';
      labelSpan.style.color = 'var(--red)';
    } else {
      labelSpan.textContent = '(Opsional untuk sakit ≤ 2 hari)';
      labelSpan.style.color = 'var(--muted)';
    }
  } else {
    labelSpan.textContent = '(Wajib untuk sakit > 2 hari)';
    labelSpan.style.color = 'var(--red)';
  }
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
  updateBuktiLabel();
}

document.getElementById('izinForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
});

document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('izinForm');
  if (!form) return;
  var sel = form.querySelector('input[name="jenis_izin"]:checked');
  if (sel) {
    document.querySelectorAll('.izin-card').forEach(function(c) { c.classList.remove('selected'); });
    var opt = sel.closest('.izin-option');
    var card = opt && opt.querySelector('.izin-card');
    if (card) card.classList.add('selected');
    toggleSakit(sel.value);
  }
  
  // Add event listeners to date inputs to update the label
  const tglMulaiInput = document.querySelector('input[name="tanggal_mulai"]');
  const tglSelesaiInput = document.querySelector('input[name="tanggal_selesai"]');
  if (tglMulaiInput) {
    tglMulaiInput.addEventListener('change', updateBuktiLabel);
  }
  if (tglSelesaiInput) {
    tglSelesaiInput.addEventListener('change', updateBuktiLabel);
  }
});
</script>
@endpush


{{-- Form jadwal + lokasi + ringkasan window (halaman Jadwal Kerja; window QR mengikuti pengaturan yang sama) --}}
@php
  $win = $jadwal?->presensiWindows() ?? [];
  $tol = $jadwal?->toleransi_menit ?? 5;
  $awalMasuk = $jadwal?->masuk_lebih_awal_menit ?? 15;
  $awalPulang = $jadwal?->pulang_lebih_awal_menit ?? 30;
  $officeHasCoords = $jadwal?->kantor_latitude !== null && $jadwal?->kantor_longitude !== null;
@endphp

<form method="POST" action="{{ route('operator.jadwal.update') }}" class="jadwal-settings-form">
  @csrf @method('PATCH')

  <div class="responsive-grid stagger">

    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-clock text-teal"></i>
        <h3>Pengaturan Jadwal Kerja</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jam Masuk</label>
            <input type="time" name="jam_masuk" class="form-control" value="{{ old('jam_masuk', $jadwal?->jam_masuk ? \Carbon\Carbon::parse($jadwal->jam_masuk)->format('H:i') : '08:00') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Jam Pulang</label>
            <input type="time" name="jam_pulang" class="form-control" value="{{ old('jam_pulang', $jadwal?->jam_pulang ? \Carbon\Carbon::parse($jadwal->jam_pulang)->format('H:i') : '17:00') }}" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Toleransi Keterlambatan (menit)</label>
            <input type="number" name="toleransi_menit" class="form-control"
                   value="{{ old('toleransi_menit', $tol) }}" min="0" max="120" required>
            <span class="form-hint">Setelah jam masuk — masih dihitung tepat waktu hingga batas ini.</span>
          </div>
          <div class="form-group">
            <label class="form-label">Hari Kerja</label>
            <input type="text" name="hari_kerja" class="form-control"
                   value="{{ old('hari_kerja', $jadwal?->hari_kerja ?? 'Senin - Jumat') }}"
                   placeholder="contoh: Senin - Jumat">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Absen masuk lebih awal (menit)</label>
            <input type="number" name="masuk_lebih_awal_menit" class="form-control"
                   value="{{ old('masuk_lebih_awal_menit', $awalMasuk) }}" min="0" max="180" required>
            <span class="form-hint">Berapa menit sebelum jam masuk scan QR masuk sudah dibuka (sama dengan halaman Kelola QR Code).</span>
          </div>
          <div class="form-group">
            <label class="form-label">Absen pulang lebih awal (menit)</label>
            <input type="number" name="pulang_lebih_awal_menit" class="form-control"
                   value="{{ old('pulang_lebih_awal_menit', $awalPulang) }}" min="0" max="180" required>
            <span class="form-hint">Berapa menit sebelum jam pulang scan QR pulang sudah dibuka.</span>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <i class="fa-solid fa-circle-info text-teal"></i>
        <h3>Ringkasan Window Presensi</h3>
      </div>
      <div class="card-body">
        <div class="presensi-window-summary">
          <div class="presensi-window-block presensi-window-block--masuk">
            <div class="presensi-window-block__label">Presensi Masuk</div>
            <div class="presensi-window-block__times">
              <div class="presensi-window-block__time">
                <div class="presensi-window-block__clock text-teal">{{ ($win['masuk_buka'] ?? now())->format('H:i') }}</div>
                <div class="text-xs text-muted">Buka ({{ $awalMasuk }} min sebelum)</div>
              </div>
              <div class="presensi-window-block__line presensi-window-block__line--masuk"></div>
              <div class="presensi-window-block__time">
                <div class="presensi-window-block__clock text-amber">{{ ($win['masuk_tutup'] ?? now())->format('H:i') }}</div>
                <div class="text-xs text-muted">Tutup scan</div>
              </div>
            </div>
            <div class="text-xs text-muted presensi-window-block__note">
              <i class="fa-solid fa-triangle-exclamation text-amber"></i>
              Toleransi keterlambatan: <strong class="text-amber">{{ $tol }} menit</strong>
              · Tutup scan = jam masuk + toleransi + {{ \App\Models\JadwalKerja::MASUK_TUTUP_EXTRA_MENIT }} menit
            </div>
          </div>

          <div class="presensi-window-block presensi-window-block--pulang">
            <div class="presensi-window-block__label">Presensi Pulang</div>
            <div class="presensi-window-block__times">
              <div class="presensi-window-block__time">
                <div class="presensi-window-block__clock text-green">{{ ($win['pulang_buka'] ?? now())->format('H:i') }}</div>
                <div class="text-xs text-muted">Buka ({{ $awalPulang }} min sebelum)</div>
              </div>
              <div class="presensi-window-block__line presensi-window-block__line--pulang"></div>
              <div class="presensi-window-block__time">
                <div class="presensi-window-block__clock text-teal">{{ ($win['pulang_tutup'] ?? now())->format('H:i') }}</div>
                <div class="text-xs text-muted">Tutup scan</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="card office-location-card" style="margin-top:24px;">
    <div class="card-header">
      <i class="fa-solid fa-location-dot text-teal"></i>
      <h3>Lokasi Kantor (Google Maps)</h3>
    </div>
    <div class="card-body">
      <p class="text-muted text-sm office-location-intro">
        Opsional. Jika diisi, karyawan harus mengizinkan lokasi dan berada dalam radius tertentu dari titik kantor saat presensi.
        Kosongkan semua field lokasi untuk menonaktifkan validasi jarak.
      </p>

      <div class="form-group">
        <label class="form-label" for="maps-coord-paste">Tempel tautan atau koordinat Google Maps</label>
        <div class="maps-paste-row">
          <input type="text" id="maps-coord-paste" class="form-control"
                 placeholder="https://maps.google.com/... atau -6.123456, 106.789012"
                 autocomplete="off" inputmode="text">
          <button type="button" class="btn btn-outline btn-sm" id="btn-office-parse-paste">
            <i class="fa-solid fa-paste"></i> Terapkan
          </button>
        </div>
        <span class="form-hint">Salin dari Google Maps (Share → Salin link) atau ketik lintang, bujur dipisah koma.</span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="kantor-latitude">Lintang (latitude)</label>
          <input type="number" step="any" name="kantor_latitude" id="kantor-latitude" class="form-control"
                 value="{{ old('kantor_latitude', $jadwal?->kantor_latitude) }}"
                 placeholder="-6.XXXXXX" inputmode="decimal">
        </div>
        <div class="form-group">
          <label class="form-label" for="kantor-longitude">Bujur (longitude)</label>
          <input type="number" step="any" name="kantor_longitude" id="kantor-longitude" class="form-control"
                 value="{{ old('kantor_longitude', $jadwal?->kantor_longitude) }}"
                 placeholder="106.XXXXXX" inputmode="decimal">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Radius izin presensi (meter)</label>
        <input type="number" name="radius_meter" class="form-control" min="10" max="50000"
               value="{{ old('radius_meter', $jadwal?->radius_meter) }}"
               placeholder="mis. 150" inputmode="numeric">
      </div>

      <div class="office-map-panel">
        <div id="office-map-placeholder" class="office-map-placeholder" style="{{ $officeHasCoords ? 'display:none;' : '' }}">
          Isi koordinat di atas, tempel tautan Maps, atau ketuk <strong>Gunakan lokasi perangkat ini</strong>.
        </div>
        <div id="office-map-frame-wrap" class="office-map-frame-wrap" style="{{ $officeHasCoords ? '' : 'display:none;' }}">
          <iframe id="office-map-embed" title="Peta lokasi kantor (Google Maps)" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="office-map-actions">
          <button type="button" class="btn btn-outline btn-sm" id="btn-office-geolocate">
            <i class="fa-solid fa-location-crosshairs"></i> Gunakan lokasi perangkat ini
          </button>
          <button type="button" class="btn btn-outline btn-sm" id="btn-office-open-maps">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka di Google Maps
          </button>
          <button type="button" class="btn btn-primary btn-sm" id="btn-office-refresh-map">
            <i class="fa-solid fa-map"></i> Perbarui peta
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="jadwal-form-actions">
    <button type="submit" class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
    </button>
  </div>
</form>

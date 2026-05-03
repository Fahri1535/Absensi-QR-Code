{{-- Form jadwal + ringkasan window (dipakai halaman QR & Jadwal Kerja) --}}
<div class="responsive-grid stagger">

  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-clock text-teal"></i>
      <h3>Pengaturan Jadwal Kerja</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('operator.jadwal.update') }}">
        @csrf @method('PATCH')
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
                   value="{{ old('toleransi_menit', $jadwal?->toleransi_menit ?? 5) }}" min="0" max="60" required>
          </div>
          <div class="form-group">
            <label class="form-label">Hari Kerja</label>
            <input type="text" name="hari_kerja" class="form-control"
                   value="{{ old('hari_kerja', $jadwal?->hari_kerja ?? 'Senin - Jumat') }}"
                   placeholder="contoh: Senin - Jumat">
          </div>
        </div>
        <hr class="divider" style="margin:16px 0;">
        <p class="text-muted text-sm" style="margin-bottom:12px;">
          <strong>Radius kantor (opsional):</strong> jika diisi, karyawan harus mengizinkan lokasi dan berada dalam jarak tertentu dari titik kantor untuk presensi. Kosongkan ketiga field untuk menonaktifkan.
        </p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Lintang kantor</label>
            <input type="number" step="any" name="kantor_latitude" id="kantor-latitude" class="form-control"
                   value="{{ old('kantor_latitude', $jadwal?->kantor_latitude) }}"
                   placeholder="-6.XXXXXX">
          </div>
          <div class="form-group">
            <label class="form-label">Bujur kantor</label>
            <input type="number" step="any" name="kantor_longitude" id="kantor-longitude" class="form-control"
                   value="{{ old('kantor_longitude', $jadwal?->kantor_longitude) }}"
                   placeholder="106.XXXXXX">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Radius izin (meter)</label>
          <input type="number" name="radius_meter" class="form-control" min="10" max="50000"
                 value="{{ old('radius_meter', $jadwal?->radius_meter) }}"
                 placeholder="mis. 150">
        </div>

        @php $officeHasCoords = $jadwal?->kantor_latitude !== null && $jadwal?->kantor_longitude !== null; @endphp
        <div class="office-map-panel" style="margin-top:4px;margin-bottom:16px;padding-top:14px;border-top:1px solid var(--border);">
          <label class="form-label">Google Maps · Lokasi Kantor</label>
          <p class="text-muted text-sm" style="margin-bottom:10px;line-height:1.45;">
            Isi koordinat di atas, pakai lokasi perangkat ini, atau buka tautan Maps untuk mencari titik kantor. Radius presensi mengikuti field radius di atas.
          </p>
          <div id="office-map-placeholder" style="{{ $officeHasCoords ? 'display:none;' : 'min-height:180px;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px;color:var(--text-secondary);font-size:.82rem;background:var(--bg-input);border-radius:var(--radius-sm);border:1px dashed var(--border);margin-bottom:10px;' }}">
            Isi lintang &amp; bujur, lalu ketuk <strong>Perbarui peta</strong> atau <strong>Gunakan lokasi perangkat ini</strong>.
          </div>
          <div id="office-map-frame-wrap" style="{{ $officeHasCoords ? 'position:relative;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border);aspect-ratio:16/10;max-height:260px;background:var(--bg-input);margin-bottom:10px;' : 'display:none;' }}">
            <iframe id="office-map-embed" title="Peta lokasi kantor (Google Maps)" style="width:100%;height:100%;border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <button type="button" class="btn btn-outline btn-sm" id="btn-office-geolocate">
              <i class="fa-solid fa-location-crosshairs"></i> Gunakan lokasi perangkat ini
            </button>
            <button type="button" class="btn btn-outline btn-sm" id="btn-office-open-maps">
              <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka koordinat di Google Maps
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="btn-office-refresh-map">
              <i class="fa-solid fa-map"></i> Perbarui peta dari koordinat
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Jadwal
        </button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <i class="fa-solid fa-circle-info text-teal"></i>
      <h3>Ringkasan Window Presensi</h3>
    </div>
    <div class="card-body">
      @php
        $jmBase = \Carbon\Carbon::parse($jadwal?->jam_masuk ?? '08:00');
        $jpBase = \Carbon\Carbon::parse($jadwal?->jam_pulang ?? '17:00');
        $tol = $jadwal?->toleransi_menit ?? 5;
      @endphp
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div style="background:rgba(0,201,167,.06);border:1px solid rgba(0,201,167,.15);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px;">Presensi Masuk</div>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="text-align:center;">
              <div style="font-family:'DM Sans',sans-serif;font-size:1.3rem;font-weight:800;color:var(--teal);">{{ $jmBase->copy()->subMinutes(15)->format('H:i') }}</div>
              <div class="text-xs text-muted">Buka (15 min sebelum)</div>
            </div>
            <div style="flex:1;height:2px;background:linear-gradient(90deg,var(--teal),var(--amber));border-radius:1px;"></div>
            <div style="text-align:center;">
              <div style="font-family:'DM Sans',sans-serif;font-size:1.3rem;font-weight:800;color:var(--amber);">{{ $jmBase->copy()->addMinutes($tol)->format('H:i') }}</div>
              <div class="text-xs text-muted">Tutup (Sesuai Toleransi)</div>
            </div>
          </div>
          <div class="text-xs text-muted" style="margin-top:8px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--amber);"></i>
            Toleransi keterlambatan: <strong style="color:var(--amber);">{{ $tol }} menit</strong>
          </div>
        </div>

        <div style="background:rgba(0,224,150,.06);border:1px solid rgba(0,224,150,.15);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px;">Presensi Pulang</div>
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="text-align:center;">
              <div style="font-family:'DM Sans',sans-serif;font-size:1.3rem;font-weight:800;color:var(--green);">{{ $jpBase->copy()->subMinutes(30)->format('H:i') }}</div>
              <div class="text-xs text-muted">Buka (30 min sebelum)</div>
            </div>
            <div style="flex:1;height:2px;background:linear-gradient(90deg,var(--green),var(--teal));border-radius:1px;"></div>
            <div style="text-align:center;">
              <div style="font-family:'DM Sans',sans-serif;font-size:1.3rem;font-weight:800;color:var(--teal);">{{ $jpBase->format('H:i') }}</div>
              <div class="text-xs text-muted">Tutup (Jam Pulang)</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>


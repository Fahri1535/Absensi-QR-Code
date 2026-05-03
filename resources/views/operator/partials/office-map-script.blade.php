<script>
(function () {
  function parseCoord(v) {
    if (v == null || v === '') return null;
    const n = typeof v === 'number' ? v : parseFloat(String(v).replace(',', '.'));
    return Number.isFinite(n) ? n : null;
  }

  const latEl = document.getElementById('kantor-latitude');
  const lngEl = document.getElementById('kantor-longitude');
  const iframe = document.getElementById('office-map-embed');
  const ph = document.getElementById('office-map-placeholder');
  const wrap = document.getElementById('office-map-frame-wrap');

  function updateOfficeMapEmbed() {
    if (!iframe || !ph || !wrap) return;
    const lat = parseCoord(latEl && latEl.value);
    const lng = parseCoord(lngEl && lngEl.value);
    if (lat != null && lng != null && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
      iframe.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=18&hl=id&output=embed';
      ph.style.display = 'none';
      wrap.style.cssText =
        'position:relative;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border);aspect-ratio:16/10;max-height:260px;background:var(--bg-input);margin-bottom:10px;';
      wrap.style.display = 'block';
    } else {
      iframe.src = '';
      iframe.removeAttribute('src');
      wrap.style.display = 'none';
      ph.style.cssText =
        'min-height:180px;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px;color:var(--text-secondary);font-size:.82rem;background:var(--bg-input);border-radius:var(--radius-sm);border:1px dashed var(--border);margin-bottom:10px;';
    }
  }

  document.getElementById('btn-office-refresh-map')?.addEventListener('click', updateOfficeMapEmbed);

  document.getElementById('btn-office-geolocate')?.addEventListener('click', function () {
    if (!navigator.geolocation) {
      alert('Perangkat ini tidak mendukung geolokasi.');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        if (latEl) latEl.value = Number(pos.coords.latitude.toFixed(7));
        if (lngEl) lngEl.value = Number(pos.coords.longitude.toFixed(7));
        updateOfficeMapEmbed();
      },
      function () {
        alert('Tidak dapat mengambil lokasi. Izinkan akses lokasi atau isi lintang dan bujur secara manual.');
      },
      { enableHighAccuracy: true, timeout: 12000 }
    );
  });

  document.getElementById('btn-office-open-maps')?.addEventListener('click', function () {
    const lat = parseCoord(latEl && latEl.value);
    const lng = parseCoord(lngEl && lngEl.value);
    if (lat == null || lng == null) {
      alert('Isi lintang dan bujur terlebih dahulu.');
      return;
    }
    window.open('https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=18&hl=id', '_blank');
  });

  latEl?.addEventListener('change', updateOfficeMapEmbed);
  lngEl?.addEventListener('change', updateOfficeMapEmbed);

  updateOfficeMapEmbed();
})();
</script>

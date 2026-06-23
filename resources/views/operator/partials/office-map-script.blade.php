<script>
(function () {
  function parseCoord(v) {
    if (v == null || v === '') return null;
    const n = typeof v === 'number' ? v : parseFloat(String(v).replace(',', '.'));
    return Number.isFinite(n) ? n : null;
  }

  function parseCoordsFromText(text) {
    if (!text || typeof text !== 'string') return null;
    const s = text.trim();
    if (!s) return null;

    // @lat,lng in Google Maps URLs
    let m = s.match(/@(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/);
    if (m) {
      const lat = parseCoord(m[1]);
      const lng = parseCoord(m[2]);
      if (lat != null && lng != null) return { lat, lng };
    }

    // q= or query= param
    m = s.match(/[?&](?:q|query)=(-?\d+\.?\d*)[,%2C\s]+(-?\d+\.?\d*)/i);
    if (m) {
      const lat = parseCoord(m[1]);
      const lng = parseCoord(m[2]);
      if (lat != null && lng != null) return { lat, lng };
    }

    // Plain "lat, lng" or "lat lng"
    m = s.match(/^(-?\d+\.?\d*)\s*[,;\s]\s*(-?\d+\.?\d*)$/);
    if (m) {
      const lat = parseCoord(m[1]);
      const lng = parseCoord(m[2]);
      if (lat != null && lng != null) return { lat, lng };
    }

    // !3d lat !4d lng embed format
    m = s.match(/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/);
    if (m) {
      const lat = parseCoord(m[1]);
      const lng = parseCoord(m[2]);
      if (lat != null && lng != null) return { lat, lng };
    }

    return null;
  }

  const latEl = document.getElementById('kantor-latitude');
  const lngEl = document.getElementById('kantor-longitude');
  const pasteEl = document.getElementById('maps-coord-paste');
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
      wrap.style.display = 'block';
    } else {
      iframe.removeAttribute('src');
      wrap.style.display = 'none';
      ph.style.display = 'flex';
    }
  }

  function applyParsedCoords(coords) {
    if (!coords) return false;
    if (latEl) latEl.value = Number(coords.lat.toFixed(7));
    if (lngEl) lngEl.value = Number(coords.lng.toFixed(7));
    updateOfficeMapEmbed();
    return true;
  }

  document.getElementById('btn-office-parse-paste')?.addEventListener('click', function () {
    const coords = parseCoordsFromText(pasteEl && pasteEl.value);
    if (!applyParsedCoords(coords)) {
      alert('Koordinat tidak dikenali. Tempel tautan Google Maps atau format lintang, bujur (contoh: -6.123, 106.456).');
    }
  });

  pasteEl?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      document.getElementById('btn-office-parse-paste')?.click();
    }
  });

  document.getElementById('btn-office-refresh-map')?.addEventListener('click', updateOfficeMapEmbed);

  document.getElementById('btn-office-geolocate')?.addEventListener('click', function () {
    if (!navigator.geolocation) {
      alert('Perangkat ini tidak mendukung geolokasi.');
      return;
    }
    const btn = this;
    btn.disabled = true;
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        applyParsedCoords({ lat: pos.coords.latitude, lng: pos.coords.longitude });
        btn.disabled = false;
      },
      function () {
        alert('Tidak dapat mengambil lokasi. Izinkan akses lokasi atau isi koordinat secara manual.');
        btn.disabled = false;
      },
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
  });

  document.getElementById('btn-office-open-maps')?.addEventListener('click', function () {
    const lat = parseCoord(latEl && latEl.value);
    const lng = parseCoord(lngEl && lngEl.value);
    if (lat == null || lng == null) {
      alert('Isi lintang dan bujur terlebih dahulu.');
      return;
    }
    window.open('https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=18&hl=id', '_blank', 'noopener');
  });

  latEl?.addEventListener('input', updateOfficeMapEmbed);
  lngEl?.addEventListener('input', updateOfficeMapEmbed);
  latEl?.addEventListener('change', updateOfficeMapEmbed);
  lngEl?.addEventListener('change', updateOfficeMapEmbed);

  updateOfficeMapEmbed();
})();
</script>

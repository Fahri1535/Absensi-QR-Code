<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#0F172A" id="meta-theme-color">
  <title>@yield('title', 'Kontak Admin') — Presensi QR</title>
  <link rel="preload" href="{{ asset('css/app.css') }}" as="style">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <script>
    (function(){
      var t = localStorage.getItem('theme') || 'dark';
      document.documentElement.style.background = t === 'light' ? '#F8FAFC' : '#0F172A';
      document.documentElement.style.colorScheme = t === 'light' ? 'light' : 'dark';
      if (t === 'light') document.documentElement.classList.add('lm');
    })();
  </script>
  <style>
    html { background: #0F172A; }
    html.lm { background: #F8FAFC; }
    body { margin: 0; min-height: 100vh; background: var(--bg-primary); padding: 24px; }
    .theme-toggle {
      position: fixed;
      top: 18px;
      right: 18px;
      z-index: 100;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 999px;
      color: var(--text-primary);
      padding: 8px 14px;
      font-size: .82rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: var(--transition);
      box-shadow: var(--shadow);
    }
    .theme-toggle:hover { border-color: var(--blue-light); transform: translateY(-1px); }
  </style>
</head>
<body>
  <button type="button" class="theme-toggle" onclick="toggleTheme()" id="themeToggleBtn">
    <i class="fa-solid fa-moon" id="themeIcon"></i>
    <span id="themeText">Dark Mode</span>
  </button>

  @yield('content')

  <script>
    (function () {
      try {
        if (localStorage.getItem('theme') === 'light') document.body.classList.add('light-mode');
      } catch (e) {}
    })();

    function applyTheme(theme) {
      var icon = document.getElementById('themeIcon');
      var text = document.getElementById('themeText');
      var meta = document.getElementById('meta-theme-color');
      document.documentElement.classList.remove('lm');

      if (theme === 'light') {
        document.documentElement.classList.add('lm');
        document.documentElement.style.background = '#F8FAFC';
        document.documentElement.style.colorScheme = 'light';
        document.body.classList.add('light-mode');
        if (meta) meta.setAttribute('content', '#F8FAFC');
        if (icon) icon.className = 'fa-solid fa-sun';
        if (text) text.textContent = 'Light Mode';
      } else {
        document.documentElement.style.background = '#0F172A';
        document.documentElement.style.colorScheme = 'dark';
        document.body.classList.remove('light-mode');
        if (meta) meta.setAttribute('content', '#0F172A');
        if (icon) icon.className = 'fa-solid fa-moon';
        if (text) text.textContent = 'Dark Mode';
      }
    }

    function toggleTheme() {
      var next = (localStorage.getItem('theme') || 'dark') === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', next);
      applyTheme(next);
    }

    applyTheme(localStorage.getItem('theme') || 'dark');
  </script>
</body>
</html>

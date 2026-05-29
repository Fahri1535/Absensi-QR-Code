/**
 * app.js — Presensi QR System
 * PT. Nugraha Tirta Sejati
 */

document.addEventListener('DOMContentLoaded', () => {
  // ── Auto dismiss flash alerts ─────────────────────────────────
  document.querySelectorAll('.alert').forEach(el => {
    if (!el.closest('.modal') && !el.closest('form')) {
      setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 500);
      }, 5000);
    }
  });

  // ── Confirm delete helper ─────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Yakin?')) e.preventDefault();
    });
  });

  // ── Loading state for forms ───────────────────────────────────
  document.querySelectorAll('form[data-loading]').forEach(form => {
    form.addEventListener('submit', () => {
      const btn = form.querySelector('[type=submit]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
      }
    });
  });

  // ── Table row click → show detail ────────────────────────────
  document.querySelectorAll('tr[data-href]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => {
      window.location.href = row.dataset.href;
    });
  });

  /* ── Live Clock ───────────────────────────────────────────── */
  function updateClock() {
    const now = new Date();
    const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' };
    const el = document.getElementById('topbar-clock');
    if (el) el.textContent = now.toLocaleDateString('id-ID', opts);
  }
  updateClock();
  setInterval(updateClock, 30000);

  /* ── Sidebar Toggle ───────────────────────────────────────── */
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const toggleBtn = document.getElementById('sidebar-toggle');
  const isMobile = () => window.innerWidth <= 768;

  // Optimasi Scroll Mobile: Matikan event listener yang tidak perlu saat scroll
  let isScrolling;
  window.addEventListener('scroll', () => {
    document.body.classList.add('is-scrolling');
    clearTimeout(isScrolling);
    isScrolling = setTimeout(() => {
      document.body.classList.remove('is-scrolling');
    }, 150);
  }, { passive: true });

  let sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

  function applySidebarState() {
    document.documentElement.classList.remove('sb');
    if (isMobile()) {
      document.body.classList.remove('sidebar-collapsed');
      return;
    }
    if (sidebarCollapsed) {
      document.body.classList.add('sidebar-collapsed');
      toggleBtn?.classList.add('sidebar-toggle-active');
    } else {
      document.body.classList.remove('sidebar-collapsed');
      toggleBtn?.classList.remove('sidebar-toggle-active');
    }
  }

  toggleBtn?.addEventListener('click', () => {
    if (isMobile()) {
      const isOpen = sidebar.classList.toggle('open');
      if (overlay) overlay.style.display = isOpen ? 'block' : 'none';
    } else {
      sidebarCollapsed = !sidebarCollapsed;
      localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
      applySidebarState();
    }
  });

  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
  });

  window.addEventListener('resize', () => {
    if (!isMobile()) {
      sidebar.classList.remove('open');
      if (overlay) overlay.style.display = 'none';
      applySidebarState();
    }
  });

  applySidebarState();

  /* ── Theme Toggle ─────────────────────────────────────────── */
  const themeBtn = document.getElementById('theme-toggle');
  const iconSun = themeBtn?.querySelector('.icon-sun');
  const iconMoon = themeBtn?.querySelector('.icon-moon');
  let currentTheme = localStorage.getItem('theme') || 'dark';

  function applyTheme(theme) {
    document.documentElement.classList.remove('lm');
    const tc = document.getElementById('meta-theme-color');
    if (theme === 'light') {
      document.documentElement.classList.add('lm');
      document.body.classList.add('light-mode');
      if (tc) tc.setAttribute('content', '#F8FAFC');
      if (iconSun) iconSun.style.display = 'block';
      if (iconMoon) iconMoon.style.display = 'none';
    } else {
      document.body.classList.remove('light-mode');
      if (tc) tc.setAttribute('content', '#0F172A');
      if (iconSun) iconSun.style.display = 'none';
      if (iconMoon) iconMoon.style.display = 'block';
    }
  }

  themeBtn?.addEventListener('click', () => {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', currentTheme);
    applyTheme(currentTheme);
  });

  applyTheme(currentTheme);
});

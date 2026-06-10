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

  /* ── Sidebar Height Fix (visualViewport API) ────────────── */
  /* On mobile browsers, the URL bar shows/hides during scroll,
     changing the visible viewport height. CSS-only solutions
     (100vh, 100svh, 100dvh, top:0+bottom:0) all have issues:
     - gap at bottom when URL bar hides
     - profile icon "sinks" when URL bar shows
     Using visualViewport API gives the EXACT visible height. */
  const sidebarEl = document.getElementById('sidebar');
  function updateSidebarHeight() {
    if (!sidebarEl) return;
    const h = window.visualViewport ? window.visualViewport.height : window.innerHeight;
    sidebarEl.style.height = h + 'px';
  }
  updateSidebarHeight();
  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', updateSidebarHeight);
  }
  window.addEventListener('resize', updateSidebarHeight);
  window.addEventListener('orientationchange', function() {
    setTimeout(updateSidebarHeight, 150);
  });

  /* ── Sidebar Toggle ───────────────────────────────────────── */
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const toggleBtn = document.getElementById('sidebar-toggle');
  const isMobile = () => window.innerWidth <= 768;

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

  // Simple click handler (no preventDefault to avoid breaking links)
  if (toggleBtn) {
    toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (isMobile()) {
        const isOpen = sidebar.classList.toggle('open');
        if (overlay) overlay.style.display = isOpen ? 'block' : 'none';
      } else {
        sidebarCollapsed = !sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
        applySidebarState();
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.style.display = 'none';
    });
  }

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

  /* ── Collapsible Nav Sections ────────────────────────────────── */
  const STORAGE_KEY = 'navSectionsCollapsed';

  // Load saved collapsed state from localStorage
  function getCollapsedSections() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    } catch (e) {
      return {};
    }
  }

  function saveCollapsedSections(state) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {}
  }

  const collapsedState = getCollapsedSections();

  document.querySelectorAll('.nav-section[data-section]').forEach(section => {
    const key = section.dataset.section;
    const toggle = section.querySelector('.nav-section-toggle');
    const hasActive = section.querySelector('.nav-item.active');

    // Jika section punya item aktif, selalu buka (dan hapus dari collapsed state)
    if (hasActive) {
      section.classList.remove('collapsed');
      delete collapsedState[key];
    } else if (collapsedState[key]) {
      // Restore collapsed state dari localStorage
      section.classList.add('collapsed');
    }

    // Toggle click handler
    if (toggle) {
      toggle.addEventListener('click', () => {
        const isCollapsed = section.classList.toggle('collapsed');
        const state = getCollapsedSections();
        if (isCollapsed) {
          state[key] = true;
        } else {
          delete state[key];
        }
        saveCollapsedSections(state);
      });
    }
  });

  // Simpan state yang sudah di-update (untuk section aktif yang dibuka)
  saveCollapsedSections(collapsedState);
});

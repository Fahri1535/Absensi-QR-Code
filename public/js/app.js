/**
 * app.js — Presensi QR System
 * PT. Nugraha Tirta Sejati
 */

// ── Auto dismiss flash alerts ─────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert').forEach(el => {
    if (!el.closest('.modal') && !el.closest('form')) {
      setTimeout(() => {
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-8px)';
        setTimeout(() => el.remove(), 500);
      }, 5000);
    }
  });
});

// ── CSRF helper for fetch ─────────────────────────────────────
function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function apiFetch(url, options = {}) {
  const defaults = {
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
      'Accept': 'application/json',
    },
  };
  const res  = await fetch(url, { ...defaults, ...options });
  const data = await res.json();
  return { ok: res.ok, status: res.status, data };
}

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
      btn.disabled   = true;
      const orig     = btn.innerHTML;
      btn.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
      btn.dataset.orig = orig;
    }
  });
});

// ── Notification mark-read on click ──────────────────────────
document.querySelectorAll('.notif-item[data-id]').forEach(item => {
  item.addEventListener('click', async () => {
    const id = item.dataset.id;
    await apiFetch(`/notifikasi/${id}/baca`, { method: 'PATCH' });
    item.querySelector('.unread-dot')?.remove();
  });
});

// ── Date range validator ──────────────────────────────────────
const tglMulai   = document.querySelector('[name="tanggal_mulai"]');
const tglSelesai = document.querySelector('[name="tanggal_selesai"]');

if (tglMulai && tglSelesai) {
  tglMulai.addEventListener('change', () => {
    if (tglSelesai.value < tglMulai.value) {
      tglSelesai.value = tglMulai.value;
    }
    tglSelesai.min = tglMulai.value;
  });
}

// ── Ripple effect on buttons ──────────────────────────────────
document.querySelectorAll('.btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    const r = document.createElement('span');
    const d = Math.max(this.offsetWidth, this.offsetHeight);
    const x = e.clientX - this.getBoundingClientRect().left - d / 2;
    const y = e.clientY - this.getBoundingClientRect().top  - d / 2;

    Object.assign(r.style, {
      position: 'absolute', width: d + 'px', height: d + 'px',
      left: x + 'px', top: y + 'px',
      background: 'rgba(255,255,255,.15)', borderRadius: '50%',
      transform: 'scale(0)', animation: 'ripple .5s linear',
      pointerEvents: 'none',
    });

    this.style.position = 'relative';
    this.style.overflow = 'hidden';
    this.appendChild(r);
    setTimeout(() => r.remove(), 600);
  });
});

// Add ripple keyframe
const style = document.createElement('style');
style.textContent = `
  @keyframes ripple {
    to { transform: scale(4); opacity: 0; }
  }
`;
document.head.appendChild(style);

// ── Table row click → show detail ────────────────────────────
document.querySelectorAll('tr[data-href]').forEach(row => {
  row.style.cursor = 'pointer';
  row.addEventListener('click', () => {
    window.location.href = row.dataset.href;
  });
});

// ── Confirm before page unload if form dirty ─────────────────
(function () {
  const forms = document.querySelectorAll('form[data-dirty-check]');
  forms.forEach(form => {
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', e => {
      if (dirty) {
        e.preventDefault();
        e.returnValue = 'Perubahan belum disimpan. Yakin ingin meninggalkan halaman?';
      }
    });
  });
})();

// ── Simple tooltip ────────────────────────────────────────────
document.querySelectorAll('[data-tooltip]').forEach(el => {
  const tip = document.createElement('div');
  Object.assign(tip.style, {
    position: 'fixed', background: 'var(--navy-mid)', color: 'var(--white)',
    padding: '5px 10px', borderRadius: '6px', fontSize: '.75rem',
    border: '1px solid var(--border)', pointerEvents: 'none',
    zIndex: '9999', opacity: '0', transition: 'opacity .15s ease',
    whiteSpace: 'nowrap',
  });
  tip.textContent = el.dataset.tooltip;
  document.body.appendChild(tip);

  el.addEventListener('mouseenter', e => {
    const rect = el.getBoundingClientRect();
    tip.style.left    = rect.left + 'px';
    tip.style.top     = (rect.top - 32) + 'px';
    tip.style.opacity = '1';
  });
  el.addEventListener('mouseleave', () => { tip.style.opacity = '0'; });
});

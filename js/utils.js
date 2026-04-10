// ============================================
// Bantayan Ferry — Shared JS Utilities
// ============================================

const API_BASE = 'https://ferryapp.infinityfreeapp.com/bantayan/backend/api';

// ── Session Helpers ──────────────────────────
const Session = {
  set(key, val) { localStorage.setItem(key, JSON.stringify(val)); },
  get(key) { try { return JSON.parse(localStorage.getItem(key)); } catch { return null; } },
  clear() { localStorage.clear(); },
  getUser() { return this.get('bfb_user'); },
  getAdmin() { return this.get('bfb_admin'); },
  setUser(u) { this.set('bfb_user', u); },
  setAdmin(a) { this.set('bfb_admin', a); },
  isLoggedIn() { return !!this.getUser(); },
  isAdmin() { return !!this.getAdmin(); },
  logout() { localStorage.removeItem('bfb_user'); localStorage.removeItem('bfb_admin'); }
};

// ── API Helper ───────────────────────────────
const API = {
  async get(endpoint, params = {}) {
    const qs = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${endpoint}${qs ? '?' + qs : ''}`;
    const res = await fetch(url, { headers: { 'Content-Type': 'application/json' } });
    return res.json();
  },
  async post(endpoint, params = {}, body = {}) {
    const qs = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${endpoint}${qs ? '?' + qs : ''}`;
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    return res.json();
  },
  async delete(endpoint, params = {}) {
    const qs = new URLSearchParams(params).toString();
    const url = `${API_BASE}/${endpoint}${qs ? '?' + qs : ''}`;
    const res = await fetch(url, { method: 'DELETE' });
    return res.json();
  }
};

// ── Toast ────────────────────────────────────
function toast(message, type = 'info', duration = 3000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  t.textContent = `${icons[type] || ''} ${message}`;
  container.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(60px)'; setTimeout(() => t.remove(), 400); }, duration);
}

// ── Format Helpers ────────────────────────────
function formatCurrency(n) { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 }); }
function formatDate(d) { return new Date(d).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }); }
function formatTime(t) {
  const [h, m] = t.split(':');
  const hr = parseInt(h);
  const ampm = hr >= 12 ? 'PM' : 'AM';
  const h12 = hr % 12 || 12;
  return `${h12}:${m} ${ampm}`;
}
function badgeHtml(status) {
  return `<span class="badge badge-${status}">${status}</span>`;
}
function initials(name) {
  return (name || 'U').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

// ── Generate QR ──────────────────────────────
function generateQR(elId, text, size = 150) {
  const el = document.getElementById(elId);
  if (!el) return;
  el.innerHTML = '';
  // Use QRCode.js if available, else fallback to API
  if (typeof QRCode !== 'undefined') {
    new QRCode(el, { text, width: size, height: size, colorDark: '#00d4c8', colorLight: '#0d1f3c', correctLevel: QRCode.CorrectLevel.M });
  } else {
    const img = document.createElement('img');
    img.src = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}&bgcolor=0d1f3c&color=00d4c8`;
    img.width = size; img.height = size;
    el.appendChild(img);
  }
}

// ── PDF Receipt ──────────────────────────────
function downloadReceipt(booking) {
  const el = document.createElement('div');
  el.style.cssText = 'font-family:sans-serif;color:#0a1628;padding:40px;max-width:600px;';
  el.innerHTML = `
    <div style="text-align:center;margin-bottom:30px;">
      <h1 style="color:#1a6fa8;font-size:24px;margin-bottom:4px;">⛴ Bantayan Ferry</h1>
      <p style="color:#666;font-size:13px;">Booking Receipt</p>
      <hr style="border:1px solid #ddd;margin:16px 0;">
    </div>
    <div style="background:#f0f7ff;border-radius:10px;padding:20px;margin-bottom:24px;">
      <div style="font-size:11px;color:#666;text-transform:uppercase;letter-spacing:1px;">Reference Number</div>
      <div style="font-size:26px;font-weight:800;color:#1a6fa8;letter-spacing:2px;">${booking.reference_no}</div>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      ${[
        ['Passenger Name', booking.user_name || booking.name],
        ['Shipping Line', booking.shipping_line],
        ['Route', booking.route],
        ['Departure Time', formatTime(booking.departure_time)],
        ['Passenger Type', booking.passenger_type],
        ['Class', booking.class_type !== 'N/A' ? booking.class_type : '—'],
        ['Cargo', booking.cargo_type || '—'],
        ['Fare', formatCurrency(booking.fare)],
        ['Cargo Fee', booking.cargo_price > 0 ? formatCurrency(booking.cargo_price) : '—'],
        ['Total Price', formatCurrency(booking.total_price)],
        ['Status', booking.status?.toUpperCase()],
        ['Booking Date', formatDate(booking.booking_date)],
      ].map(([k,v]) => `
        <tr style="border-bottom:1px solid #eee;">
          <td style="padding:10px 8px;color:#666;width:45%;">${k}</td>
          <td style="padding:10px 8px;font-weight:600;">${v || '—'}</td>
        </tr>`).join('')}
    </table>
    <div style="margin-top:30px;padding:16px;background:#e8f4fd;border-radius:8px;font-size:12px;color:#666;text-align:center;">
      Thank you for choosing Bantayan Ferry! Please present this receipt and QR code at boarding.<br>
      Keep your reference number: <strong>${booking.reference_no}</strong>
    </div>
  `;

  if (typeof html2pdf !== 'undefined') {
    html2pdf().from(el).set({
      margin: 0, filename: `receipt-${booking.reference_no}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 }, jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    }).save();
  } else {
    // Fallback: open print dialog
    const w = window.open('', '_blank');
    w.document.write(`<html><body>${el.outerHTML}</body></html>`);
    w.document.close(); w.print();
  }
}

// ── Confirm Dialog ────────────────────────────
function confirm(message, onYes) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.innerHTML = `
    <div class="modal" style="max-width:360px;text-align:center;">
      <div style="font-size:2.5rem;margin-bottom:16px;">⚠️</div>
      <p style="font-size:1rem;font-weight:600;margin-bottom:8px;">Are you sure?</p>
      <p style="font-size:0.88rem;color:var(--text-dim);margin-bottom:24px;">${message}</p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button class="btn btn-ghost" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
        <button class="btn btn-danger" id="confirm-yes">Yes, proceed</button>
      </div>
    </div>`;
  document.body.appendChild(overlay);
  overlay.querySelector('#confirm-yes').onclick = () => { overlay.remove(); onYes(); };
}

// ── Sidebar Toggle (desktop only — mobile uses bottom tabbar) ───────────────────
function initSidebar() {
  // Desktop sidebar: no hamburger needed. Mobile uses bottom tabbar via CSS.
  // Kept as no-op so admin.html references don't break.
}

// ── Nav active state ──────────────────────────
function setActiveNav(id) {
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  const el = document.getElementById(id);
  if (el) el.classList.add('active');
}

// ── Animate on load ───────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.stagger-1,.stagger-2,.stagger-3,.stagger-4').forEach(el => {
    el.classList.add('slide-up');
    el.style.animationFillMode = 'forwards';
  });
});

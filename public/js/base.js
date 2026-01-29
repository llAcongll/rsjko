// =========================
// GLOBAL TOAST NOTIFICATION
// =========================
window.toast = function (message = '', type = 'info') {
  if (!message) message = 'Notifikasi';

  const el = document.getElementById('toast');
  if (!el) return;

  el.className = `toast ${type}`;
  el.textContent = String(message);

  el.classList.add('show');

  setTimeout(() => {
    el.classList.remove('show');
  }, 3000);
};

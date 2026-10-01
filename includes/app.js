// Peningkatan UI saja; proses utama selalu ditangani PHP.
const toggle = document.getElementById('toggle-password');
if (toggle) toggle.addEventListener('click', () => {
  const field = document.getElementById('password');
  const reveal = field.type === 'password';
  field.type = reveal ? 'text' : 'password';
  toggle.textContent = reveal ? 'Tutup' : 'Lihat';
  toggle.setAttribute('aria-pressed', String(reveal));
});
document.querySelectorAll('[data-amount]').forEach(button => button.addEventListener('click', () => {
  const field = document.getElementById('amount');
  field.value = button.dataset.amount;
  field.focus();
}));
const expiry = document.querySelector('[data-expires]');
if (expiry) {
  const update = () => {
    const left = Math.max(0, Math.floor(Number(expiry.dataset.expires) - Date.now() / 1000));
    expiry.textContent = left ? ` · ${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}` : ' · Waktu habis';
    if (!left) {
      document.querySelectorAll('button[name="action"]').forEach(button => { button.disabled = true; });
      expiry.textContent += ' — muat ulang untuk memperbarui status.';
      clearInterval(timer);
    }
  };
  const timer = setInterval(update, 1000);
  update();
}

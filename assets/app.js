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

// Progressive enhancement: PHP also accepts item lists without JavaScript.
const itemEditor = document.querySelector('[data-item-editor]');
if (itemEditor) {
  const rows = [...itemEditor.querySelectorAll('[data-item-row]')];
  const addButton = itemEditor.querySelector('[data-add-item]');
  const count = itemEditor.querySelector('[data-item-count]');
  const status = itemEditor.querySelector('[data-item-status]');
  const target = document.getElementById('target');
  let manualTarget = target.value;
  const setVisible = (row, visible) => {
    row.hidden = !visible;
    row.querySelectorAll('input').forEach(input => { input.disabled = !visible; });
  };
  let lastFilled = 0;
  rows.forEach((row, index) => {
    if ([...row.querySelectorAll('input')].some(input => input.value !== '')) lastFilled = index;
    row.querySelector('[data-remove-item]').hidden = false;
  });
  rows.forEach((row, index) => setVisible(row, index <= lastFilled));
  addButton.hidden = false;
  count.hidden = false;
  const update = () => {
    let total = 0;
    let filled = 0;
    let hasItems = false;
    rows.filter(row => !row.hidden).forEach(row => {
      const name = row.querySelector('[data-item-name]').value.trim();
      const value = row.querySelector('[data-item-amount]').value;
      if (name || value) hasItems = true;
      const amount = Number(value);
      if (name && Number.isInteger(amount) && amount > 0) { total += amount; filled++; }
    });
    target.readOnly = hasItems;
    target.required = !hasItems;
    target.value = hasItems ? (total || '') : manualTarget;
    const visibleCount = rows.filter(row => !row.hidden).length;
    addButton.disabled = visibleCount === rows.length;
    count.textContent = `${visibleCount} / ${rows.length} kolom barang`;
    status.textContent = hasItems
      ? (total > 1000000000 ? 'Total melebihi Rp1.000.000.000. Sesuaikan harga barang.' : `${filled} barang lengkap · total Rp${total.toLocaleString('id-ID')}. Lengkapi nama dan harga setiap barang yang diisi.`)
      : '';
  };
  addButton.addEventListener('click', () => {
    const row = rows.find(row => row.hidden);
    if (!row) return;
    setVisible(row, true);
    update();
    row.querySelector('[data-item-name]').focus();
  });
  rows.forEach(row => {
    row.querySelectorAll('input').forEach(input => input.addEventListener('input', update));
    row.querySelector('[data-remove-item]').addEventListener('click', () => {
      row.querySelectorAll('input').forEach(input => { input.value = ''; });
      if (rows.filter(item => !item.hidden).length > 1) setVisible(row, false);
      update();
      const nextRow = rows.find(item => !item.hidden);
      nextRow.querySelector('[data-item-name]').focus();
    });
  });
  target.addEventListener('input', () => { if (!target.readOnly) manualTarget = target.value; });
  update();
}

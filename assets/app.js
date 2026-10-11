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


// Login story: autoplay, keyboard controls and horizontal swipes.
const storyCarousel = document.querySelector('[data-story-carousel]');
if (storyCarousel) {
  const slides = [...storyCarousel.querySelectorAll('[data-story-slide]')];
  const selectors = [...storyCarousel.querySelectorAll('[data-story-select]')];
  const pauseButton = storyCarousel.querySelector('[data-story-pause]');
  const counter = storyCarousel.querySelector('[data-story-counter]');
  const status = storyCarousel.querySelector('[data-story-status]');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let current = 0;
  let playing = !reducedMotion.matches;
  let hovered = false;
  let focused = false;
  let timer;
  let gesture = null;
  slides.forEach(slide => { slide.hidden = false; });
  storyCarousel.querySelector('[data-story-controls]').hidden = false;
  const schedule = () => {
    window.clearTimeout(timer);
    if (playing && !hovered && !focused && !document.hidden && !gesture) {
      timer = window.setTimeout(() => show(current + 1), 6000);
    }
  };
  const show = (index, announce = false) => {
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      const active = i === current;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', String(!active));
      slide.inert = !active;
      selectors[i].setAttribute('aria-pressed', String(active));
    });
    counter.textContent = `${String(current + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
    if (announce) status.textContent = `Slide ${current + 1} dari ${slides.length}: ${slides[current].querySelector('.story-label').textContent}.`;
    schedule();
  };
  const updatePlayback = () => {
    pauseButton.textContent = playing ? 'Jeda' : 'Putar';
    pauseButton.setAttribute('aria-label', playing ? 'Jeda pergantian slide otomatis' : 'Putar slide otomatis');
    schedule();
  };
  selectors.forEach((button, i) => button.addEventListener('click', () => show(i, true)));
  storyCarousel.querySelector('[data-story-prev]').addEventListener('click', () => show(current - 1, true));
  storyCarousel.querySelector('[data-story-next]').addEventListener('click', () => show(current + 1, true));
  pauseButton.addEventListener('click', () => { playing = !playing; updatePlayback(); });
  storyCarousel.addEventListener('keydown', event => {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    show(current + (event.key === 'ArrowRight' ? 1 : -1), true);
  });
  storyCarousel.addEventListener('pointerenter', event => {
    if (event.pointerType !== 'mouse') return;
    hovered = true; schedule();
  });
  storyCarousel.addEventListener('pointerleave', event => {
    if (event.pointerType !== 'mouse') return;
    hovered = false; schedule();
  });
  storyCarousel.addEventListener('focusin', () => { focused = true; schedule(); });
  storyCarousel.addEventListener('focusout', event => {
    focused = storyCarousel.contains(event.relatedTarget); schedule();
  });
  const surface = storyCarousel.querySelector('[data-story-slides]');
  surface.addEventListener('pointerdown', event => {
    if (!event.isPrimary || event.button !== 0) return;
    gesture = {id: event.pointerId, x: event.clientX, y: event.clientY};
    surface.setPointerCapture(event.pointerId);
    schedule();
  });
  surface.addEventListener('pointerup', event => {
    if (!gesture || event.pointerId !== gesture.id) return;
    const dx = event.clientX - gesture.x;
    const dy = event.clientY - gesture.y;
    gesture = null;
    if (Math.abs(dx) >= 45 && Math.abs(dx) > Math.abs(dy) * 1.5) show(current + (dx < 0 ? 1 : -1), true);
    else schedule();
  });
  const cancelGesture = () => { gesture = null; schedule(); };
  surface.addEventListener('pointercancel', cancelGesture);
  surface.addEventListener('lostpointercapture', cancelGesture);
  document.addEventListener('visibilitychange', schedule);
  const motionChanged = () => { playing = !reducedMotion.matches; updatePlayback(); };
  if (reducedMotion.addEventListener) reducedMotion.addEventListener('change', motionChanged);
  else reducedMotion.addListener(motionChanged);
  show(0);
  updatePlayback();
}

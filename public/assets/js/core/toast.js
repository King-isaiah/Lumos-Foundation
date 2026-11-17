/**
 * Lightweight toast. No dependency.
 * Auto-removes after a duration, animates in/out with CSS + transform.
 */
let container;

function ensureContainer() {
  if (container) return container;
  container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

export const toast = {
  success(msg) { show(msg, 'success'); },
  error(msg)   { show(msg, 'error'); },
  info(msg)    { show(msg, 'info'); },
  warning(msg) { show(msg, 'warning'); },
};

function show(message, type, duration = 4000) {
  const c = ensureContainer();
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.textContent = message;
  c.appendChild(el);

  requestAnimationFrame(() => el.classList.add('toast-in'));

  setTimeout(() => {
    el.classList.remove('toast-in');
    el.addEventListener('transitionend', () => el.remove(), { once: true });
  }, duration);
}
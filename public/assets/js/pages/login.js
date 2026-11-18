import { api, ApiError } from '../core/api.js';
import { toast } from '../core/toast.js';
import { validators, validateForm } from '../core/validators.js';

const form = document.getElementById('loginForm');
const submitBtn = form.querySelector('[data-submit]');
const btnLabel = submitBtn.querySelector('.btn-label');
const btnSpinner = submitBtn.querySelector('.btn-spinner');

// GSAP entrance (loaded from CDN)
const gsap = window.gsap;

if (gsap) {
  gsap.set('.login-hero h1, .hero-sub, .brand', { opacity: 0, y: 24 });
  gsap.timeline()
    .to('.brand',     { opacity: 1, y: 0, duration: .6, ease: 'power3.out' })
    .to('.login-hero h1', { opacity: 1, y: 0, duration: .8, ease: 'power3.out' }, '-=0.3')
    .to('.hero-sub',  { opacity: 1, y: 0, duration: .6, ease: 'power3.out' }, '-=0.4');

  gsap.set('.login-card form label, .btn-primary, .signup-line, .login-card header', { opacity: 0, y: 16 });
  gsap.to('.login-card header', { opacity: 1, y: 0, duration: .6, delay: .2, ease: 'power3.out' });
  gsap.to('.login-card form label, .btn-primary, .signup-line', {
    opacity: 1, y: 0, duration: .5, stagger: 0.08, delay: .35, ease: 'power2.out',
  });
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const check = validateForm(form, {
    email:    [v => v ? true : 'Email is required.', v => validators.email(v) || 'Enter a valid email.'],
    password: [v => v ? true : 'Password is required.'],
  });
  if (!check.ok) { toast.error(check.message); form.elements[check.field].focus(); return; }

  setLoading(true);
  try {
    const data = await api('auth', 'special', {
      method: 'POST',
      body: {
        task: 'login',
        email: form.elements.email.value.trim(),
        password: form.elements.password.value,
      },
    });

    toast.success(`Welcome back, ${data.user.first_name}!`);

    // Check if there is a saved redirect (e.g., if they were trying to access the exam room)
    const redirect = sessionStorage.getItem('redirect_after_login');
    if (redirect) {
        sessionStorage.removeItem('redirect_after_login');
        setTimeout(() => location.href = redirect, 1500);
        return;
    }

    // Role-based Redirect Logic
    if (data.user.role === 'admin' || data.user.role === 'super_admin') {
        setTimeout(() => location.href = './admin/dashboard.php', 1500);
    } else {
        setTimeout(() => location.href = './students/dashboard.php', 1500);
    }

  } catch (err) {
    toast.error(err instanceof ApiError ? err.message : 'Login failed. Please try again.');
    setLoading(false);
  }
});

function setLoading(on) {
  submitBtn.disabled = on;
  btnLabel.hidden = on;
  btnSpinner.hidden = !on;
}
import { api, ApiError } from '../core/api.js';
import { toast } from '../core/toast.js';
import { validators, validateForm } from '../core/validators.js';

const form = document.getElementById('loginForm');
const submitBtn = form.querySelector('[data-submit]');
const btnLabel = submitBtn.querySelector('.btn-label');
const btnSpinner = submitBtn.querySelector('.btn-spinner');

// --- SPECTACULAR GSAP ANIMATIONS ---
document.addEventListener('DOMContentLoaded', () => {
    const gsap = window.gsap;

    if (gsap) {
        // 1. Animate the Floating Orbs continuously
        gsap.to(".hero-orbs span:nth-child(1)", { x: 50, y: 30, duration: 4, repeat: -1, yoyo: true, ease: "sine.inOut" });
        gsap.to(".hero-orbs span:nth-child(2)", { x: -40, y: -20, duration: 5, repeat: -1, yoyo: true, ease: "sine.inOut" });
        gsap.to(".hero-orbs span:nth-child(3)", { x: 20, y: -50, duration: 6, repeat: -1, yoyo: true, ease: "sine.inOut" });

        // 2. The Entry Timeline
        const tl = gsap.timeline();

        // Brand and Hero Text reveal
        tl.from(".brand", { y: -20, opacity: 0, duration: 0.8, ease: "power3.out" })
          .from(".login-hero h1", { y: 40, opacity: 0, duration: 1, ease: "power4.out" }, "-=0.5")
          .from(".hero-sub", { y: 20, opacity: 0, duration: 0.8, ease: "power3.out" }, "-=0.6");

        // Form Card Slide in
        tl.from(".login-card", { x: 50, opacity: 0, duration: 0.8, ease: "power3.out" }, "-=1")
        
        // Form Elements Stagger
          .from(".login-card header", { y: 20, opacity: 0, duration: 0.6 }, "-=0.4")
          .from(".login-card label, .row-between", { y: 20, opacity: 0, duration: 0.5, stagger: 0.1 }, "-=0.3")
          .from(".btn-primary, .signup-line", { y: 20, opacity: 0, duration: 0.5, stagger: 0.1 }, "-=0.2");

        // 3. Interactive Button Hover Effect
        const btn = document.querySelector('.btn-primary');
        btn.addEventListener('mouseenter', () => gsap.to(btn, { scale: 1.02, duration: 0.3 }));
        btn.addEventListener('mouseleave', () => gsap.to(btn, { scale: 1, duration: 0.3 }));
    }
});

// --- FORM SUBMISSION LOGIC (Keep your existing logic below) ---
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

    const redirect = sessionStorage.getItem('redirect_after_login');
    if (redirect) {
        sessionStorage.removeItem('redirect_after_login');
        setTimeout(() => location.href = redirect, 1500);
        return;
    }

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
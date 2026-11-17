export const validators = {
  email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
  minLen: (v, n) => typeof v === 'string' && v.length >= n,
  maxLen: (v, n) => typeof v === 'string' && v.length <= n,
  username: (v) => /^[A-Za-z0-9_]{3,30}$/.test(v),
  password: (v) => v.length >= 8 && /[A-Za-z]/.test(v) && /\d/.test(v),
  sixDigitCode: (v) => /^\d{6}$/.test(v),
};

/**
 * Tiny form validator. Attach to a form, pass rules, get back
 * a boolean + the first error message.
 */
export function validateForm(form, rules) {
  for (const [field, checks] of Object.entries(rules)) {
    const input = form.elements[field];
    if (!input) continue;
    const value = input.value.trim();

    for (const check of checks) {
      const result = check(value);
      if (result !== true) {
        return { ok: false, field, message: result };
      }
    }
  }
  return { ok: true };
}
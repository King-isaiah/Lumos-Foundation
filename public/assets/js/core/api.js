/**
 * Single fetch wrapper for the whole app.
 * Handles auth redirects, error surfacing, and request IDs.
 */

const getApiPath = () => {
  const path = window.location.pathname;
  // If we are inside an 'admin' OR 'students' folder
  if (path.includes('/admin/') || path.includes('/students/')) {
    return '../../class/ApiHandler.php';
  }
  // If we are in the public root folder (e.g. login.php)
  return '../class/ApiHandler.php';
};

const BASE = getApiPath();

export class ApiError extends Error {
  constructor(message, code, requestId) {
    super(message);
    this.code = code;
    this.requestId = requestId;
  }
}

export async function api(entity, action, { method = 'GET', id, body, query } = {}) {
  let url = `${BASE}?entity=${entity}&action=${action}`;
  
  if (id !== undefined && id !== null) url += `&id=${id}`;
  if (query) {
    Object.entries(query).forEach(([k, v]) => {
      if (v !== undefined && v !== null) url += `&${k}=${encodeURIComponent(v)}`;
    });
  }

  const res = await fetch(url, {
    method,
    credentials: 'include',
    headers: body ? { 'Content-Type': 'application/json' } : {},
    body: body ? JSON.stringify(body) : undefined,
  });

  let json;
  try { 
    json = await res.json(); 
  } catch { 
    throw new ApiError('Unexpected response from server.', res.status); 
  }

  if (res.status === 401) {
    // Updated to .php for the live server
    if (!location.pathname.includes('login') && !location.pathname.includes('register')) {
      sessionStorage.setItem('redirect_after_login', location.pathname);
      location.href = './login.php'; 
    }
    throw new ApiError(json.message || 'Please log in.', 401, json.requestId);
  }

  if (!json.success) {
    throw new ApiError(json.message || 'Something went wrong.', res.status, json.requestId);
  }

  return json.data;
}
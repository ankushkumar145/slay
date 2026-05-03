import { Platform } from 'react-native';

export const API_URL =
  process.env.EXPO_PUBLIC_API_URL ??
  Platform.select({
    android: 'http://10.0.2.2:8000/api',
    default: 'http://127.0.0.1:8000/api',
  });

const tokenKey = 'slay_token';
const resultKey = 'slay_last_result';

export function getStoredToken() {
  if (Platform.OS !== 'web' || typeof window === 'undefined') {
    return '';
  }

  return window.localStorage.getItem(tokenKey) ?? '';
}

export function saveStoredToken(token) {
  if (Platform.OS !== 'web' || typeof window === 'undefined') {
    return;
  }

  if (token) {
    window.localStorage.setItem(tokenKey, token);
  } else {
    window.localStorage.removeItem(tokenKey);
  }
}

export function getStoredResult() {
  if (Platform.OS !== 'web' || typeof window === 'undefined') {
    return null;
  }

  try {
    return JSON.parse(window.localStorage.getItem(resultKey));
  } catch {
    return null;
  }
}

export function saveStoredResult(result) {
  if (Platform.OS !== 'web' || typeof window === 'undefined') {
    return;
  }

  if (result) {
    window.localStorage.setItem(resultKey, JSON.stringify(result));
  } else {
    window.localStorage.removeItem(resultKey);
  }
}

export async function apiRequest(path, { method = 'GET', body, token } = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    method,
    headers: {
      Accept: 'application/json',
      ...(body ? { 'Content-Type': 'application/json' } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = payload.message ?? Object.values(payload.errors ?? {})[0]?.[0] ?? 'Request failed.';
    throw new Error(message);
  }

  return payload;
}

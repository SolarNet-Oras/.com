/**
 * Secure Token Storage Utility
 * 
 * SECURITY CONSIDERATIONS:
 * ======================
 * 
 * CURRENT IMPLEMENTATION (Development):
 * - Uses sessionStorage (better than localStorage - cleared on tab close)
 * - Not vulnerable to CSRF attacks
 * - Still vulnerable to XSS attacks
 * 
 * PRODUCTION RECOMMENDATIONS:
 * - Use httpOnly cookies set by the backend
 * - Implement SameSite cookie attributes
 * - Use secure flag for HTTPS-only transmission
 * - Consider implementing token refresh mechanism
 * - Add Content Security Policy (CSP) headers
 * 
 * BACKEND REQUIREMENTS for Production:
 * - Set tokens as httpOnly cookies
 * - Implement CSRF token protection
 * - Use SameSite=Strict or SameSite=Lax
 * - Enable Secure flag in production
 */

const TOKEN_KEY = 'auth_token';
const REFRESH_TOKEN_KEY = 'refresh_token';

export interface TokenStorage {
  getToken(): string | null;
  setToken(token: string): void;
  removeToken(): void;
  getRefreshToken(): string | null;
  setRefreshToken(token: string): void;
  removeRefreshToken(): void;
  clearAll(): void;
}

/**
 * Session Storage Implementation (Default for Development)
 * 
 * Pros:
 * - Automatically cleared when tab is closed
 * - Not shared across tabs
 * - Reduced attack surface compared to localStorage
 * 
 * Cons:
 * - Still accessible via JavaScript (XSS vulnerable)
 * - Not suitable for long-term sessions
 */
class SessionStorageTokenManager implements TokenStorage {
  private storage: Storage;

  constructor() {
    this.storage = window.sessionStorage;
  }

  getToken(): string | null {
    try {
      return this.storage.getItem(TOKEN_KEY);
    } catch (error) {
      console.error('Failed to get token:', error);
      return null;
    }
  }

  setToken(token: string): void {
    try {
      this.storage.setItem(TOKEN_KEY, token);
    } catch (error) {
      console.error('Failed to set token:', error);
    }
  }

  removeToken(): void {
    try {
      this.storage.removeItem(TOKEN_KEY);
    } catch (error) {
      console.error('Failed to remove token:', error);
    }
  }

  getRefreshToken(): string | null {
    try {
      return this.storage.getItem(REFRESH_TOKEN_KEY);
    } catch (error) {
      console.error('Failed to get refresh token:', error);
      return null;
    }
  }

  setRefreshToken(token: string): void {
    try {
      this.storage.setItem(REFRESH_TOKEN_KEY, token);
    } catch (error) {
      console.error('Failed to set refresh token:', error);
    }
  }

  removeRefreshToken(): void {
    try {
      this.storage.removeItem(REFRESH_TOKEN_KEY);
    } catch (error) {
      console.error('Failed to remove refresh token:', error);
    }
  }

  clearAll(): void {
    this.removeToken();
    this.removeRefreshToken();
  }
}

/**
 * Memory Storage Implementation (Most Secure for SPA)
 * 
 * Pros:
 * - Not accessible via any storage API
 * - Automatically cleared on page refresh
 * - Best protection against XSS
 * 
 * Cons:
 * - Lost on page refresh (requires re-login or token refresh)
 * - Not persisted across tabs
 * 
 * Best for: High-security applications with short sessions
 */
class MemoryStorageTokenManager implements TokenStorage {
  private token: string | null = null;
  private refreshToken: string | null = null;

  getToken(): string | null {
    return this.token;
  }

  setToken(token: string): void {
    this.token = token;
  }

  removeToken(): void {
    this.token = null;
  }

  getRefreshToken(): string | null {
    return this.refreshToken;
  }

  setRefreshToken(token: string): void {
    this.refreshToken = token;
  }

  removeRefreshToken(): void {
    this.refreshToken = null;
  }

  clearAll(): void {
    this.token = null;
    this.refreshToken = null;
  }
}

/**
 * Cookie Storage Implementation (Production Ready)
 * 
 * NOTE: For production, prefer backend-set httpOnly cookies
 * This implementation is for scenarios where backend cannot set cookies
 * 
 * Pros:
 * - Can be set as httpOnly by backend (JavaScript inaccessible)
 * - Supports SameSite protection
 * - Automatic HTTPS-only with Secure flag
 * 
 * Cons:
 * - Vulnerable to CSRF if not properly configured
 * - Requires CSRF token implementation
 */
class CookieStorageTokenManager implements TokenStorage {
  private setCookie(name: string, value: string, days: number = 7): void {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = `expires=${date.toUTCString()}`;
    const secure = window.location.protocol === 'https:' ? 'Secure;' : '';
    const sameSite = 'SameSite=Strict;';
    
    document.cookie = `${name}=${value};${expires};path=/;${secure}${sameSite}`;
  }

  private getCookie(name: string): string | null {
    const nameEQ = `${name}=`;
    const cookies = document.cookie.split(';');
    
    for (let cookie of cookies) {
      cookie = cookie.trim();
      if (cookie.indexOf(nameEQ) === 0) {
        return cookie.substring(nameEQ.length);
      }
    }
    return null;
  }

  private deleteCookie(name: string): void {
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
  }

  getToken(): string | null {
    return this.getCookie(TOKEN_KEY);
  }

  setToken(token: string): void {
    this.setCookie(TOKEN_KEY, token);
  }

  removeToken(): void {
    this.deleteCookie(TOKEN_KEY);
  }

  getRefreshToken(): string | null {
    return this.getCookie(REFRESH_TOKEN_KEY);
  }

  setRefreshToken(token: string): void {
    this.setCookie(REFRESH_TOKEN_KEY, token, 30); // Longer expiry for refresh token
  }

  removeRefreshToken(): void {
    this.deleteCookie(REFRESH_TOKEN_KEY);
  }

  clearAll(): void {
    this.removeToken();
    this.removeRefreshToken();
  }
}

// Export the appropriate storage manager
// Change this based on your security requirements and deployment environment

/**
 * CONFIGURATION:
 * =============
 * 
 * Choose one based on your needs:
 * 
 * 1. SessionStorageTokenManager - Good for development, cleared on tab close
 * 2. MemoryStorageTokenManager - Best security, lost on refresh
 * 3. CookieStorageTokenManager - Production ready, but prefer backend httpOnly cookies
 * 
 * For Production: Configure backend to set httpOnly cookies and remove frontend token management
 */
export const tokenStorage: TokenStorage = new SessionStorageTokenManager();

// Alternative exports for flexibility
export const createTokenStorage = (type: 'session' | 'memory' | 'cookie' = 'session'): TokenStorage => {
  switch (type) {
    case 'memory':
      return new MemoryStorageTokenManager();
    case 'cookie':
      return new CookieStorageTokenManager();
    case 'session':
    default:
      return new SessionStorageTokenManager();
  }
};

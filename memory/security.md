# Security Best Practices - ISP Billing System

## 🔒 Authentication & Token Storage

### Current Implementation (Development)

**Token Storage: sessionStorage**
- ✅ Better than localStorage (cleared on tab close)
- ✅ Not vulnerable to CSRF attacks
- ✅ Reduced attack surface
- ⚠️ Still accessible via JavaScript (XSS vulnerable)
- ⚠️ Not suitable for long-term sessions

### Production Recommendations

#### 1. Use httpOnly Cookies (RECOMMENDED)

**Backend Configuration (Laravel):**
```php
// In your authentication controller
return response()->json([
    'user' => $user,
    'message' => 'Login successful'
])->cookie(
    'auth_token',
    $token,
    60 * 24 * 7, // 7 days
    '/',
    null,
    true, // secure (HTTPS only)
    true, // httpOnly (not accessible via JavaScript)
    false,
    'strict' // SameSite policy
);
```

**Advantages:**
- ✅ Not accessible via JavaScript (XSS protection)
- ✅ Automatically sent with requests
- ✅ SameSite protection against CSRF
- ✅ Secure flag ensures HTTPS-only

**Frontend Changes Required:**
```typescript
// Remove token from storage
// Backend will handle token via httpOnly cookies
// Add CSRF token handling
```

#### 2. Content Security Policy (CSP)

Add CSP headers to prevent XSS attacks:

```nginx
# In Nginx configuration
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';" always;
```

#### 3. CSRF Protection

**Laravel CSRF Token:**
```typescript
// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Add to requests
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
```

## 🛡️ API Security

### Rate Limiting

**Laravel Configuration:**
```php
// In routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // API routes limited to 60 requests per minute
});
```

### Input Validation

Always validate input on the backend:

```php
// In Laravel Request
public function rules(): array
{
    return [
        'email' => 'required|email|max:255',
        'password' => 'required|min:8|confirmed',
    ];
}
```

### SQL Injection Prevention

✅ **Using Eloquent ORM** (protected by default)
```php
// Safe - uses parameter binding
User::where('email', $request->email)->first();
```

❌ **Avoid raw queries without bindings**
```php
// Unsafe
DB::raw("SELECT * FROM users WHERE email = '$email'");

// Safe
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
```

## 🔐 Password Security

### Current Implementation

**Laravel Bcrypt:**
- ✅ Bcrypt hashing (12 rounds)
- ✅ Automatic salting
- ✅ Industry standard

### Best Practices

1. **Password Requirements:**
   - Minimum 8 characters
   - Mix of uppercase and lowercase
   - Numbers and special characters
   - Not in common password list

2. **Password Reset:**
   - Time-limited reset tokens
   - Single-use tokens
   - Secure random token generation

## 🌐 CORS Configuration

### Development
```php
// config/cors.php
'allowed_origins' => ['*'], // Allow all for development
```

### Production
```php
// config/cors.php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://www.yourdomain.com'
],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
'supports_credentials' => true,
```

## 🔍 XSS Prevention

### Frontend

1. **React Default Protection:**
   - React escapes content by default
   - Safe to render user input as text

2. **Dangerous Operations:**
```typescript
// ❌ Avoid dangerouslySetInnerHTML
<div dangerouslySetInnerHTML={{__html: userInput}} />

// ✅ Use text content
<div>{userInput}</div>
```

3. **URL Sanitization:**
```typescript
// Sanitize user-provided URLs
const isSafeUrl = (url: string): boolean => {
  return url.startsWith('http://') || url.startsWith('https://');
};
```

### Backend

1. **Output Escaping:**
```php
// In Blade templates
{{ $userInput }} // Escaped by default
{!! $userInput !!} // Unescaped - avoid with user input
```

2. **HTML Purifier:**
```bash
composer require mews/purifier
```

## 🔒 Database Security

### Connection Security

```env
# .env file
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1 # Use localhost, not 0.0.0.0
DB_PORT=5432
DB_DATABASE=isp_billing
DB_USERNAME=isp_user
DB_PASSWORD=strong_random_password_here

# PostgreSQL SSL (Production)
DB_SSLMODE=require
```

### Backup Security

1. **Encrypt database backups**
2. **Store backups off-site**
3. **Regular backup testing**
4. **Access control on backups**

## 🔐 API Authentication

### JWT Token Security

**Token Structure:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Security Measures:**
1. ✅ Short expiration time (1 hour)
2. ✅ Token refresh mechanism
3. ✅ Secure token storage
4. ✅ Token revocation on logout

### Token Refresh Flow

```typescript
// Auto-refresh before expiration
const refreshToken = async (): Promise<void> => {
  try {
    const response = await api.post('/auth/refresh');
    setAuthToken(response.data.access_token);
  } catch (error) {
    // Logout on refresh failure
    clearAuth();
    window.location.href = '/login';
  }
};
```

## 🚨 Security Headers

### Recommended Headers

```nginx
# Nginx configuration
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```

### Laravel Security Headers

```php
// In Middleware
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    
    return $response;
}
```

## 📝 Audit Logging

### What to Log

1. **Authentication Events:**
   - Login attempts (success/failure)
   - Logout events
   - Password changes
   - Password reset requests

2. **Data Modifications:**
   - Customer record changes
   - Service plan modifications
   - Payment transactions
   - Router configuration changes

3. **Security Events:**
   - Failed authorization attempts
   - Rate limit violations
   - Suspicious activity patterns

### Log Storage

```php
// Laravel Activity Log
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use LogsActivity;
    
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
}
```

## 🔄 Security Checklist

### Development
- [x] sessionStorage for tokens
- [x] HTTPS in development
- [x] Input validation
- [x] SQL injection protection (ORM)
- [x] XSS protection (React)
- [x] CORS configured

### Pre-Production
- [ ] Change to httpOnly cookies
- [ ] Implement CSRF protection
- [ ] Add rate limiting
- [ ] Configure CSP headers
- [ ] Set up security headers
- [ ] Enable audit logging
- [ ] Implement token refresh
- [ ] Set up SSL/TLS certificates

### Production
- [ ] Security audit completed
- [ ] Penetration testing
- [ ] Backup encryption enabled
- [ ] Monitoring and alerting
- [ ] Incident response plan
- [ ] Regular security updates
- [ ] Access control review
- [ ] Data encryption at rest

## 📚 References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security](https://laravel.com/docs/12.x/security)
- [React Security Best Practices](https://react.dev/learn/security)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)

---

**Last Updated:** July 1, 2026  
**Security Review:** Required before production deployment

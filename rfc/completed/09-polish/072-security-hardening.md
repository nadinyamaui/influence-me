# 072 - Security Hardening

**Labels:** `security`, `enhancement`
**Depends on:** #016, #019

## Description

Review and harden security across the application, focusing on external integrations, user input, and access control.

## Tasks

### Rate Limiting
Add rate limiting to sensitive endpoints in `bootstrap/app.php` or route definitions:
- Portal login: 5 attempts per minute per IP (already in #019)
- Instagram OAuth callback: 10 per minute per IP

### Token Storage
Verify all tokens are encrypted at rest:
- `InstagramAccount.access_token` — encrypted cast (done in #003)

### Markdown XSS Prevention
When rendering proposal markdown content, sanitize HTML output:
```php
// Use Str::markdown() which is safe by default (strips HTML)
// Or explicitly: strip_tags(Str::markdown($content), '<p><h1><h2><h3><ul><ol><li><strong><em><a><code><pre><blockquote>')
```

### CSRF Protection
Verify all forms have `@csrf` (Livewire handles this automatically).
Verify all external callback routes have appropriate middleware and validation.

### Mass Assignment Audit
Standardize model mass assignment to:
- `protected $guarded = [];`

Then verify over-assignment is prevented by validation + authorization boundaries:
- Sensitive ownership fields (for example `user_id`) are set programmatically in Livewire/actions/services
- IDs and guarded business state fields are never accepted from untrusted input without explicit authorization

### Content Security Policy
Add CSP headers via middleware (optional, discuss with user):
```php
// In a middleware or bootstrap/app.php
$middleware->append(\App\Http\Middleware\SecurityHeaders::class);
```

### Input Validation
Verify all form requests have proper validation rules:
- Max lengths on all string fields
- Proper type validation
- Enum validation for status fields

## Files to Create
- `app/Http/Middleware/SecurityHeaders.php` (optional)

## Files to Modify
- `bootstrap/app.php` — rate limiting, middleware
- Review all models for `protected $guarded = [];` consistency and input-boundary safety
- Review all Form Requests for completeness

## Acceptance Criteria
- [ ] Rate limiting on portal login and Instagram OAuth callback
- [ ] All tokens stored encrypted
- [ ] Markdown rendering is XSS-safe
- [ ] No mass assignment vulnerabilities
- [ ] All form inputs validated with max lengths
- [ ] Feature tests verify rate limiting

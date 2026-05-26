# Email Verification & Bot Prevention Setup Guide

## Overview
This guide explains the email verification and bot prevention features implemented in Wing POS.

## Features Implemented

### 1. **Email Verification Requirement**
- Users must verify their email before they can log in
- Verification emails are sent automatically during registration
- Users are redirected to the email verification notice page if they try to log in without verifying their email
- Error message: "Please verify your email address before logging in. Check your inbox for the verification link."

### 2. **Bot Prevention Mechanisms**

#### A. User-Agent Validation
- Detects and blocks common bot patterns (curl, wget, python, java, etc.)
- Checks for missing standard browser identifiers
- Automatically rejects requests from suspicious user agents

#### B. reCAPTCHA v3 Integration
- Invisible captcha verification on login
- No user interaction required (runs in background)
- Detects bot behavior through user interactions

## Setup Instructions

### Step 1: Get reCAPTCHA Keys

1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Sign in with your Google account
3. Click "Create" or "+" to create a new site
4. Fill in the form:
   - Label: "Wing POS"
   - reCAPTCHA type: **reCAPTCHA v3** (for invisible captcha)
   - Domains: your-domain.com (add your actual domain)
5. Accept the reCAPTCHA Terms of Service
6. Click "Create"
7. You will see your **Site Key** and **Secret Key**

### Step 2: Configure Environment Variables

Add these to your `.env` file:

```env
RECAPTCHA_PUBLIC_KEY=your_site_key_here
RECAPTCHA_SECRET_KEY=your_secret_key_here
RECAPTCHA_VERSION=v3
```

**Example:**
```env
RECAPTCHA_PUBLIC_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1HoRo9WVJDkJbIxn3pnLp
RECAPTCHA_VERSION=v3
```

### Step 3: (Optional) Development/Testing

If you don't want to use reCAPTCHA in development:

1. Leave `RECAPTCHA_PUBLIC_KEY` and `RECAPTCHA_SECRET_KEY` empty in `.env`
2. The system will skip captcha validation when keys are not configured
3. But email verification will still be required

### Step 4: Test the Setup

1. Register a new account
2. You should receive a verification email
3. Verify your email by clicking the link
4. Try to log in with unverified email (should fail with verification message)
5. Log in after verifying email (should succeed)
6. Try to login with a bot user agent (should be blocked)

## Testing with Bot User Agents

You can test bot detection using curl or similar tools:

```bash
# This will be blocked
curl -X POST http://your-domain/login \
  -H "User-Agent: curl/7.68.0" \
  -d "email=user@example.com&password=password"

# This will also be blocked
curl -X POST http://your-domain/login \
  -H "User-Agent: Python-Requests/2.25.1" \
  -d "email=user@example.com&password=password"
```

## Implementation Details

### Files Modified

1. **app/Http/Controllers/Auth/AuthenticatedSessionController.php**
   - Added email verification check
   - Added bot detection logic (isSuspiciousBot method)
   - Added captcha validation

2. **app/Rules/CaptchaRule.php** (new)
   - Custom validation rule for reCAPTCHA
   - Verifies token with Google's API
   - Checks reCAPTCHA score (v3)

3. **config/services.php**
   - Added reCAPTCHA configuration block

4. **resources/views/auth/login.blade.php**
   - Added reCAPTCHA script
   - Added hidden input field for captcha response
   - Added JavaScript to handle captcha validation
   - Added error display for better UX

## Error Messages

Users will see these error messages in specific scenarios:

| Scenario | Error Message |
|----------|---------------|
| No email verification | "Please verify your email address before logging in. Check your inbox for the verification link." |
| Bot user agent detected | "Access denied. Please use a standard web browser." |
| Captcha verification failed | "Captcha verification failed. Please try again." |
| Suspicious activity (low score) | "We detected unusual activity. Please try again." |
| Invalid credentials | "The provided credentials do not match our records." |

## Security Considerations

### What This Protects Against:
- ✅ Brute force attacks (rate limiting + captcha)
- ✅ Automated bot login attempts
- ✅ Unverified email accounts accessing the system
- ✅ Suspicious/unusual login patterns (via reCAPTCHA v3 scoring)

### What This Does NOT Protect Against:
- ❌ Phishing attacks
- ❌ Compromised credentials
- ❌ Man-in-the-middle attacks (use HTTPS!)

### Best Practices:
1. Always use HTTPS for your production environment
2. Keep reCAPTCHA keys secret (never commit to git)
3. Monitor failed login attempts in audit logs
4. Implement additional rate limiting if needed
5. Regularly review and update allowed domains in reCAPTCHA console

## Troubleshooting

### Issue: Captcha always fails
- Check that RECAPTCHA_SECRET_KEY is correct
- Verify the domain is added in reCAPTCHA admin console
- Check server firewall/network allows connections to Google API

### Issue: Legitimate users blocked as bots
- Review the user agent detection logic in `isSuspiciousBot()`
- Whitelist specific user agents if needed
- Ensure the user is using a standard browser

### Issue: Email verification emails not being sent
- Check mail configuration in `.env`
- Verify MAIL_DRIVER is set correctly
- Check application logs in `storage/logs/laravel.log`

## References

- [Google reCAPTCHA Documentation](https://developers.google.com/recaptcha/docs/v3)
- [Laravel Validation Rules](https://laravel.com/docs/validation#available-validation-rules)
- [Laravel Email Verification](https://laravel.com/docs/verification)

# Google SSO Setup Instructions

## Overview
I've implemented Google Single Sign-On (SSO) for your FitFuel application. Here's what has been added:

## Files Created/Modified

### New Files:
1. **`config/google_config.php`** - Google OAuth configuration
2. **`google_callback.php`** - Handles Google OAuth callback
3. **`GOOGLE_SSO_SETUP.md`** - This setup guide

### Modified Files:
1. **`login.php`** - Added Google SSO login functionality
2. **`registration.php`** - Added Google SSO registration functionality

## Google Cloud Console Setup

### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Google+ API (or Google Identity API)

### Step 2: Configure OAuth Consent Screen
1. Go to "APIs & Services" > "OAuth consent screen"
2. Choose "External" user type
3. Fill in required fields:
   - App name: "FitFuel"
   - User support email: your email
   - Developer contact: your email
4. Add scopes: `email`, `profile`, `openid`
5. Save and continue

### Step 3: Create OAuth 2.0 Credentials
1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application"
4. Add authorized redirect URIs:
   - `http://localhost/fitfuel_sia/google_callback.php` (for local development)
   - `https://yourdomain.com/fitfuel_sia/google_callback.php` (for production)
5. Save and copy the Client ID and Client Secret

### Step 4: Update Configuration
1. Open `config/google_config.php`
2. Replace the placeholder values:
   ```php
   define('GOOGLE_CLIENT_ID', 'your-actual-client-id.apps.googleusercontent.com');
   define('GOOGLE_CLIENT_SECRET', 'your-actual-client-secret');
   define('GOOGLE_REDIRECT_URI', 'http://localhost/fitfuel_sia/google_callback.php');
   ```

## Features Implemented

### Login Page (`login.php`)
- Google SSO button that redirects to Google OAuth
- Error handling for SSO failures
- Seamless integration with existing login form

### Registration Page (`registration.php`)
- Google SSO registration option
- Automatic account creation for new Google users
- Account linking for existing users with same email

### Google Callback (`google_callback.php`)
- Handles OAuth callback from Google
- Creates new users or logs in existing users
- Links Google accounts to existing email accounts
- Role-based redirection after authentication

## Database Integration

The system uses the existing `users` table with the `google_id` field:
- New Google users: Creates account with `google_id` populated
- Existing users: Links Google account to existing email
- Maintains all existing user roles and permissions

## Security Features

1. **Secure Token Exchange**: Uses server-side token exchange
2. **Account Linking**: Safely links Google accounts to existing emails
3. **Role Preservation**: Maintains user roles and permissions
4. **Error Handling**: Comprehensive error handling and user feedback

## Testing

1. Update the configuration with your Google OAuth credentials
2. Test the login flow:
   - Click "Continue with Google" on login page
   - Complete Google OAuth flow
   - Verify redirection based on user role
3. Test the registration flow:
   - Click "Sign up with Google" on registration page
   - Complete Google OAuth flow
   - Verify account creation

## Production Deployment

For production deployment:
1. Update `GOOGLE_REDIRECT_URI` to your production domain
2. Add production redirect URI in Google Cloud Console
3. Ensure HTTPS is enabled for security
4. Test thoroughly before going live

## Troubleshooting

### Common Issues:
1. **"Invalid redirect URI"**: Check that the redirect URI in Google Console matches exactly
2. **"Client ID not found"**: Verify the Client ID is correct in `google_config.php`
3. **"Access denied"**: Check OAuth consent screen configuration
4. **Database errors**: Ensure the `google_id` field exists in the users table

### Debug Mode:
Add this to `google_callback.php` for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

If you encounter issues:
1. Check Google Cloud Console logs
2. Verify database connection
3. Check PHP error logs
4. Ensure all required PHP extensions are installed (curl, json)

The Google SSO implementation is now complete and ready for testing!

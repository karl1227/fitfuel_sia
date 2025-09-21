# Forgot Password - Simple Setup

## What You Need
Just 3 files for forgot password functionality:

1. **`login.php`** - Modified with forgot password modal
2. **`forgot_password.php`** - Handles password reset requests  
3. **`reset_password.php`** - Password reset page
4. **`SQL FILES/password_reset_table.sql`** - Database table (run once)

## Database Setup
Run this SQL once:
```sql
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);
```

## How It Works
1. User clicks "Forget Password?" on login page
2. Enters email address in modal
3. Reset link is generated and logged to PHP error log
4. User uses the reset link to set new password
5. Done!

## For Production
- Uncomment the `mail()` function in `forgot_password.php` 
- Configure your server's email settings
- Update the "From" email address

That's it! Simple and clean.

## Change this line:
## $resetLink = "http://localhost/fitfuel_sia/reset_password.php?token=" . $resetToken;

## To your actual domain:
## $resetLink = "https://yourdomain.com/reset_password.php?token=" . $resetToken;
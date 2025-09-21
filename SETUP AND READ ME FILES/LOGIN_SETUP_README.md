# FitFuel Login & Registration System

This project includes a complete login and registration system for the FitFuel fitness application.

## Features

### Login Page (`login.php`)
- Modern, responsive design with background image
- Username/Email and password fields
- Password visibility toggle
- Remember me checkbox
- Google SSO button (placeholder for future implementation)
- Role-based redirection after login
- Error handling and validation

### Registration Page (`registration.php`)
- User-friendly registration form
- Username, email, password, and confirm password fields
- Password strength validation
- Terms and conditions checkbox
- Google SSO registration option
- Success/error message handling

### Authentication System
- Secure password hashing using PHP's `password_hash()`
- Session management
- Role-based access control (admin, manager, staff, customer)
- Database connection with PDO
- Input validation and sanitization

## Database Setup

1. **Create Database**: Run the `database_setup.sql` file in your MySQL/MariaDB server
2. **Default Credentials**:
   - Admin: `admin` / `password`
   - Customer: `customer` / `password`

## File Structure

```
├── config/
│   └── database.php          # Database configuration
├── login.php                 # Login page
├── registration.php          # Registration page
├── logout.php               # Logout functionality
├── auth_check.php           # General authentication check
├── admin_auth_check.php     # Admin authentication check
├── database_setup.sql       # Database setup script
└── README.md                # This file
```

## Usage

### For Customers
1. Visit `registration.php` to create an account
2. Login at `login.php`
3. Redirected to `index.html` after successful login

### For Admin/Staff
1. Login at `login.php` with admin credentials
2. Redirected to `admin/dashboard.php` after successful login

### Protecting Pages
- Include `auth_check.php` in customer pages
- Include `admin_auth_check.php` in admin pages

## Security Features

- Password hashing with `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session security
- Input validation
- CSRF protection ready (can be added)

## Customization

### Database Configuration
Edit `config/database.php` to match your database settings:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fitfuel_sia');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Styling
The pages use Tailwind CSS and custom styles. Modify the `<style>` sections in the PHP files to customize the appearance.

## Next Steps

1. **Google SSO Integration**: Implement actual Google OAuth2 authentication
2. **Password Reset**: Add forgot password functionality
3. **Email Verification**: Add email verification for new accounts
4. **Profile Management**: Create user profile pages
5. **Admin Dashboard**: Complete the admin dashboard functionality

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher
- Web server (Apache/Nginx)
- PDO MySQL extension enabled

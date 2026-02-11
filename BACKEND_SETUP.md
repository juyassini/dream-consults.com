# Dream Consults Backend Setup

This project uses both **PHP** and **Flask** for backend functionality:
- **PHP**: Contact form handling with SQLite database
- **Flask**: Legacy API and optional email sending

## File Structure

```
01/
├── contact.php          # PHP contact form handler
├── config.php           # PHP configuration
├── app.py              # Flask backend
├── script.js           # Frontend (sends to both backends)
├── admin/
│   └── submissions.php  # Admin panel to view submissions
├── requirements.txt     # Python dependencies
└── submissions.db       # SQLite database (auto-created)
```

## Setup Instructions

### 1. Install Flask (Python)

```bash
python -m pip install -r requirements.txt
```

### 2. Start Flask Server (Port 5000)

```bash
python app.py
```

The Flask app will be available at `http://localhost:5000/`

### 3. Start PHP Server (Port 8000)

In a **new terminal**, navigate to the project folder and run:

```bash
php -S localhost:8000
```

The PHP app will be available at `http://localhost:8000/`

### 4. Open Your Website

- **Using Flask**: http://localhost:5000/
- **Using PHP**: http://localhost:8000/
- **Admin Panel**: http://localhost:8000/admin/submissions.php (password: `dream2026`)

## Form Submission Flow

When users submit the contact form, `script.js` will:
1. First try to POST to PHP (`/contact.php`)
2. Fallback to Flask (`/api/contact`) if PHP fails
3. Save locally if both backends fail

## Database

Contact submissions are stored in `submissions.db` (SQLite):
- Auto-created on first submission
- View submissions via Admin Panel: http://localhost:8000/admin/submissions.php

## Changing Admin Password

**IMPORTANT**: The default password `dream2026` is plaintext and visible in the code. For production use, you MUST use a secure hashed password.

### Option A: Use Environment Variable (Recommended)

1. Generate a secure password hash:

```bash
php -r "echo password_hash('your-secure-password-here', PASSWORD_DEFAULT);"
```

2. Copy the generated hash (starts with `$2y$`)

3. Add to your `.env` file:

```
ADMIN_PASSWORD_HASH=$2y$10$YourGeneratedHashHere...
```

4. Restart your PHP server

### Option B: Edit Code Directly (Less Secure)

Edit `admin/submissions.php` line 13:

```php
define('ADMIN_PASSWORD_PLAIN', 'your-new-password');
```

**WARNING**: This exposes the password in your source code. Use Option A for production.

## Troubleshooting

### PHP: "No such file or directory"
- Make sure you're running `php -S` from the correct project folder

### Flask: Port already in use
```bash
# Kill process on port 5000 (Windows PowerShell)
Stop-NetTCPConnection -LocalPort 5000 -Force

# Or use a different port
python app.py --port 5001
```

### Database errors in PHP
- Check that the project folder is writable
- Ensure SQLite is enabled in your PHP installation (`php -m | grep sqlite`)


## Upgrading to Real Server

### For PHP:
- Upload all PHP files to your hosting's public folder
- Update `config.php` with your database and email settings

### For Flask:
- Use `gunicorn` or `Waitress` for production
- Set environment variables for sensitive data
- Use a reverse proxy (Nginx/Apache) in front

## Support

For issues, check:
- `php_errors.log` (PHP errors)
- Flask console output
- Browser Developer Tools (Network tab)

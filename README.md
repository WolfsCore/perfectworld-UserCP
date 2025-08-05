# User Control Panel (UCP) - Complete Multi-Language Game Server Management System WIP

A modern, secure, and multilingual User Control Panel for game servers featuring a dark theme, reCAPTCHA v3 integration, and email activation.

NOTE THIS IS NOT FOR THE NORMAL LEAKED SERVERS BUT U CAN MODIFI IT

# New Server ill relesed soon 

https://dev.drenor.de/info.html

## 🌟 Main Features

### 🔒 Security
- **reCAPTCHA v3** - Protection against bots and automated attacks
- **Email Verification** - Secure account activation
- **Password Hashing** - Modern Argon2ID encryption
- **Session Management** - Secure session handling with timeout
- **Rate Limiting** - Protection from brute-force attacks
- **CSRF Protection** - Cross-Site Request Forgery protection
- **SQL Injection Protection** - Prepared statements for all database queries

### 🌍 Multi-Language Support
- **German (DE)** - Fully translated
- **English (EN)** - Fully translated
- **Portuguese (BR)** - Fully translated
- **Chinese (CN)** - Fully translated
- **Vietnamese (VM)** - Fully translated
- **Russian (RU)** - Fully translated

### 🎮 Game Features
- **Character Management** - Create, manage, and delete game characters
- **Account Dashboard** - Overview of game stats and activities
- **Server Status** - Real-time game server status display
- **User Profiles** - Complete profile management
- **Game Statistics** - Detailed player and character statistics

### 🎨 Design & UX
- **Dark Theme** - Modern gaming design with a dark color scheme
- **Responsive Design** - Optimized for desktop, tablet, and mobile
- **Smooth Animations** - Seamless transitions and animations
- **Accessibility** - ARIA labels and keyboard navigation
- **PWA-Ready** - Service worker for offline functionality

## 📋 Requirements

### Server
- **PHP 8.0+** with extensions:
  - PDO (MySQL/MariaDB)
  - OpenSSL
  - cURL
  - JSON
  - mbstring
  - intl (optional for advanced localization)
- **MySQL 8.0+** or **MariaDB 10.5+**
- **Apache 2.4+** or **Nginx 1.18+**
- **SSL/TLS Certificate** (recommended)

### Client
- **Modern Browsers** (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- **JavaScript enabled**
- **Cookies enabled**

## 🚀 Installation

### 1. Clone the Repository
```bash
git clonehttps://github.com/WolfsCore/perfectworld-UserCP.git
cd ucp
```

### 2. Install Dependencies
```bash
# If using Composer
composer install

# Or install required libraries manually
```

### 3. Configuration
```bash
# Copy the example config
cp config/config.example.php config/config.php

# Edit the config file
nano config/config.php
```

### 4. Database Setup
```sql
-- Create the database
CREATE DATABASE gameserver CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import the structure
mysql -u username -p gameserver < database/structure.sql

-- (Optional) Add sample data
mysql -u username -p gameserver < database/sample_data.sql
```

### 5. Directory Permissions
```bash
# Set write permissions for log and cache directories
chmod 755 logs/
chmod 755 cache/
chmod 755 uploads/

# For Apache
chown -R www-data:www-data logs/ cache/ uploads/

# For Nginx
chown -R nginx:nginx logs/ cache/ uploads/
```

### 6. reCAPTCHA Setup
1. Go to [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Create a new reCAPTCHA v3 project
3. Copy the keys into the config file:
```php
define('RECAPTCHA_SITE_KEY', 'your-site-key-here');
define('RECAPTCHA_SECRET_KEY', 'your-secret-key-here');
```

### 7. Email Configuration
```php
// SMTP settings in config.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### 8. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'"

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/html/ucp;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

## 🔧 Configuration

### Main Config File (`config/config.php`)

```php
<?php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'gameserver');
define('DB_USER', 'username');
define('DB_PASS', 'password');

// reCAPTCHA v3
define('RECAPTCHA_SITE_KEY', 'your-site-key');
define('RECAPTCHA_SECRET_KEY', 'your-secret-key');

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Security settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Feature flags
define('FEATURE_REGISTRATION', true);
define('FEATURE_EMAIL_VERIFICATION', true);
define('FEATURE_PASSWORD_RESET', true);
define('FEATURE_TWO_FACTOR_AUTH', false);
?>
```

### Language Settings
- Default language: `DEFAULT_LANGUAGE = 'de'`
- Available languages: `AVAILABLE_LANGUAGES = 'de,en,br,cn,vm,ru'`
- Language files: `lang/[language].php`

### Security Settings
```php
// Password requirements
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SPECIAL', true);

// Session security
define('SESSION_COOKIE_SECURE', true);
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Strict');
```

## 📁 Project Structure

```
ucp/
├── config/                 # Configuration files
│   ├── config.php         # Main config
│   ├── database_tables.php # Database structure
│   └── email_templates.php # Email templates
├── css/                   # Stylesheets
│   ├── main.css           # Main stylesheet
│   └── forms.css          # Form stylesheet
├── js/                    # JavaScript files
│   ├── main.js            # Main JavaScript
│   ├── form-validation.js # Form validation
│   └── password-strength.js # Password strength checker
├── php/                   # PHP backend
│   ├── classes/           # PHP classes
│   │   ├── UCP.php        # Main UCP class
│   │   ├── ReCaptcha.php  # reCAPTCHA handler
│   │   ├── Language.php   # Language manager
│   │   └── Session.php    # Session management
│   └── api/               # API endpoints
├── lang/                  # Language files
│   ├── de.php             # German
│   ├── en.php             # English
│   ├── br.php             # Portuguese
│   ├── cn.php             # Chinese
│   ├── vm.php             # Vietnamese
│   └── ru.php             # Russian
├── images/                # Images and assets
├── uploads/               # File uploads
├── logs/                  # Log files
├── cache/                 # Cache files
├── index.php              # Main page
├── register.php           # Registration
├── login.php              # Login
├── dashboard.php          # Dashboard
├── characters.php         # Character management
├── account.php            # Account management
└── README.md              # This file
```

## 🔐 Security Features

### Authentication
- **Argon2ID** password hashing
- **Session hijacking** protection
- **Brute-force** protection with rate limiting
- **Account lockout** after multiple failed attempts

### Validation
- **Server-side** validation of all inputs
- **Client-side** validation for better UX
- **reCAPTCHA v3** integration for bot protection
- **CSRF tokens** for all forms

### Data Privacy
- **GDPR-compliant** - Data protection guidelines
- **Secure cookies** - HttpOnly, Secure, SameSite
- **Data minimization** - Only necessary data is stored
- **Encrypted transmission** - SSL/TLS required

## 🌐 Multi-Language Support

### Available Languages
1. **German (DE)** - Fully translated
2. **English (EN)** - Fully translated
3. **Portuguese (BR)** - Fully translated
4. **Chinese (CN)** - Fully translated
5. **Vietnamese (VM)** - Fully translated
6. **Russian (RU)** - Fully translated

### Language Management
- **Automatic detection** of browser language
- **Manual selection** via dropdown menu
- **Session-based** language preference storage
- **URL parameter** for language change (`?lang=de`)

### Adding a New Language
1. Create new language file: `lang/[language].php`
2. Copy the structure from `lang/de.php`
3. Translate all texts
4. Add the language to configuration:
```php
define('AVAILABLE_LANGUAGES', 'de,en,br,cn,vm,ru,new');
```

## 📱 Responsive Design

### Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Features
- **Mobile-first** approach
- **Touch-optimized** for smartphones and tablets
- **Flexible grid** system
- **Scalable fonts**
- **Optimized navigation** for smaller screens

## 🔧 API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `POST /api/verify-email` - Email verification
- `POST /api/reset-password` - Password reset

### User Management
- `GET /api/profile` - Get user profile
- `PUT /api/profile` - Update user profile
- `GET /api/characters` - Get characters
- `POST /api/characters` - Create character
- `DELETE /api/characters/{id}` - Delete character

### Server Information
- `GET /api/server-status` - Server status
- `GET /api/online-players` - Online players
- `GET /api/server-info` - Server info

## 🚀 Performance Optimization

### Caching
- **File-based** caching for configs
- **Session caching** for user data
- **Template caching** for static content
- **Browser caching** for assets

### Compression
- **Gzip compression** for HTML, CSS, JS
- **Image optimization** for better loading times
- **Minification** of CSS and JavaScript
- **Lazy loading** for images

### Database
- **Prepared statements** for all queries
- **Indexing** of important fields
- **Query optimization** for better performance
- **Connection pooling** for efficient connections

## 📊 Logging and Monitoring

### Log Files
- **Logins** - Successful and failed logins
- **Registrations** - New user accounts
- **Security events** - Suspicious activities
- **Errors** - System and application errors

### Monitoring
- **Server status** - Automatic monitoring
- **Performance metrics** - Response times and load
- **Security alerts** - Suspicious activities
- **User activities** - Login statistics

## 🔄 Maintenance & Updates

### Automatic Updates
- **Security updates** - Regular security patches
- **Feature updates** - New features and improvements
- **Database migrations** - Automatic schema updates

### Backup Strategy
- **Daily backups** - Automated database backups
- **Rollback functions** - Quick restore
- **Monitoring** - Backup status monitoring

## 🛠️ Troubleshooting

### Common Issues

#### reCAPTCHA not working
1. Check site and secret keys
2. Make sure the domain is correctly set up
3. Check network connectivity to Google

#### Email sending failed
1. Verify SMTP settings
2. Test connection to mail server
3. Check firewall settings

#### Database connection error
1. Check database settings
2. Test database connection
3. Check user permissions

### Debug Mode
```php
// Enable in config.php
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);
```

## 🤝 Contributing

### Development
1. Fork the repository
2. Create a feature branch
3. Implement your changes
4. Write tests for new features
5. Create a pull request

### Translations
1. Copy an existing language file
2. Translate all texts
3. Test your translation
4. Submit a pull request

### Bug Reports
1. Describe the issue in detail
2. Add screenshots (if relevant)
3. Provide reproduction steps
4. Include system information

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## 🔗 Links

- **Demo**: [https://demo.yourserver.com](https://demo.yourserver.com)
- **Documentation**: [https://docs.yourserver.com](https://docs.yourserver.com)
- **Support**: [https://support.yourserver.com](https://support.yourserver.com)
- **GitHub**: [https://github.com/yourrepository/ucp](https://github.com/yourrepository/ucp)

## 👥 Credits

- **Development**: Your Dev Team
- **Design**: Your Design Team
- **Translations**: Community Contributions
- **Testing**: QA Team

---

**Made with ❤️ for the gaming community**

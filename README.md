# ResumeForge — Online Resume Builder

A full-featured resume builder built with PHP, HTML, CSS, JavaScript, and MySQL.

## Features
- 🔐 User registration & login (sessions)
- 📄 Create multiple resumes per account
- ✏️ Live preview editor (form → rendered resume in real time)
- 🎨 6 professional templates: Modern, Classic, Minimal, Executive, Creative, Compact
- 🌈 Custom theme color picker
- 📋 All resume sections: Personal Info, Experience, Education, Skills, Projects, Certifications, Languages
- 🔗 Public shareable resume URL
- 🖨️ Print to PDF from browser
- 📱 Responsive dashboard

---

## Requirements
- PHP 7.4+ with PDO & PDO_MySQL
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx web server
- A modern browser (Chrome, Firefox, Edge)

---

## Setup Instructions

### 1. Copy files to your web server
Place the `resume-builder/` folder inside your web root:
- XAMPP: `C:/xampp/htdocs/resume-builder/`
- LAMP: `/var/www/html/resume-builder/`

### 2. Create the database
Open phpMyAdmin or MySQL CLI and run:
```sql
SOURCE /path/to/resume-builder/database.sql;
```
Or paste the contents of `database.sql` into phpMyAdmin's SQL tab.

### 3. Configure database credentials
Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_user');      // change this
define('DB_PASS', 'your_mysql_password');  // change this
define('DB_NAME', 'resume_builder');
```

### 4. Create uploads directory
```bash
mkdir -p assets/uploads
chmod 755 assets/uploads
```

### 5. Open in browser
Visit: `http://localhost/resume-builder/`

---

## Demo Account
After running the SQL, a demo account is pre-created:
- **Email:** demo@example.com
- **Password:** demo1234

---

## File Structure
```
resume-builder/
├── index.php          # Landing page + auth modals
├── dashboard.php      # User dashboard (list resumes)
├── editor.php         # Resume editor with live preview
├── view.php           # Public resume viewer
├── database.sql       # Database schema + seed data
├── css/
│   ├── main.css       # Global styles, variables, components
│   ├── landing.css    # Landing page styles
│   ├── dashboard.css  # Dashboard styles
│   ├── editor.css     # Editor layout styles
│   └── templates.css  # All 6 resume template CSS
├── js/
│   ├── auth.js        # Login/register UI logic
│   ├── dashboard.js   # Dashboard (list, create, delete)
│   ├── editor.js      # Editor controller (sections, settings, share)
│   ├── preview.js     # Resume preview renderer (all 6 templates)
│   └── items.js       # Add/edit/delete item modals
├── php/
│   ├── config.php     # DB config, session, helpers
│   ├── auth.php       # Auth API (login, register, logout)
│   └── resume.php     # Resume CRUD API
└── assets/
    └── uploads/       # User photo uploads (future)
```

---

## API Endpoints

### Auth (`php/auth.php`)
| Action     | Method | Description           |
|------------|--------|-----------------------|
| `login`    | POST   | Login with email/pass |
| `register` | POST   | Create new account    |
| `logout`   | POST   | Destroy session       |
| `check`    | GET    | Check if logged in    |

### Resume (`php/resume.php`) — requires login
| Action            | Method | Description                        |
|-------------------|--------|------------------------------------|
| `list`            | GET    | List all user resumes              |
| `create`          | POST   | Create new resume                  |
| `get_full`        | GET    | Get resume with all sections       |
| `save_section`    | POST   | Save personal info section         |
| `get_section`     | GET    | Get items for a section            |
| `add_item`        | POST   | Add item to a section              |
| `update_item`     | POST   | Update an item                     |
| `delete_item`     | POST   | Delete an item                     |
| `update_settings` | POST   | Update template/color/title        |
| `toggle_public`   | POST   | Toggle public share link           |
| `delete`          | POST   | Delete a resume                    |

---

## Security Notes
- All user input is sanitized with `htmlspecialchars()` / `strip_tags()`
- Passwords are hashed with `password_hash(BCRYPT)`
- All DB queries use PDO prepared statements (SQL injection safe)
- All resume operations verify user ownership before modifying data
- Sessions are used for authentication

---

## Customization
- Add more color options in `editor.php` `.color-grid`
- Add new templates in `css/templates.css` and `js/preview.js`
- Extend sections by adding tables to `database.sql` and handlers to `php/resume.php`
# Resume-builder

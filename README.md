# ⛴ Bantayan Ferry Booking & Scheduling System

A complete mobile-friendly ferry booking system with Glass UI design, built with HTML/CSS/JS frontend and PHP/MySQL backend.

---

## 🗂 Project Structure

```
bantayan/
├── frontend/
│   ├── css/
│   │   └── main.css          ← Glass UI design system
│   ├── js/
│   │   └── utils.js          ← Shared utilities, API helpers
│   └── pages/
│       ├── index.html         ← Welcome / Landing page
│       ├── login.html         ← User + Admin login
│       ├── register.html      ← User registration
│       ├── dashboard.html     ← User dashboard + booking flow
│       └── admin.html         ← Full admin dashboard
├── backend/
│   ├── config.php             ← DB config + helpers
│   └── api/
│       ├── auth.php           ← Login / Register API
│       ├── bookings.php       ← Bookings CRUD API
│       ├── schedules.php      ← Schedules CRUD API
│       ├── shipping_lines.php ← Shipping lines API
│       ├── fares.php          ← Fares + cargo rates API
│       └── users.php          ← Users + admins API
└── sql/
    └── bantayan.sql           ← Full DB schema + seed data
```

---

## ⚙️ Setup Instructions

### 1. Database
```sql
-- In phpMyAdmin or MySQL CLI:
SOURCE /path/to/bantayan/sql/bantayan.sql;
```
This creates the database, all tables, and seeds:
- 3 shipping lines (Island Shipping, Super Shuttle Ferry, Aznar Shipping)
- Passenger fares for all types
- Cargo rates
- Default schedules
- Default admin: `admin` / `admin123`

### 2. Backend (PHP)
- Requires: PHP 7.4+ with MySQLi extension
- Place the `backend/` folder in your web server (Apache/XAMPP/Laragon)
- Default config: `localhost`, user `root`, no password
- Edit `backend/config.php` if your credentials differ:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'bantayan_ferry');
  ```

### 3. Frontend (Apache Cordova / Browser)
- For browser testing: Open `frontend/pages/index.html`
- For Cordova: Copy all files into `www/` of your Cordova project
- Update `API_BASE` in `frontend/js/utils.js` to match your backend URL:
  ```js
  const API_BASE = 'http://localhost/bantayan/backend/api';
  ```

---

## 🔑 Default Credentials

| Role  | Username/Email       | Password   |
|-------|----------------------|------------|
| Admin | admin                | admin123   |

Register new users from the Register page.

---

## ✨ Features

### User Side
- ✅ Register / Login
- ✅ Step-by-step ferry booking flow
- ✅ 3 shipping lines with separate fares
- ✅ Aznar Shipping Economy/Class selection
- ✅ Passenger type discounts (Student, Senior, PWD, Child)
- ✅ Optional cargo booking
- ✅ Auto-computed total price
- ✅ QR Code ticket generation (qrserver API fallback)
- ✅ PDF receipt download (html2pdf.js)
- ✅ View & cancel bookings

### Admin Side
- ✅ Dashboard with stats + revenue chart
- ✅ Manage all bookings (status, delete)
- ✅ Manage schedules (add, edit, delete)
- ✅ Manage fares & cargo rates (inline editing)
- ✅ Manage shipping lines
- ✅ Manage users (activate/deactivate, delete)
- ✅ Manage admin accounts

---

## 🎨 Design
- Glass morphism UI with ocean/ferry theme
- Syne (display) + DM Sans (body) fonts
- Animated background orbs
- Fully responsive mobile layout
- Sidebar collapses to hamburger on mobile
- Toast notifications
- Step-by-step booking wizard
- QR ticket card with die-cut effect

---

## 📦 External Libraries Used
- **qrcode.js** — QR code generation (via CDN)
- **html2pdf.js** — PDF receipt generation (via CDN)
- **Chart.js** — Revenue chart in admin (via CDN)
- **QR Server API** — Fallback QR if qrcode.js unavailable

All CDN-linked, no npm required for frontend.

# ShoeStore E-Commerce Platform

A full-featured e-commerce website with customer store and admin panel.

## Quick Start

### First Time Setup
1. Start XAMPP (Apache + MySQL)
2. Create database `shoes_ecom` in phpMyAdmin
3. Run `setup.bat`

### Start Application
Run `start-all.bat` - starts everything on port 5173!

---

## URLs

| App | URL |
|-----|-----|
| Customer Store | http://localhost:5173 |
| **Admin Panel** | http://localhost:5173/admin |
| Backend API | http://localhost:8000/api |

---

## Test Accounts

| Account | Email | Password |
|---------|-------|----------|
| **Admin** | admin@shoestore.com | password123 |
| Customer | test@test.com | password |

---

## Features

### Customer Store (/)
- Product catalog with categories
- Product search and filters
- Shopping cart
- Checkout
- Order history
- User profile

### Admin Panel (/admin)
- Dashboard with stats
- Product management (CRUD)
- Category management (CRUD)
- Order management
- User management

---

## Project Structure

```
shoes_ecom/
├── backend/              # Laravel API
│   ├── app/Http/Controllers/Api/
│   │   ├── Admin/        # Admin controllers
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── OrderController.php
│   ├── routes/
│   │   ├── api.php       # Public API routes
│   │   └── admin.php     # Admin API routes
│   └── database/
│       ├── migrations/   # Database schema
│       └── seeders/      # Sample data
│
├── frontend/             # Vue.js (port 5173) - includes admin panel
│   └── src/
│       ├── views/
│       │   ├── admin/    # Admin pages
│       │   └── *.vue     # Customer pages
│       ├── layouts/
│       │   └── AdminLayout.vue
│       ├── stores/       # Pinia state
│       └── router/       # Vue Router
│
├── start-all.bat         # Start all servers
└── setup.bat             # First-time setup
```

---

## How to Access Admin Panel

1. Start the app: `start-all.bat`
2. Go to: http://localhost:5173
3. Login with: admin@shoestore.com / password123
4. Click your profile name (top right)
5. Click "Admin Panel" in the dropdown
6. Or go directly to: http://localhost:5173/admin

---

## Batch Files

| File | Description |
|------|-------------|
| `setup.bat` | First-time setup (run once) |
| `start-all.bat` | Start all servers (backend + frontend) |
| `start-backend.bat` | Start Laravel API only |
| `start-frontend.bat` | Start Vue.js only (includes admin) |
| `reset-database.bat` | Reset and reseed database |
| `create-database.bat` | Create the MySQL database |

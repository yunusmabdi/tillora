# Tillora

> A modern, multi-warehouse Enterprise Resource Planning (ERP) and Point of Sale (POS) system built with Laravel and Filament.

![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.5-blue)
![Filament](https://img.shields.io/badge/Filament-v4-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## 📖 Overview

Tillora is a comprehensive inventory, sales, purchasing, and warehouse management system designed for retail and wholesale businesses. It provides businesses with an intuitive admin dashboard, powerful reporting tools, inventory tracking, and a modern Point of Sale (POS) interface.

The project follows Laravel best practices using a service-oriented architecture, Filament Admin Panel, and role-based access control to deliver a scalable and maintainable business management solution.

---

## ✨ Features

### 📊 Dashboard
- Business overview
- Revenue analytics
- Sales trends
- Low stock alerts
- Top selling products
- Recent sales

### 👥 User Management
- Authentication
- User Management
- Role & Permission Management
- Activity Tracking

### 📦 Inventory Management
- Product Management
- Categories
- Brands
- Suppliers
- Barcode Support
- SKU Generation
- Stock Levels
- Stock Movements
- Low Stock Notifications

### 🏬 Warehouse Management
- Multiple Warehouses
- Warehouse Stock Tracking
- Inventory Transfers *(Planned)*

### 🛒 Purchase Management
- Purchase Orders
- Purchase Items
- Supplier Management
- Goods Receiving
- Automatic Inventory Updates

### 💳 Sales & POS
- Modern POS Interface
- Product Search
- Shopping Cart
- Customer Selection
- Checkout
- Payment Processing
- Receipt Generation
- Sales History

### 👤 Customer Management
- Customer Profiles
- Purchase History
- Customer Reports

### 📈 Reports
- Sales Reports
- Purchase Reports
- Inventory Reports
- Customer Reports
- Revenue Analytics

---

# 🛠 Tech Stack

- Laravel 13
- PHP 8.5
- Filament v4
- Livewire
- Tailwind CSS
- MySQL
- Spatie Laravel Permission
- Vite

---

# 📂 Project Structure

```
app/
├── Livewire/
├── Models/
├── Services/
├── Policies/
├── Providers/
├── Filament/
│   ├── Resources/
│   ├── Widgets/
│   └── Pages/
├── Http/
└── ...
```

---

# 🚀 Installation

## 1. Clone the repository

```bash
git clone https://github.com/yunusmabdi/tillora.git

cd tillora
```

## 2. Install dependencies

```bash
composer install

npm install
```

## 3. Environment

```bash
cp .env.example .env
```

Update your database credentials.

## 4. Generate application key

```bash
php artisan key:generate
```

## 5. Run migrations

```bash
php artisan migrate
```

## 6. Seed the database (Optional)

```bash
php artisan db:seed
```

## 7. Link storage

```bash
php artisan storage:link
```

## 8. Start the application

```bash
php artisan serve

npm run dev
```

Visit:

```
http://127.0.0.1:8000
```

---

# 🔐 Default Roles

- Super Admin
- Admin
- Manager
- Cashier

Permissions are managed using **Spatie Laravel Permission**.

---

# 📦 Core Modules

| Module | Status |
|---------|--------|
| Authentication | ✅ |
| Roles & Permissions | ✅ |
| Categories | ✅ |
| Products | ✅ |
| Suppliers | ✅ |
| Purchases | ✅ |
| Inventory | ✅ |
| Stock Movements | ✅ |
| Customers | ✅ |
| Sales | ✅ |
| POS | ✅ |
| Dashboard | ✅ |
| Reports | 🚧 |
| Warehouse Transfers | 🚧 |
| Accounting | 📅 Planned |

---

# 📸 Screens

- Admin Dashboard
- Product Management
- Purchase Orders
- Inventory
- POS Interface
- Sales History

> Add screenshots inside a `/screenshots` folder and reference them here.

---

# 🏗 Architecture

Tillora follows a clean architecture approach:

- Service Layer Pattern
- Form Requests for Validation
- Policies for Authorization
- Filament Resources
- Livewire Components
- Repository-friendly Project Structure

---

# 🔮 Roadmap

- Multi-Warehouse Transfers
- Barcode Printing
- Invoice PDF Generation
- Receipt Printing
- Customer Loyalty Program
- Discounts & Coupons
- Expense Management
- Accounting Module
- REST API
- Mobile POS
- Offline POS Support
- Multi-branch Support

---

# 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a feature branch

```bash
git checkout -b feature/new-feature
```

3. Commit your changes

```bash
git commit -m "Add new feature"
```

4. Push

```bash
git push origin feature/new-feature
```

5. Open a Pull Request

---

# 📄 License

This project is licensed under the MIT License.

---

# 👨‍💻 Author

**Yunus Abdi**

- GitHub: https://github.com/yunusmabdi

---

## ⭐ Support

If you find this project useful, consider giving it a ⭐ on GitHub.

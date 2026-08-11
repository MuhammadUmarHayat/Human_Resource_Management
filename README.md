# HRMS — Human Resource Management System

A modern **Human Resource Management System (HRMS)** built with **Laravel, Vue.js, Inertia.js, and MySQL**.

This application provides a role-based platform for managing employees, departments, attendance, leave applications, and payroll through dedicated **Admin, HR, and Employee** portals.

---

## 🚀 Tech Stack

### Backend
- **PHP**
- **Laravel 12**
- **MySQL**
- **Spatie Laravel Permission**

### Frontend
- **Vue.js 3**
- **Inertia.js**
- **Bootstrap 5**
- **Bootstrap Icons**
- **Vite**

### Authentication & Authorization
- **Laravel Breeze**
- **Role-Based Access Control (RBAC)**
- **Spatie Laravel Permission**

---

## 👥 User Roles

The system supports three separate roles:

### 🔐 Admin
Full access to HRMS operations.

- Dashboard
- Employee Management
- Department Management
- Designation Management
- Attendance Management
- Leave Types
- Leave Applications
- Leave Approval / Rejection
- Payroll Management
- Payroll Processing
- Mark Payroll as Paid

### 👨‍💼 HR
HR-focused access to employee and organizational operations.

- HR Dashboard
- Employee Management
- Departments
- Designations
- Attendance
- Leave Applications
- Leave Approval / Rejection
- Payroll Management
- Payroll Processing
- Payment Management

### 👤 Employee
Employee self-service portal.

- Employee Dashboard
- Personal Profile
- Attendance
- Leave Applications
- Payroll Information

---

## ✨ Core Features

### 🔑 Authentication
- User registration
- Login / Logout
- Password authentication
- Email verification
- Role-based access protection

### 🛡️ Role-Based Access Control
Implemented using **Spatie Laravel Permission**.

Users are assigned one of:

```text
Admin
HR
Employee
```

Each role has its own protected routes, dashboard, and navigation.

### 👨‍💼 Employee Management
- Create employees
- View employees
- Edit employee information
- Delete employees
- Employee codes
- Departments & designations
- Contact information
- CNIC
- Joining date
- Salary
- Employment status

### 🏢 Organization Management
- Department CRUD
- Designation CRUD
- Employee-department relationships
- Employee-designation relationships

### 🕒 Attendance Management
- Daily attendance
- Attendance status
- Monthly attendance register
- Employee attendance records
- Attendance filtering

### 📝 Leave Management
- Leave type management
- Leave applications
- Leave duration calculation
- Leave status tracking
- Pending / Approved / Rejected workflow
- Leave approval
- Leave rejection
- Approval remarks

### 💰 Payroll Management
- Monthly payroll generation
- Employee salary information
- Basic salary
- Gross salary
- Net salary
- Payroll status
- Draft payroll
- Process payroll
- Mark payroll as Paid
- Payment date
- Payroll filtering and search
- Monthly payroll summary

### 📊 Dashboards
Separate dashboards for:

```text
Admin Dashboard
HR Dashboard
Employee Dashboard
```

Navigation and access are dynamically controlled according to the authenticated user's role.

---

## 🏗️ Application Architecture

The project follows a modern **Laravel + Inertia + Vue SPA architecture**.

```text
Browser
   │
   ▼
Vue.js 3
   │
   ▼
Inertia.js
   │
   ▼
Laravel 12
   │
   ├── Controllers
   ├── Models
   ├── Middleware
   ├── Form Requests
   └── Spatie Permissions
   │
   ▼
MySQL
```

Inertia allows the application to provide a Single Page Application experience without requiring a separate REST API for the frontend.

---

## 📁 Main Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Auth/
│   │   ├── AdminController.php
│   │   ├── HRController.php
│   │   └── EmployeeController.php
│   │
│   └── Middleware/
│
├── Models/
│   ├── User.php
│   ├── Employee.php
│   ├── Department.php
│   ├── Designation.php
│   ├── Attendance.php
│   ├── Leave.php
│   ├── LeaveType.php
│   └── Payroll.php
│
resources/
└── js/
    ├── Components/
    ├── Layouts/
    │   ├── AdminLayout.vue
    │   ├── HRLayout.vue
    │   └── EmployeeLayout.vue
    │
    └── Pages/
        ├── Admin/
        ├── HR/
        ├── Employee/
        └── Auth/

routes/
├── web.php
├── admin.php
├── hr.php
└── employee.php
```

---

## 🔐 Role-Based Route Protection

Each portal has its own protected route group.

Example:

```php
Route::middleware([
    'auth',
    'verified',
    'role:Admin',
])
->prefix('admin')
->name('admin.')
->group(function () {
    // Admin routes
});
```

HR:

```php
Route::middleware([
    'auth',
    'verified',
    'role:HR',
])
->prefix('hr')
->name('hr.')
->group(function () {
    // HR routes
});
```

Employee:

```php
Route::middleware([
    'auth',
    'verified',
    'role:Employee',
])
->prefix('employee')
->name('employee.')
->group(function () {
    // Employee routes
});
```

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git

cd YOUR_REPOSITORY
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Create Environment File

```bash
cp .env.example .env
```

On Windows:

```bash
copy .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Configure MySQL

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Build Frontend

Development:

```bash
npm run dev
```

Production:

```bash
npm run build
```

### 9. Start Laravel

```bash
php artisan serve
```

---

## 📦 Important Packages

### Laravel Breeze

Used for authentication and Vue/Inertia integration:

```bash
composer require laravel/breeze --dev
php artisan breeze:install vue
```

### Spatie Laravel Permission

Used for Role-Based Access Control:

```bash
composer require spatie/laravel-permission
```

Publish the package:

```bash
php artisan vendor:publish \
--provider="Spatie\Permission\PermissionServiceProvider"
```

---

## 🎯 Skills Demonstrated

This project demonstrates practical experience with:

- Laravel 12
- PHP
- Vue.js 3
- Inertia.js
- MySQL
- Laravel Breeze
- Spatie Laravel Permission
- Role-Based Access Control
- Authentication & Authorization
- MVC Architecture
- RESTful Resource Controllers
- Eloquent ORM
- Eloquent Relationships
- Database Migrations
- Form Validation
- CRUD Operations
- Middleware
- Route Groups
- Named Routes
- Pagination
- Search & Filtering
- Bootstrap 5
- Responsive UI
- Vite
- SPA Development
- Debugging Laravel applications
- Git & GitHub

---

## 🔄 HRMS Workflow

```text
User Registration
       │
       ▼
Authentication
       │
       ▼
Role Assignment
       │
 ┌─────┼─────────┐
 ▼     ▼         ▼
Admin   HR     Employee
 │      │         │
 ▼      ▼         ▼
HRMS   HR       Self
Mgmt   Operations Service
 │      │         │
 └──────┼─────────┘
        ▼
Employee
Attendance
Leave
Payroll
Management
```

---

## 🧪 Development Focus

The project was developed with a focus on:

- Clean Laravel architecture
- Reusable Vue components
- Role-specific layouts
- Secure route protection
- Database relationships
- Practical HR workflows
- Responsive Bootstrap UI
- Maintainable CRUD architecture

---

## 📌 Project Status

**Status: Completed**

The core HRMS modules are implemented and integrated:

- ✅ Authentication
- ✅ RBAC
- ✅ Admin Portal
- ✅ HR Portal
- ✅ Employee Portal
- ✅ Employee Management
- ✅ Departments
- ✅ Designations
- ✅ Attendance
- ✅ Leave Management
- ✅ Payroll
- ✅ Role-based Dashboards

---

## 👨‍💻 Developer

**Umar Hayat**

Full-Stack Developer

### Technical Interests

- Laravel & PHP
- Vue.js
- React.js
- Python / Flask
- ASP.NET Core
- MySQL
- REST APIs
- Full-Stack Web Development
- Software Engineering

---

## 📄 License

This project is developed for **learning, portfolio, and demonstration purposes**.

---

⭐ If you find this project useful, consider giving the repository a star.

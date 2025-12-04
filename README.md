# User Management System API

A robust RESTful API for user management built with Laravel 12, featuring JWT authentication and role-based access control (RBAC).

## 📋 Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Authorization](#authorization)
- [Validation Rules](#validation-rules)
- [Testing](#testing)
- [Project Structure](#project-structure)

## 🎯 Project Overview

This is a comprehensive User Management System API that provides secure user registration, authentication, and management capabilities. The system implements JWT-based authentication and supports role-based access control with two roles: Admin and User.

## 🛠 Tech Stack

- **Framework:** Laravel 12.x
- **Database:** MySQL
- **Authentication:** JWT (JSON Web Tokens) via `php-open-source-saver/jwt-auth`
- **PHP Version:** ^8.2

## ✨ Features

- ✅ User Registration with strict validation
- ✅ JWT-based Authentication (Login/Logout)
- ✅ Role-based Access Control (Admin & User roles)
- ✅ User Management (List, Update, Delete)
- ✅ Secure password hashing
- ✅ Comprehensive input validation
- ✅ RESTful API design

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

- PHP ^8.2
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- Laravel 12.x

## 🚀 Installation

### Step 1: Clone or Navigate to Project

```bash
cd omar-laravel
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Step 4: Configure Database

Edit the `.env` file with your database credentials:

```env
DB_CONNECTION=mysql 
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Generate JWT Secret

```bash
php artisan jwt:secret
```

This command will automatically add `JWT_SECRET` to your `.env` file.

### Step 6: Run Migrations

```bash
php artisan migrate
```

### Step 7: Seed Roles

```bash
php artisan db:seed --class=RoleSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```

## ⚙️ Configuration

### JWT Configuration

The JWT package configuration has been published to `config/jwt.php`. You can customize token expiration and other settings there.

### Authentication Guard

The API guard is configured to use JWT in `config/auth.php`:

```php
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

## 🗄️ Database Setup

### Database Schema

The system uses two main tables:

1. **roles** - Stores user roles (Admin, User)
2. **users** - Stores user information with foreign key to roles

### Running Migrations

```bash
php artisan migrate
```

### Seeding Roles

The `RoleSeeder` creates two default roles:
- **Admin** - Full access to all endpoints
- **User** - Limited access

Run the seeder:

```bash
php artisan db:seed --class=RoleSeeder
```

## 📡 API Endpoints

### Base URL

All API endpoints are prefixed with `/api`

### Authentication Endpoints

#### Register User

**Endpoint:** `POST /api/register`

**Description:** Register a new user account.

**Authentication:** Not required

**Request Body:**
```json
{
    "name": "Omar Alselek",
    "email": "omaralselek@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

**Success Response (201):**
```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "Omar Alselek",
            "email": "Omar@example.com",
            "role": {
                "id": 2,
                "name": "User"
            },
            "created_at": "2025-12-04T21:00:00.000000Z",
            "updated_at": "2025-12-04T21:00:00.000000Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer"
    }
}
```

**Error Response (422):**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 8 characters and contain at least one uppercase letter and one number."]
    }
}
```

#### Login User

**Endpoint:** `POST /api/login`

**Description:** Authenticate user and receive JWT token.

**Authentication:** Not required

**Request Body:**
```json
{
    "email": "omaralselek@example.com",
    "password": "Password123"
}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Omar Alselek",
            "email": "omaralselek@example.com",
            "role": {
                "id": 2,
                "name": "User"
            }
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer"
    }
}
```

**Error Response (401):**
```json
{
    "status": "error",
    "message": "Invalid email or password"
}
```

#### Logout User

**Endpoint:** `POST /api/logout`

**Description:** Invalidate the current JWT token.

**Authentication:** Required (Bearer Token)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

### User Management Endpoints

#### List All Users

**Endpoint:** `GET /api/users`

**Description:** Retrieve a list of all users.

**Authentication:** Required (Bearer Token)

**Authorization:** Admin only

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Users retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Omar Alselek",
            "email": "omaralselek@example.com",
            "role": {
                "id": 2,
                "name": "User"
            }
        },
        {
            "id": 2,
            "name": "Admin User",
            "email": "admin@example.com",
            "role": {
                "id": 1,
                "name": "Admin"
            }
        }
    ]
}
```

**Error Response (403):**
```json
{
    "status": "error",
    "message": "Unauthorized. Admin access required."
}
```

#### Update User

**Endpoint:** `PUT /api/users/{id}`

**Description:** Update user information. Users can update their own profile, or Admins can update any user.

**Authentication:** Required (Bearer Token)

**Authorization:** User updating own profile OR Admin

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body (all fields optional):**
```json
{
    "name": "Omar Name",
    "email": "Omar@example.com",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "Omar Name",
        "email": "Omar@example.com",
        "role": {
            "id": 2,
            "name": "User"
        }
    }
}
```

**Error Response (403):**
```json
{
    "status": "error",
    "message": "Unauthorized. You can only update your own profile."
}
```

#### Delete User

**Endpoint:** `DELETE /api/users/{id}`

**Description:** Delete a user account. Users can delete their own account, or Admins can delete any user. Cannot delete the last Admin account.

**Authentication:** Required (Bearer Token)

**Authorization:** User deleting own account OR Admin

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "User deleted successfully"
}
```

**Error Response (403):**
```json
{
    "status": "error",
    "message": "Cannot delete the last admin account."
}
```

## 🔐 Authentication

### How to Obtain JWT Token

1. **Register** a new user via `POST /api/register`, or
2. **Login** with existing credentials via `POST /api/login`

Both endpoints return a JWT token in the response.

### Using the Bearer Token

Include the token in the `Authorization` header for protected endpoints:

```
Authorization: Bearer {your_jwt_token_here}
```

### Token Expiration

By default, JWT tokens expire after 60 minutes. You can configure this in `config/jwt.php`.

## 🔒 Authorization

### Role System

The system implements two roles:

- **Admin** - Full access to all endpoints
- **User** - Default role for new registrations, limited access

### Authorization Rules

- **List Users:** Admin only (enforced via Gate)
- **Update User:** User can update own profile OR Admin can update any user
- **Delete User:** User can delete own account OR Admin can delete any user
- **Delete Last Admin:** Prevented by business logic

### Authorization Implementation

The system uses Laravel Gates for authorization checks:

```php
Gate::define('view-users', function ($user) {
    return $user->role && $user->role->name === 'Admin';
});
```

## ✅ Validation Rules

### Registration

- **name:** required, string, max 255 characters
- **email:** required, valid email format, unique in database
- **password:** required, minimum 8 characters, must contain:
  - At least one uppercase letter
  - At least one number
- **password_confirmation:** required, must match password

### Login

- **email:** required, valid email format
- **password:** required

### Update User

All fields are optional (use `sometimes` rule):

- **name:** string, max 255 characters (if provided)
- **email:** valid email format, unique except current user (if provided)
- **password:** minimum 8 characters with complexity rules (if provided)
- **password_confirmation:** required if password is provided, must match

## 🧪 Testing

### Using cURL

#### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Omar Alselek",
    "email": "omaralselek@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'
```

#### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "omaralselek@example.com",
    "password": "Password123"
  }'
```

#### List Users (Admin only)

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

#### Update User

```bash
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'
```

#### Delete User

```bash
curl -X DELETE http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### Using Postman

1. Import the API endpoints into Postman
2. Set up environment variables for `base_url` and `token`
3. Use the "Authorization" tab to set Bearer token for protected endpoints

## 📁 Project Structure

```
omar-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   │       ├── AuthController.php      # Authentication logic
│   │   │       └── UserController.php      # User management logic
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php         # Admin-only middleware
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php         # Registration validation
│   │   │   ├── LoginRequest.php            # Login validation
│   │   │   └── UpdateUserRequest.php       # Update user validation
│   │   └── Resources/
│   │       └── UserResource.php            # User API resource
│   ├── Models/
│   │   ├── User.php                        # User model with JWT
│   │   └── Role.php                        # Role model
│   └── Providers/
│       └── AppServiceProvider.php          # Gates definition
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2025_12_04_211340_create_roles_table.php
│   │   └── 2025_12_04_211342_add_role_id_to_users_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RoleSeeder.php                  # Roles seeder
├── routes/
│   └── api.php                             # API routes
├── config/
│   ├── auth.php                            # Authentication config
│   └── jwt.php                             # JWT config
└── README.md
```

## 🔧 Troubleshooting

### JWT Secret Not Generated

If you encounter JWT-related errors, ensure you've run:

```bash
php artisan jwt:secret
```

### Roles Not Found

Make sure you've run the RoleSeeder:

```bash
php artisan db:seed --class=RoleSeeder
```

### Database Connection Issues

Verify your `.env` file has correct database credentials and the database exists.

### 401 Unauthorized Errors

- Ensure you're including the Bearer token in the Authorization header
- Check that the token hasn't expired
- Verify the token format: `Bearer {token}` (with space after Bearer)

## 📝 Notes

- New users are automatically assigned the "User" role upon registration
- Password complexity requires at least one uppercase letter and one number
- The last Admin account cannot be deleted
- All timestamps are in UTC format

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Development

Built with  using Laravel 12

---


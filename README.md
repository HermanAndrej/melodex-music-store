# Melodex Music Store

![Melodex Logo](https://via.placeholder.com/150x50?text=Melodex+Logo)

A full-featured online music instrument store built with modern web technologies. This single-page application (SPA) provides a seamless shopping experience for music enthusiasts.

## ✨ Features

- **User Authentication**
  - User registration and login with JWT
  - Role-based access control (Admin/User)
  - Secure password hashing

- **Product Management**
  - Browse products by categories
  - Product search and filtering
  - Product ratings and reviews

- **Shopping Experience**
  - Shopping cart functionality
  - Order processing
  - Order history

- **Admin Dashboard**
  - Manage products and categories
  - View and manage orders
  - User management

## 🛠 Technology Stack

### Frontend
- HTML5, CSS3, JavaScript (ES6+)
- Bootstrap 5 for responsive design
- Vanilla JavaScript for SPA functionality
- Fetch API for AJAX requests

### Backend
- PHP 8.1+
- FlightPHP framework
- MySQL 8.0+
- JWT for authentication

### Development Tools
- Git for version control
- Composer for PHP dependencies
- PHPUnit for testing

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone [repository-url]
   cd webapp
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up the database**
   - Create a new MySQL database
   - Import the database schema from `melodex_db.sql`

4. **Configure environment variables**
   - Copy `.env-example` to `.env`
   - Update database credentials and JWT secret

5. **Configure your web server**
   - Point your web server to the `public` directory
   - Ensure mod_rewrite is enabled (for Apache)
   - Set up proper permissions for the `storage` directory

6. **Access the application**
   - Open your browser and navigate to the configured URL

## 📁 Project Structure

```
webapp/
├── backend/
│   ├── config/         # Configuration files
│   ├── controllers/    # Request handlers
│   ├── dao/            # Data Access Objects
│   ├── middleware/     # Authentication and validation
│   ├── services/       # Business logic
│   └── routes/         # API route definitions
├── frontend/
│   ├── assets/        # Images, fonts, etc.
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript modules
│   │   ├── controllers/
│   │   ├── models/
│   │   └── services/
│   └── views/         # HTML templates
├── docs/              # API documentation
└── public/            # Publicly accessible files
```

## 📚 API Documentation

API documentation is available using OpenAPI (Swagger). After setting up the project, you can access it at:

```
http://your-domain.com/docs
```

Or view the OpenAPI specification file at:
[`/backend/docs/openapi.yaml`](/backend/docs/openapi.yaml)

## 🔒 Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header for protected routes:

```
Authorization: Bearer your.jwt.token.here
```

## 📱 Mobile Responsive

The application is fully responsive and works on all device sizes, from mobile phones to desktop computers.

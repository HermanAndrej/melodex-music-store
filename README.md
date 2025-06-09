# Melodex Music Store

Melodex is a full-stack e-commerce platform for musical instruments and equipment. Built with modern web technologies, it provides a seamless shopping experience for music enthusiasts.

## Features

- 🛍️ **Product Catalog**
  - Browse musical instruments and equipment
  - Filter products by categories
  - Search functionality
  - Detailed product views

- 👤 **User Management**
  - User registration and authentication
  - User profiles
  - Order history
  - Admin panel for user management

- 🛒 **Shopping Cart**
  - Add/remove items
  - Update quantities
  - Real-time price calculations
  - Secure checkout process

- 📦 **Order Management**
  - Order placement
  - Order tracking
  - Order history
  - Admin order management

- 👨‍💼 **Admin Panel**
  - Product management (CRUD operations)
  - Order management
  - User management
  - Category management

## Tech Stack

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap 5 for responsive design
- Font Awesome for icons
- Custom CSS for styling

### Backend
- PHP 8.x
- MySQL Database
- RESTful API architecture
- JWT Authentication

## Prerequisites

- PHP 8.x or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (PHP package manager)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/melodex-music-store.git
   cd melodex-music-store
   ```

2. Set up the database:
   - Create a new MySQL database
   - Import the database schema from `backend/melodexdb.sql`

3. Configure the backend:
   - Copy `backend/config.example.php` to `backend/config.php`
   - Update the database credentials in `config.php`
   - Set your JWT secret key

4. Start the PHP development server:
   ```bash
   cd backend
   php -S localhost:8000
   ```

5. Open the frontend:
   - Use a web server to serve the `frontend` directory
   - Or open `frontend/views/index.html` directly in your browser

## Project Structure

```
melodex-music-store/
├── backend/
│   ├── api/           # API endpoints
│   ├── config/        # Configuration files
│   ├── dao/          # Data Access Objects
│   ├── models/       # Data models
│   ├── routes/       # Route definitions
│   ├── services/     # Business logic
│   └── utils/        # Utility functions
├── frontend/
│   ├── assets/       # Static assets
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── views/       # HTML pages
└── docs/            # Documentation
```

## API Documentation

The API documentation is available at `/api/docs` when running the backend server. It provides detailed information about all available endpoints, request/response formats, and authentication requirements.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [Bootstrap](https://getbootstrap.com/) for the frontend framework
- [Font Awesome](https://fontawesome.com/) for the icons
- [TemplateMo](https://templatemo.com/) for the initial template design

## Contact

Andrej Herman - [@HermanAndrej](https://github.com/HermanAndrej)

Project Link: [https://github.com/HermanAndrej/melodex-music-store](https://github.com/HermanAndrej/melodex-music-store) 
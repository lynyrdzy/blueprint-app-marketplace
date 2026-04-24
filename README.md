# Single-Vendor E-Commerce Application

A production-ready Laravel application for selling products directly to consumers using Cash on Delivery (COD) payment method.

## Features

- **User Authentication**: Register, login, and role-based access (admin/customer)
- **Product Management**: Admin can create, update, and manage product inventory
- **COD Checkout**: Simple checkout without payment gateway integration
- **Order Management**: Track order status from pending to completed
- **Real-Time Chat**: Message system between customers and admin for each order
- **Stock Management**: Automatic stock decrement on order, restore on cancellation
- **Notifications**: Admin notified of new orders, customers notified of status changes

## Tech Stack

- **Backend**: Laravel (latest stable)
- **Database**: MySQL
- **Frontend**: Blade + Tailwind CSS
- **API**: RESTful with JSON responses

## Installation

```bash
# Clone the repository
git clone <repo-url>
cd blueprint-app-marketplace

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create database and run migrations
php artisan migrate

# Seed test data (optional)
php artisan db:seed

# Start the development server
php artisan serve
```

## Project Structure

```
app/
├── Models/                 # Eloquent models
├── Http/
│   ├── Controllers/        # API & web controllers
│   ├── Requests/           # Form request validation
│   └── Resources/          # API response resources
├── Services/               # Business logic
├── Events/                 # Application events
├── Listeners/              # Event listeners
├── Policies/               # Authorization policies
└── Exceptions/             # Custom exceptions

database/
├── migrations/             # Database schema
└── seeders/                # Test data

resources/views/            # Blade templates
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user

### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product details
- `POST /api/admin/products` - Create product (admin)
- `PATCH /api/admin/products/{id}` - Update product (admin)
- `DELETE /api/admin/products/{id}` - Delete product (admin)

### Orders
- `POST /api/orders` - Create new order
- `GET /api/orders` - Get customer's orders
- `GET /api/orders/{id}` - Get order details
- `PATCH /api/orders/{id}/status` - Update order status (admin)
- `GET /api/admin/orders` - List all orders (admin)

### Chat
- `GET /api/orders/{order_id}/messages` - Get chat messages
- `POST /api/orders/{order_id}/messages` - Send message

## Database Schema

Key tables:
- `users` - User accounts
- `products` - Product catalog
- `orders` - Customer orders
- `order_items` - Order line items
- `messages` - Chat messages
- `notifications` - In-app notifications

## Security

- All inputs validated via Form Requests
- SQL injection prevention via Eloquent ORM
- CSRF protection on all state-changing requests
- Authorization policies for order access
- Password hashing with bcrypt

## Scalability

### Phase 2: Real-Time Chat
Upgrade from polling to WebSocket using Pusher

### Phase 3: Mobile App
Build Flutter app using the same API

### Phase 4: Multi-Vendor
Add vendor system with commission calculations

### Phase 5: Microservices
Split into separate services with message queues

## Development

```bash
# Run tests
php artisan test

# Run artisan commands
php artisan tinker

# View logs
tail -f storage/logs/laravel.log
```

## License

Private

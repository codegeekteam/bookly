# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based booking system ("Bookly") with a mobile API and admin panel built with Filament. The system manages service providers, customers, appointments, subscriptions, loyalty programs, and gift cards.

## Development Commands

### Local Development
```bash
# Start development server (Vite for assets)
npm run dev

# Build frontend assets
npm run build

# Run PHP development server
php artisan serve

# Run database migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear all caches
php artisan optimize:clear

# Generate API documentation (Scribe)
php artisan scribe:generate
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run Pint with verbose output
./vendor/bin/pint -v
```

## Architecture Overview

### Core Domain Models
- **Customer**: End users who book services
- **ServiceProvider**: Businesses offering services (salons, spas, etc.)
- **Service**: Individual services offered by providers
- **Appointment**: Bookings made by customers
- **Employee**: Staff members at service providers
- **Subscription/Plan**: Subscription packages for customers

### Key Features
1. **Multi-provider marketplace** with categories and regions
2. **Appointment booking system** with status tracking
3. **Loyalty points system** with discounts and rewards
4. **Gift card system** with themes and redemption
5. **Subscription plans** with recurring benefits
6. **Wallet system** for cashouts and transactions
7. **Push notifications** via Firebase
8. **Payment integration** with Payfort helper

### Directory Structure
- `app/Actions/`: Business logic mutations and queries
- `app/Filament/`: Admin panel resources and pages
- `app/Http/Controllers/Api/`: Mobile API endpoints
- `app/Models/`: Eloquent models
- `app/Services/`: Service layer classes
- `app/StateMachines/`: State machine implementations
- `database/migrations/`: Database schema definitions
- `routes/api.php`: API route definitions

### API Structure
The API uses Laravel Sanctum for authentication with OTP-based login. Main API groups:
- Auth endpoints (login, OTP verification)
- Customer management (addresses, profiles, loyalty points)
- Service browsing (categories, providers, services)
- Booking flow (appointments, cart, payments)
- Support features (FAQs, notifications, reviews)

### Admin Panel
Built with Filament v3, accessible at `/admin`. Features:
- Resource management for all entities
- Financial details dashboard
- Customer campaign management
- Support ticket system
- Rewards configuration

### Database
MySQL database with migrations. Key tables include:
- customers, service_providers, services
- appointments, appointment_items
- loyalty_points, loyalty_discounts, point_transactions
- subscriptions, plans, plan_items
- gift_cards, gift_card_themes
- wallet_transactions, cashout_requests

### Frontend Assets
Uses Vite for asset compilation with Tailwind CSS. Configuration in `vite.config.js` and `tailwind.config.js`.

### Dependencies
- Laravel 10.x with PHP 8.2+
- Filament 3.0.63 for admin panel
- Livewire 3.0 for reactive components
- Spatie packages for media library and translations
- Firebase integration via larafirebase
- don't apply pint
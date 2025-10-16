# Laravel 12 Team Starter Kit

A modern Laravel 12 starter kit with team and billing support, built with Inertia.js, React, and Tailwind CSS.

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/itsrafsanjani/laravel-team-starter-kit.git your-project-name
cd your-project-name
```

### 2. Remove Existing Git and Re-initialize

```bash
rm -rf .git
git init
git add .
git commit -m "Initial commit"
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies with pnpm
pnpm install
```

### 4. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database (SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 5. Development

```bash
# Start development server (runs Laravel server, queue worker, logs, and Vite)
composer run dev

# Or run individual commands:
# php artisan serve
# pnpm run dev
```

### 6. Building for Production

```bash
# Build assets
pnpm run build

# Or build with SSR support
pnpm run build:ssr
```

## Available Scripts

### PHP/Composer

- `composer run dev` - Start full development environment
- `composer run dev:ssr` - Start development with SSR
- `composer run test` - Run tests
- `php artisan serve` - Start Laravel server only

### Node.js/pnpm

- `pnpm run dev` - Start Vite development server
- `pnpm run build` - Build for production
- `pnpm run build:ssr` - Build with SSR support
- `pnpm run format` - Format code with Prettier
- `pnpm run lint` - Run ESLint

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: React 19, Inertia.js v2, TypeScript
- **Styling**: Tailwind CSS v4, Shadcn
- **Package Manager**: pnpm
- **Testing**: Pest v4
- **Authentication**: Laravel Fortify
- **Payments**: Laravel Cashier (Stripe)
- **Teams**: Laravel's built-in team features

## Features

- ✅ User authentication and registration
- ✅ Team management with invitations
- ✅ Role-based permissions
- ✅ Subscription billing with Stripe
- ✅ Two-factor authentication
- ✅ Modern React components with Radix UI
- ✅ Responsive design with Tailwind CSS
- ✅ TypeScript support
- ✅ ESLint and Prettier configuration
- ✅ Comprehensive testing with Pest

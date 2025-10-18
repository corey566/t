# Ultimate POS - Point of Sale System

## Overview

Ultimate POS is a comprehensive point-of-sale (POS) and business management system built on the Laravel PHP framework. The system provides multi-tenant capabilities, modular architecture, and extensive features for retail operations including inventory management, sales processing, customer relationship management, accounting, and various industry-specific modules.

The application serves as an enterprise-grade solution for businesses requiring sophisticated POS functionality with support for multiple locations, payment gateways, e-commerce integration, and advanced reporting capabilities.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Backend Architecture

**Framework & Core**
- Laravel 9.x framework as the foundation
- PHP 8.0+ requirement
- Modular architecture using nwidart/laravel-modules for extensibility
- Laravel Passport for API authentication and OAuth2 implementation
- Spatie packages for permissions (spatie/laravel-permission) and activity logging (spatie/laravel-activitylog)

**Design Pattern**
- Modular monolith: Core POS functionality in main application with business-specific features separated into modules (Accounting, CRM, Manufacturing, Repair, etc.)
- Each module is self-contained with its own routes, controllers, views, and assets
- Module activation controlled via modules_statuses.json

**Key Architectural Decisions**
- Separation of concerns through Laravel's MVC pattern
- Module-based extensibility allowing features to be enabled/disabled independently
- Multi-tenancy support through Superadmin module for SaaS deployments
- Real-time capabilities using Pusher for notifications and live updates

### Frontend Architecture

**UI Framework**
- Traditional server-side rendered Blade templates as primary view layer
- AdminLTE-based dashboard with custom theming
- Modern UI updates with Aceternity-inspired design system featuring:
  - Tailwind CSS 4.x with custom ambient color palette and glass-morphism effects
  - Aurora gradient backgrounds and smooth animations
  - Responsive design with mobile-first approach
- Vue.js 2.7 for reactive components (Passport clients, personal access tokens)
- jQuery for legacy interactions and AJAX operations

**Asset Management**
- Vite for modern module bundling (newer modules like AiAssistance, Gallface)
- Laravel Mix for legacy module asset compilation
- Separate build directories per module to avoid conflicts

**Design System**
- Custom Tailwind configuration with `tw-` prefix to avoid conflicts with existing styles
- Blue ambient color palette: blue → sky → indigo gradients (October 2025 update)
- Glass-morphism cards with backdrop blur effects
- Smooth hover animations and transitions
- Aceternity-inspired modern UI with minimalist design

### Data Storage

**Primary Database**
- Relational database (MySQL/PostgreSQL) for core business data
- Laravel Eloquent ORM for data access layer
- Database migrations for schema versioning

**Key Data Models**
- Multi-tenant structure with business locations
- Product catalog with variations and stock management
- Transaction processing (sales, purchases, returns, adjustments)
- Contact management (customers, suppliers)
- Module-specific tables (e.g., Gallface integration creates location_api_credentials, integra_transactions, colombo_city_transactions)

**File Storage**
- Laravel Flysystem with multiple driver support:
  - AWS S3 (league/flysystem-aws-s3-v3)
  - Dropbox (spatie/flysystem-dropbox)
  - Local storage with configurable paths

### Authentication & Authorization

**Authentication**
- Laravel's built-in authentication system
- Laravel Passport for API token management and OAuth2 flows
- Support for personal access tokens and client credentials

**Authorization**
- Role-based access control (RBAC) via Spatie Permission package
- Per-module permission management
- Multi-level user access (Superadmin, Business Owner, Employee roles)

### API Architecture

**Internal APIs**
- Connector module provides RESTful APIs for POS terminal integration
- AJAX endpoints for real-time data updates
- JSON responses for asynchronous operations

**API Design**
- RESTful conventions for resource management
- Token-based authentication for API access
- Rate limiting and request validation

### Module System

**Core Modules**
- **Accounting**: Financial management and reporting
- **CRM**: Customer relationship management
- **Manufacturing**: Production and bill of materials
- **Repair**: Service and repair shop management
- **Essentials**: HR and business essentials
- **Project**: Project management functionality
- **AssetManagement**: Tracking business assets
- **Ecommerce**: Online store integration

**Integration Modules**
- **Woocommerce**: WooCommerce store synchronization
- **Connector**: API for external POS systems
- **Gallface/HCM**: Mall integration for invoice sync with external systems

**Platform Modules**
- **Superadmin**: Multi-tenant SaaS management
- **Cms**: Content management for frontend pages
- **AiAssistance**: OpenAI integration for copywriting and reports

## External Dependencies

### Payment Gateways
- **Stripe** (stripe/stripe-php): Credit card processing
- **PayPal** (srmklive/paypal): PayPal checkout integration
- **Razorpay** (razorpay/razorpay): Indian payment gateway
- **Paystack** (unicodeveloper/laravel-paystack): African payment processing
- **MyFatoorah** (myfatoorah/laravel-package): Middle East payment gateway
- **Pesapal** (knox/pesapal): East African mobile money

### Communication Services
- **Twilio** (aloha/twilio): SMS notifications and verification
- **Pusher** (pusher/pusher-php-server): Real-time WebSocket events

### Third-Party Integrations
- **WooCommerce API** (automattic/woocommerce): E-commerce platform sync
- **OpenAI API** (openai-php/laravel): AI-powered content generation
- **Mall Integration APIs**: 
  - Havelock City Mall (HCM) integration
  - Integra (Colombo City Center) API
  - External token-based authentication for invoice validation

### Utility Libraries
- **GuzzleHTTP** (guzzlehttp/guzzle): HTTP client for API requests
- **Maatwebsite Excel** (maatwebsite/excel): Excel import/export
- **DomPDF** (barryvdh/laravel-dompdf): PDF generation
- **mPDF** (mpdf/mpdf): Advanced PDF rendering
- **Milon Barcode** (milon/barcode): Barcode generation
- **Chart.js** (consoletvs/charts): Data visualization
- **Yajra DataTables** (yajra/laravel-datatables-oracle): Server-side table processing
- **libphonenumber** (giggsey/libphonenumber-for-php): Phone number validation

### Development Tools
- **Tailwind CSS** 4.x: Utility-first CSS framework
- **PostCSS & Autoprefixer**: CSS processing and vendor prefixes
- **Vite**: Modern build tool for newer modules
- **Laravel Mix**: Asset compilation for legacy modules
- **Laravel Debugbar**: Development debugging (implied)

### Backup & Logging
- **Spatie Backup** (spatie/laravel-backup): Automated database and file backups
- **Log Viewer** (arcanedev/log-viewer): Web-based log file viewer
- **Activity Log** (spatie/laravel-activitylog): User action auditing

### Notable Architectural Constraints
- Database agnostic design (supports MySQL, PostgreSQL via Laravel)
- Module independence: Each module can function without others
- Legacy support: Maintains backward compatibility with older JavaScript patterns while modernizing UI layer
- Multi-currency and multi-language support built into core
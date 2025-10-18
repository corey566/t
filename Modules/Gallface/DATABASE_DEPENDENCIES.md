
# Gallface Module - Database Dependencies

## Overview
This document outlines the database structure and dependencies for the Gallface module.

## New Tables Created by Gallface Module

The following tables are **created by the Gallface module** and don't affect existing tables:

### 1. location_api_credentials
Stores API credentials for different mall integrations (Gallface, HCM, Integra, etc.)
- Primary Key: `id`
- Foreign Keys: References `business_id` and `business_location_id`

### 2. integra_api_logs
Logs all API requests/responses for Integra (Colombo City Center)
- Primary Key: `id`

### 3. integra_transactions
Stores transaction data received from Integra API
- Primary Key: `id`
- Unique: `receipt_num`

### 4. integra_items
Stores item details for Integra transactions
- Primary Key: `id`
- Foreign Key: `transaction_id` → `integra_transactions.id`

### 5. integra_payments
Stores payment details for Integra transactions
- Primary Key: `id`
- Foreign Key: `transaction_id` → `integra_transactions.id`

### 6. colombo_city_transactions
Stores transaction data for Colombo City API
- Primary Key: `id`
- Unique: `receipt_num`

### 7. colombo_city_items
Stores item details for Colombo City transactions
- Primary Key: `id`

### 8. colombo_city_payments
Stores payment details for Colombo City transactions
- Primary Key: `id`

## Modified Existing Tables

### transactions (columns added)
- `gallface_synced_at` (timestamp, nullable) - Tracks when synced to Gallface
- `hcm_synced_at` (timestamp, nullable) - Tracks when synced to HCM
- `gift_voucher_amount` (decimal, default 0) - Gift voucher amount
- `hcm_loyalty_amount` (decimal, default 0) - HCM loyalty points amount
- `is_gift_voucher` (boolean, default 0) - Flags gift voucher transactions

## External Table Dependencies

The Gallface module **reads from** these existing tables (does NOT modify them):

### Core Tables
1. **business** - Business information
   - Used in: `GallfaceController.php`
   - Purpose: Get business settings and configuration

2. **business_locations** - Store/outlet locations
   - Used in: All controllers
   - Purpose: Location-specific API credentials and sync operations

3. **transactions** - Sales transactions
   - Used in: All sync services
   - Purpose: Main sales data for syncing to malls

4. **transaction_sell_lines** - Transaction line items
   - Used in: `GallfaceApiService.php`, `HcmApiService.php`
   - Purpose: Product details for each transaction

5. **transaction_payments** - Payment details
   - Used in: `GallfaceApiService.php`, `HcmApiService.php`
   - Purpose: Payment method information

6. **contacts** - Customer information
   - Used in: `GallfaceController.php`
   - Purpose: Customer details for invoices

7. **users** - User accounts
   - Used in: Authentication middleware
   - Purpose: User permissions and authentication

## Migration Safety

All migrations include:
- Table existence checks (`Schema::hasTable()`)
- Column existence checks (`Schema::hasColumn()`)
- Safe rollback procedures
- No destructive operations on existing tables

## Installation Order

1. Run existing application migrations first
2. Run Gallface module migrations second
3. Migrations are idempotent - safe to run multiple times

## Data Flow

```
External Mall APIs (Gallface, HCM, Integra)
    ↓
location_api_credentials (stores credentials)
    ↓
Sync Services (GallfaceApiService, HcmApiService)
    ↓
Read from: transactions, transaction_sell_lines, transaction_payments
    ↓
Write to: Mall-specific tables (integra_*, colombo_city_*)
    ↓
Update: transactions.{mall}_synced_at timestamp
```

## No Conflicts

The module is designed to:
- **Never delete** existing tables
- **Never modify** existing table structures (only adds columns)
- **Only read** from core application tables
- **Isolate** all mall-specific data in separate tables
- **Track sync status** via timestamp columns in transactions table

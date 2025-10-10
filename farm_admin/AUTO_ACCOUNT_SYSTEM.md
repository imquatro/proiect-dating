# Auto Account System Documentation

## Overview
The Auto Account System distinguishes between automatically created accounts (from admin panel) and normal user registrations. This allows for better management and targeted operations on specific account types.

## Database Schema

### New Field: `auto_account`
- **Type**: TINYINT(1) NOT NULL DEFAULT 0
- **Values**: 
  - `1` = Auto-created account (from admin panel)
  - `0` = Normal account (user registration)
- **Index**: `idx_auto_account` for performance

## Implementation

### 1. Database Setup
```sql
-- Add the field
ALTER TABLE users ADD COLUMN auto_account TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '1 = cont creat din admin panel, 0 = cont creat prin înregistrare normală';

-- Add index for performance
CREATE INDEX idx_auto_account ON users(auto_account);
```

### 2. Account Creation

#### Auto-Created Accounts (Admin Panel)
- **File**: `farm_admin/create_users_auto.php`
- **Flag**: `auto_account = 1`
- **Used for**: Bulk account creation for testing

#### Manual-Created Accounts (Admin Panel)
- **File**: `farm_admin/create_user_manual.php`
- **Flag**: `auto_account = 0`
- **Used for**: Individual account creation by admin

#### Normal User Registration
- **File**: `register.php`
- **Flag**: `auto_account = 0`
- **Used for**: Regular user signup

### 3. Password Management

#### Auto Account Password Updates
- **File**: `farm_admin/update_all_passwords.php`
- **Target**: Only accounts with `auto_account = 1`
- **Query**: `UPDATE users SET password = ? WHERE auto_account = 1`

### 4. Admin Panel Interface

#### Auto Create Tab
- Creates accounts with `auto_account = 1`
- Shows current password indicator
- Password visibility toggle

#### Manual Create Tab
- Creates accounts with `auto_account = 0`
- Individual account creation

#### Update Passwords Tab
- Updates passwords for auto-created accounts only
- Clear indication that only auto accounts are affected

## Migration Scripts

### 1. Add Field Script
- **File**: `farm_admin/add_auto_account_field.sql`
- **Purpose**: Add the auto_account field to existing database

### 2. Update Existing Users
- **File**: `farm_admin/update_existing_users.php`
- **Purpose**: Set all existing users to `auto_account = 0`
- **Usage**: Run once after adding the field

### 3. Statistics Viewer
- **File**: `farm_admin/user_statistics.php`
- **Purpose**: View account type statistics and recent accounts

## Usage Instructions

### Setup (One-time)
1. Run `farm_admin/add_auto_account_field.sql` in your database
2. Run `farm_admin/update_existing_users.php` to set existing users
3. Verify with `farm_admin/user_statistics.php`

### Daily Usage
1. **Create test accounts**: Use Auto Create tab (sets `auto_account = 1`)
2. **Update test passwords**: Use Update Passwords tab (affects only auto accounts)
3. **Monitor statistics**: Check `user_statistics.php` for account distribution

## Benefits

### 1. Targeted Operations
- Password updates affect only test accounts
- Normal user accounts remain untouched
- Clear separation of concerns

### 2. Testing Efficiency
- Create hundreds of test accounts quickly
- Manage test account passwords centrally
- Normal user experience unaffected

### 3. Data Integrity
- Normal user accounts protected from admin operations
- Clear audit trail of account creation method
- Better system organization

### 4. Future Extensibility
- Easy to add more auto-specific features
- Can implement different rules for different account types
- Foundation for advanced admin tools

## Security Considerations

### 1. Admin Access
- All auto account operations require admin privileges
- Proper session validation in all scripts
- Database transactions for data consistency

### 2. Data Protection
- Normal user accounts isolated from admin operations
- Clear separation prevents accidental modifications
- Audit trail for all admin operations

### 3. Password Security
- All passwords properly hashed with PASSWORD_DEFAULT
- Password visibility toggle for admin convenience
- Validation for password strength and confirmation

## File Structure

```
farm_admin/
├── add_auto_account_field.sql      # Database migration
├── create_users_auto.php          # Auto account creation
├── create_user_manual.php         # Manual account creation
├── update_all_passwords.php       # Password updates (auto only)
├── update_existing_users.php      # Migration script
├── user_statistics.php            # Statistics viewer
├── AUTO_ACCOUNT_SYSTEM.md         # This documentation
└── panel.php                      # Admin interface
```

## Troubleshooting

### Common Issues

1. **Field doesn't exist**
   - Run `add_auto_account_field.sql`
   - Check database permissions

2. **Existing users not updated**
   - Run `update_existing_users.php`
   - Check for NULL values in auto_account

3. **Password updates not working**
   - Verify admin permissions
   - Check if accounts have `auto_account = 1`

4. **Statistics not showing**
   - Run `user_statistics.php` as admin
   - Check database connection

### Verification Queries

```sql
-- Check field exists
SHOW COLUMNS FROM users LIKE 'auto_account';

-- Check account distribution
SELECT 
    auto_account,
    COUNT(*) as count
FROM users 
GROUP BY auto_account;

-- Check for NULL values
SELECT COUNT(*) FROM users WHERE auto_account IS NULL;
```

## Future Enhancements

1. **Account Management**
   - Bulk delete auto accounts
   - Account type conversion tools
   - Advanced filtering and search

2. **Monitoring**
   - Real-time statistics dashboard
   - Account creation alerts
   - Usage analytics

3. **Integration**
   - API endpoints for account management
   - Webhook notifications
   - External system integration

# Setup Instructions for Admin System

## Problem
The error you're seeing indicates that the `admin_level` column doesn't exist in the `users` table. This column is required for the admin grading system and user management features.

## Solution
I've created a setup system that will automatically add all required database columns and tables.

## Steps to Fix

### 1. Access Admin Panel
1. Log in to your admin account
2. Go to Settings → Admin Panel
3. You should now see a new "Setup Admin System" button

### 2. Run Setup
1. Click on the "Setup Admin System" tab
2. Click the "Setup Admin System" button
3. Wait for the setup to complete (it will show progress)
4. The page will automatically reload after setup

### 3. What the Setup Does
The setup will automatically:

- **Add Required Columns to `users` table:**
  - `admin_level` - Admin grade levels (1=SUPER_ADMIN, 2=ADMIN, 3=MODERATOR, 4=HELPER, 5=USER)
  - `auto_account` - Distinguish auto-created accounts from normal users
  - `is_banned` - Ban status for users
  - `ban_reason` - Reason for ban
  - `ban_end_date` - When ban expires
  - `banned_by` - Who banned the user
  - `banned_at` - When the user was banned

- **Create `admin_activity_logs` table** for tracking admin actions

- **Update Existing Users:**
  - Set current admins (`is_admin = 1`) to `admin_level = 2` (ADMIN)
  - Set user with ID 1 (quatro) to `admin_level = 1` (SUPER_ADMIN)
  - Set all other users to `admin_level = 5` (USER)
  - Set all existing users to `auto_account = 0` (normal users)

- **Add Database Indexes** for better performance

### 4. After Setup
Once setup is complete, you'll have access to:

- **User Management** tab (SUPER_ADMIN only)
- **Admin Grades** tab for managing admin levels
- **Add Users** tab for creating test accounts
- All the user management features (statistics, ban management, account editing)

## Manual Setup (Alternative)
If you prefer to run the SQL manually, you can use the file `farm_admin/add_admin_columns.sql`:

1. Open phpMyAdmin or your database management tool
2. Select your database (`datingz1`)
3. Go to SQL tab
4. Copy and paste the contents of `farm_admin/add_admin_columns.sql`
5. Execute the SQL

## Verification
After setup, you can verify everything is working by:

1. Going to Admin Panel → User Management (should be visible for SUPER_ADMIN)
2. Checking the Statistics tab for user counts
3. Testing the Admin Grades tab

## Troubleshooting

### If Setup Fails
- Check that you have admin privileges in the database
- Ensure the database connection is working
- Check PHP error logs for detailed error messages

### If User Management Tab Doesn't Appear
- Make sure you're logged in as a user with `admin_level = 1` (SUPER_ADMIN)
- Check that the setup completed successfully
- Refresh the page after setup

### If You Get Permission Errors
- Ensure you're logged in as an admin
- Check that the `admin_level` column was added correctly
- Verify your user has `admin_level = 1` or `admin_level = 2`

## Support
If you encounter any issues during setup, check:
1. PHP error logs
2. Database connection
3. User permissions
4. Browser console for JavaScript errors

The setup is designed to be safe and won't affect existing data - it only adds new columns and tables.

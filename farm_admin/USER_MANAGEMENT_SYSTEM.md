# User Management System - SUPER_ADMIN Only

## Overview
The User Management System is a comprehensive admin tool that allows SUPER_ADMIN users to manage all aspects of user accounts. This system provides detailed statistics, user search capabilities, ban management, and account editing features.

## Features

### 1. Statistics Dashboard
- **Overall Statistics**: Total users, auto-created users, normal users, active admins
- **Grade Distribution**: Visual breakdown of admin levels (SUPER_ADMIN, ADMIN, MODERATOR, HELPER, USER)
- **Recent Activity**: List of the 10 most recently created users
- **Real-time Updates**: Statistics refresh automatically when actions are performed

### 2. All Users Management
- **Advanced Search**: Search by username, email, or user ID
- **Grade Filtering**: Filter users by admin level
- **Type Filtering**: Filter by auto-created vs normal users
- **Pagination**: Handle large user lists efficiently (20 users per page)
- **Quick Actions**: Direct edit and ban buttons for each user

### 3. Ban Management
- **User Search**: Find users by username or email
- **Ban/Unban Actions**: Toggle user ban status
- **Flexible Duration**: 1 day, 7 days, 30 days, 1 year, or permanent
- **Reason Tracking**: Required reason for all ban/unban actions
- **Status Display**: Clear indication of current ban status and duration
- **Protection**: SUPER_ADMIN accounts cannot be banned by lower-level admins

### 4. Account Editor
- **Comprehensive Editing**: Modify all user account fields
- **Field Validation**: Age limits, email format, required fields
- **Grade Management**: Change admin levels (with protection for SUPER_ADMIN)
- **Account Type**: Switch between auto-created and normal user types
- **Duplicate Prevention**: Username and email uniqueness validation

## Access Control

### SUPER_ADMIN Only
- The User Management tab is only visible to users with `admin_level = 1`
- All functionality is protected by permission checks
- SUPER_ADMIN accounts have special protection against downgrading

### Permission Requirements
- `manage_admins` permission required for all operations
- Session validation for all requests
- Admin level verification on every action

## Database Requirements

### Required Tables
1. **users** table with columns:
   - `id`, `username`, `email`, `password`, `age`, `country`, `city`, `gender`
   - `admin_level`, `auto_account`, `is_banned`, `ban_reason`
   - `ban_end_date`, `banned_by`, `banned_at`, `created_at`

2. **admin_activity_logs** table for tracking actions

### Required Functions
- `getGradeName($adminLevel)` - Returns human-readable grade name
- `requireCurrentUserPermission($permission)` - Permission validation

## File Structure

### PHP Backend Files
- `get_user_statistics.php` - Statistics dashboard data
- `get_all_users.php` - User search and pagination
- `search_user_for_ban.php` - User search for ban management
- `ban_user.php` - Ban/unban functionality
- `search_user_for_edit.php` - User search for editing
- `edit_user.php` - User account editing

### Frontend Integration
- **HTML**: Integrated into `farm_admin/panel.php`
- **CSS**: Styled in `farm_admin/admin-panel.css`
- **JavaScript**: Functionality in `farm_admin/admin-panel.js`

## Security Features

### Input Validation
- All inputs are validated and sanitized
- SQL injection prevention through prepared statements
- XSS protection through proper output encoding

### Permission Checks
- Session validation on every request
- Admin level verification
- Protection against unauthorized access

### Audit Trail
- All admin actions are logged in `admin_activity_logs`
- Detailed information about changes made
- Timestamp and admin identification

## Usage Instructions

### 1. Accessing User Management
1. Log in as SUPER_ADMIN (admin_level = 1)
2. Navigate to Admin Panel
3. Click "User Management" tab

### 2. Viewing Statistics
- Statistics load automatically when tab opens
- Real-time updates after actions
- Grade distribution shows admin level breakdown

### 3. Searching Users
1. Go to "All Users" tab
2. Enter search term (username, email, or ID)
3. Apply filters if needed
4. Click "Search"
5. Use pagination for large results

### 4. Banning Users
1. Go to "Ban Management" tab
2. Search for user by username or email
3. Click "Ban" or "Unban" button
4. Fill in reason and duration (for bans)
5. Submit action

### 5. Editing Users
1. Go to "Account Editor" tab
2. Search for user by username or email
3. Click "Edit" button
4. Modify desired fields
5. Save changes

## Error Handling

### Common Error Messages
- "Access denied" - Insufficient permissions
- "User not found" - Invalid user ID or search term
- "Username/Email already taken" - Duplicate validation failure
- "Cannot downgrade SUPER_ADMIN" - Protection mechanism

### Error Display
- Success messages in green
- Error messages in red
- Detailed error information for debugging

## Performance Considerations

### Optimization Features
- Pagination for large user lists (20 per page)
- Efficient database queries with proper indexing
- Minimal data transfer with targeted queries
- Cached statistics where appropriate

### Recommended Indexes
```sql
CREATE INDEX idx_users_admin_level ON users(admin_level);
CREATE INDEX idx_users_auto_account ON users(auto_account);
CREATE INDEX idx_users_is_banned ON users(is_banned);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
```

## Future Enhancements

### Potential Features
- Bulk user operations
- Advanced filtering options
- User activity tracking
- Automated ban expiration
- User import/export functionality
- Advanced reporting and analytics

### Integration Opportunities
- Email notifications for bans
- Discord/Slack integration for admin actions
- API endpoints for external tools
- Advanced user analytics dashboard

## Troubleshooting

### Common Issues
1. **Tab not visible**: Check admin_level is 1
2. **Permission errors**: Verify session and admin status
3. **Search not working**: Check database connection
4. **Statistics not loading**: Verify required functions exist

### Debug Mode
- Check browser console for JavaScript errors
- Verify PHP error logs for backend issues
- Test database queries directly
- Validate session data

## Support

For technical support or feature requests, contact the development team or create an issue in the project repository.

---

**Note**: This system is designed for SUPER_ADMIN use only and includes comprehensive security measures to prevent unauthorized access and abuse.

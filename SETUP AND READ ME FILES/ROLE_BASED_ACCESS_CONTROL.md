# Role-Based Access Control Implementation

## Overview
This document describes the role-based access control (RBAC) system implemented for the FitFuel admin panel.

## Roles and Permissions

### Admin Role
- **Full Access** to all modules including:
  - Dashboard
  - Products
  - Orders
  - Inventory
  - Users
  - Analytics
  - Content
  - Audit Trail

### Manager Role
- **Limited Access** to:
  - Dashboard
  - Orders (Order Management)
  - Inventory (Inventory Management)

### Staff Role
- **Limited Access** to:
  - Dashboard
  - Analytics

## Implementation Details

### 1. Authentication Check (`admin_auth_check.php`)
- Added `hasAccess($module)` function to check if user has permission for a specific module
- Added `requireAccess($module)` function that redirects to dashboard if access is denied
- Module permissions are defined based on role

### 2. Sidebar Navigation (`includes/admin_sidebar.php`)
- Created a reusable sidebar function `renderAdminSidebar($current_page)`
- Sidebar automatically hides menu items based on user role
- Each menu item has an `accessible_to` array defining which roles can see it

### 3. Page-Level Protection
All admin pages now include:
```php
require_once '../admin_auth_check.php';
require_once '../includes/admin_sidebar.php';
requireAccess('module_name'); // e.g., 'orders', 'inventory', 'analytics'
```

### 4. Protected Modules
- **Orders Module**: `admin/orders.php`, `admin/view_order.php`, `admin/generate_invoice.php`
- **Inventory Module**: `admin/inventory.php`
- **Analytics Module**: `admin/analytics.php`
- **Products Module**: `admin/product.php`, `admin/clear_admin_carts.php`
- **Users Module**: `admin/users.php`
- **Content Module**: `admin/content.php`
- **Audit Trail Module**: `admin/audit_logs.php`, `admin/get_audit_log_details.php`

## How It Works

1. **User Login**: When a user logs in with role admin, manager, or staff, they are redirected to the admin dashboard.

2. **Access Check**: Each admin page calls `requireAccess('module_name')` which checks if the user's role has permission to access that module.

3. **Sidebar Rendering**: The sidebar uses the `renderAdminSidebar()` function which only displays menu items that the user has permission to access based on their role.

4. **Access Denial**: If a user tries to access a module they don't have permission for:
   - They are automatically redirected to the dashboard
   - The module will not appear in their sidebar

## Testing the Implementation

To test the role-based access:

1. **Admin User**: 
   - Should see all modules in sidebar
   - Can access all pages

2. **Manager User**:
   - Should only see Dashboard, Orders, and Inventory in sidebar
   - Can access orders.php and inventory.php
   - Will be redirected from other pages

3. **Staff User**:
   - Should only see Dashboard and Analytics in sidebar
   - Can access analytics.php
   - Will be redirected from other pages

## Database Schema

The role is stored in the `users` table:
- Column: `role`
- Type: ENUM('admin','manager','staff','customer')
- Default: 'customer'

## Files Modified

### Core Files
- `admin_auth_check.php` - Added permission functions
- `includes/admin_sidebar.php` - Created reusable sidebar component

### Admin Pages Updated
- `admin/dashboard.php`
- `admin/orders.php`
- `admin/inventory.php`
- `admin/analytics.php`
- `admin/product.php`
- `admin/users.php`
- `admin/content.php`
- `admin/audit_logs.php`

### Supporting Files
- `admin/view_order.php`
- `admin/generate_invoice.php`
- `admin/clear_admin_carts.php`
- `admin/get_audit_log_details.php`

## Security Features

1. **Server-Side Validation**: All access checks happen on the server side
2. **Automatic Redirect**: Users are redirected to dashboard when accessing unauthorized pages
3. **Hidden UI Elements**: Unauthorized modules don't appear in the sidebar
4. **Session-Based**: Permissions are checked based on the user's session role

## Future Enhancements

Potential improvements for the future:
- Add more granular permissions (e.g., manager can only view orders, not edit them)
- Implement permission management UI for admins
- Add audit logging for access attempts
- Create permission groups for easier management


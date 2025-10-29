# Reviews and Returns System - Setup Guide

## Overview
This document provides step-by-step instructions to set up the Reviews and Returns System for FitFuel.

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/nginx web server configured
- File upload permissions configured

## Installation Steps

### Step 1: Run the SQL File

1. Open phpMyAdmin or your MySQL client
2. Select your database (usually `sia_fitfuel`)
3. Go to the SQL tab
4. Run the following SQL file: `SQL FILES/create_reviews_and_returns_tables.sql`

**What this creates:**
- `reviews` table - Stores product reviews
- `review_images` table - Stores review images
- `returns` table - Stores return requests
- `return_images` table - Stores return images

### Step 2: Add Columns to Existing Tables

After running the SQL above, you need to manually add columns to existing tables:

#### Add columns to `products` table:

```sql
ALTER TABLE `products` 
ADD COLUMN `average_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
ADD COLUMN `total_reviews` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_5_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_4_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_3_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_2_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `rating_1_count` int(11) NOT NULL DEFAULT 0;
```

If you get "Duplicate column name" error, the columns already exist - you can skip this step.

#### Add columns to `order_items` table:

```sql
ALTER TABLE `order_items`
ADD COLUMN `review_submitted` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `return_requested` tinyint(1) NOT NULL DEFAULT 0;
```

Again, if you get a duplicate column error, skip this step.

### Step 3: Create Upload Directories

Create the following directories in your project root:

```bash
mkdir uploads/reviews
mkdir uploads/returns
```

### Step 4: Set Proper Permissions

Set write permissions for the web server:

**On Linux:**
```bash
chmod 755 uploads/reviews
chmod 755 uploads/returns
chown -R www-data:www-data uploads/reviews
chown -R www-data:www-data uploads/returns
```

**On Windows:**
Right-click each folder → Properties → Security → Add "Everyone" with "Write" permissions

### Step 5: Update PHP Configuration (if needed)

Ensure these PHP settings in your `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
```

### Step 6: Verify Installation

1. **Check database tables:** All 4 new tables should exist
2. **Check columns:** `products` and `order_items` should have the new rating columns
3. **Test file uploads:** Try uploading an image in the review form

## Files Created/Modified

### New Files:
- `review_submit.php` - Customer review submission page
- `return_request.php` - Customer return request page
- `admin/returns.php` - Admin returns management page
- `SQL FILES/create_reviews_and_returns_tables.sql` - Database schema

### Modified Files:
- `order_details.php` - Added Review/Return buttons
- `product_detail.php` - Added reviews section
- `shop.php` - Added star ratings on product cards
- `admin/view_order.php` - Added returns display

## Usage

### For Customers:

**To Submit a Review:**
1. Go to "My Orders"
2. Find a delivered order
3. Click "Review Product" button
4. Rate the product (1-5 stars)
5. Write optional review text
6. Upload up to 4 images (optional)
7. Submit

**To Request a Return:**
1. Go to "My Orders"
2. Find a delivered order
3. Click "Request Return" button
4. Select return type (Refund/Exchange/Repair)
5. Provide reason
6. Upload supporting images (optional)
7. Submit

### For Admins:

**View Returns:**
1. Go to Admin Dashboard
2. Click "Returns" in sidebar
3. View all return requests
4. Click "View" to see details

**Manage Returns in Order Details:**
1. Go to Orders
2. Click on any order
3. Scroll to "Returns & Refunds" section
4. View all returns for that order

## Troubleshooting

### Issue: "Table already exists" error
**Solution:** This is normal on first run. The tables have been created successfully.

### Issue: "Duplicate column name" error
**Solution:** The columns already exist in your database. Skip the ALTER TABLE steps.

### Issue: Images not uploading
**Solutions:**
- Check `uploads/reviews/` and `uploads/returns/` have write permissions
- Verify PHP upload_max_filesize is at least 10M
- Check web server user has write access to those directories

### Issue: Foreign key constraint errors
**Solutions:**
- Ensure the referenced tables (`users`, `products`, `orders`) exist
- Verify data types match between primary and foreign keys
- Check that referenced rows actually exist before inserting

### Issue: Reviews not showing on product page
**Solutions:**
- Verify the `reviews` table has data
- Check that review status is 'approved'
- Ensure product_id matches between products and reviews tables

## Database Schema Reference

### Reviews Table
- Stores customer reviews with ratings (1-5 stars)
- Linked to: users, products, orders, order_items
- Fields: review_id, order_id, order_item_id, product_id, user_id, rating, review_text, is_verified_purchase, helpful_count, status, created_at, updated_at

### Returns Table  
- Stores return requests
- Linked to: users, products, orders, order_items
- Fields: return_id, order_id, order_item_id, product_id, user_id, return_reason, return_type, status, admin_notes, refund_amount, refund_status, created_at, updated_at

## Support

If you encounter any issues, please:
1. Check the error logs (Apache error log, PHP error log)
2. Verify all SQL commands ran successfully
3. Check file permissions on upload directories
4. Verify database connection settings in `config/database.php`

## Notes

- Rating validation is handled in PHP code (not database constraints)
- Maximum 4 images per review
- Maximum 5 images per return request
- Image size limit: 5MB per image
- Allowed formats: JPEG, PNG, GIF, WebP


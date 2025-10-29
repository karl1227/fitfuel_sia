# Reviews and Returns System - Implementation Guide

## Overview
This document describes the Reviews and Ratings System and Returns & Refunds Management features that have been implemented for the FitFuel e-commerce platform.

## Features Implemented

### 1. Review and Rating System
- Customers can submit product reviews with star ratings (1-5 stars)
- Customers can upload up to 4 images per review
- Reviews display on product detail pages with rating breakdown
- Product ratings show on shop page product cards
- Verified purchase badges for authentic reviews
- Review statistics automatically calculated

### 2. Returns and Refunds Management
- Customers can request returns for delivered products
- Separate "Review" and "Return" buttons on delivered orders
- Return requests include reason and optional images
- Admin can view and manage all return requests
- Returns display in order details view
- Support for refund, exchange, and repair return types

## Database Tables Created

### New Tables Required
Run the SQL file: `SQL FILES/create_reviews_and_returns_tables.sql`

**Tables:**
1. `reviews` - Stores product reviews and ratings
2. `review_images` - Stores images uploaded with reviews (max 4 per review)
3. `returns` - Stores return requests from customers
4. `return_images` - Stores images uploaded with return requests

### Updated Tables
The `products` table has been extended with:
- `average_rating` - Decimal field for average rating
- `total_reviews` - Total number of reviews
- `rating_5_count` through `rating_1_count` - Count of each star rating

The `order_items` table has been extended with:
- `review_submitted` - Tracks if review was submitted
- `return_requested` - Tracks if return was requested

## Files Created

### Customer-Facing Files
1. **review_submit.php** - Form for customers to submit product reviews
   - Star rating selector (1-5 stars)
   - Text review field
   - Image upload (up to 4 images)
   - Validation and error handling

2. **return_request.php** - Form for customers to submit return requests
   - Return type selection (refund/exchange/repair)
   - Reason text field
   - Image upload for supporting evidence
   - Validation and error handling

### Admin Files
3. **admin/returns.php** - Returns management page
   - Lists all return requests
   - View return details
   - Approve/reject returns
   - Process refunds

### Updated Files
4. **order_details.php** - Added Review and Return buttons for delivered orders
5. **product_detail.php** - Added reviews section with rating display
6. **shop.php** - Added star ratings to product cards
7. **admin/view_order.php** - Added returns display in order details

## How to Setup

### Step 1: Run Database Migrations
```sql
-- Run this SQL file to create the necessary tables
-- File: SQL FILES/create_reviews_and_returns_tables.sql
```

### Step 2: Create Upload Directories
```bash
mkdir uploads/reviews
mkdir uploads/returns
chmod 755 uploads/reviews
chmod 755 uploads/returns
```

### Step 3: Verify File Permissions
Ensure the following directories are writable by the web server:
- `uploads/reviews/`
- `uploads/returns/`
- `uploads/profile/`

## Usage Guide

### For Customers

#### Submitting a Review
1. Navigate to "My Orders" after logging in
2. Find a delivered order
3. Click "Review Product" button
4. Select star rating (1-5 stars)
5. Write review text (optional)
6. Upload up to 4 images (optional)
7. Click "Submit Review"

#### Requesting a Return
1. Navigate to "My Orders"
2. Find a delivered order
3. Click "Request Return" button
4. Select return type (Refund/Exchange/Repair)
5. Provide reason for return
6. Upload supporting images (optional)
7. Click "Submit Return Request"

### For Admins

#### Managing Returns
1. Navigate to Admin Dashboard
2. Go to "Returns" page (admin/returns.php)
3. View all return requests
4. Click "View" to see return details
5. Approve or reject returns
6. Process refunds as needed

#### Viewing Returns in Order Details
1. Navigate to Orders page
2. Click on an order to view details
3. Scroll to "Returns & Refunds" section
4. View all returns associated with that order

## Features Detail

### Review System Features
- **Rating Display**: Shows average rating out of 5 stars
- **Rating Breakdown**: Visual bar chart showing distribution of ratings
- **Review Cards**: Each review displays:
  - User profile picture and username
  - Star rating
  - Review text
  - Uploaded images (thumbnail grid)
  - Verified purchase badge
  - Helpful count
  - Date posted
- **Product Stats**: Shows total reviews and average rating on shop cards

### Returns System Features
- **Return Request Form**: Comprehensive form with validation
- **Image Upload**: Customers can provide visual evidence
- **Return Types**: Supports refund, exchange, and repair
- **Status Tracking**: Returns can be pending, approved, rejected, processing, or completed
- **Admin Management**: Admins can view, approve, reject, and process returns
- **Refund Tracking**: System tracks refund amounts and status

## UI/UX Features
- Star rating selector with hover effects
- Image preview before upload
- Responsive design for mobile and desktop
- Error messages and success notifications
- Already reviewed indicator (prevents duplicate reviews)
- Clean, modern UI matching FitFuel design system

## Security Features
- Authentication required for all actions
- Customer can only review products they purchased
- Customer can only request returns for their orders
- File upload validation (type and size)
- SQL injection protection (prepared statements)
- XSS protection (htmlspecialchars)

## Technical Notes

### Image Upload Limits
- Reviews: Maximum 4 images per review
- Returns: Maximum 5 images per return request
- File size: Maximum 5MB per image
- Allowed types: JPEG, PNG, GIF, WebP

### Database Relationships
- Reviews linked to: users, products, orders, order_items
- Returns linked to: users, products, orders, order_items
- Cascade delete on user/product/order deletion
- Foreign key constraints for data integrity

### Performance Considerations
- Reviews limited to 10 most recent on product page
- Ratings cached in products table for fast loading
- Indexes on frequently queried fields

## Future Enhancements (Optional)
- Review helpful voting system
- Review sorting (most helpful, newest, highest/lowest rating)
- Return status email notifications
- Bulk return processing
- Return analytics dashboard
- Review moderation queue
- Automated refund processing
- Exchange item selection

## Support
For issues or questions regarding this implementation, please refer to the documentation or contact the development team.


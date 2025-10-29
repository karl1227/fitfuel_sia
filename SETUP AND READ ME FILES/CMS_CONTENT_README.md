# CMS Content Management Tutorial

Complete guide for managing content on About Us, FAQs, and Contact pages using the Content Management System.

---

## Table of Contents
1. [Overview](#overview)
2. [About Us Page](#about-us-page)
3. [FAQs Page](#faqs-page)
4. [Contact Us Page](#contact-us-page)
5. [SEO Fields](#seo-fields)
6. [Images](#images)
7. [Troubleshooting](#troubleshooting)
8. [Quick Reference](#quick-reference)

---

## Overview

The public pages (`aboutus.php`, `faq.php`, `contact.php`) automatically read content from your CMS `contents` table. 

### Required Fields in CMS:
- **Content Type**: Must match exactly (`about`, `faq`, or `contact`)
- **Title**: Used as section heading or FAQ question
- **Body**: Main content text (see format requirements below)
- **Status**: Must be set to **"Published"** for content to appear

### Optional Fields:
- **Slug**: Helpful for organization/searching (e.g., `aboutus`, `faq-shipping`)
- **SEO Title**: Page title for search engines
- **SEO Description**: Meta description for search engines
- **SEO Keywords**: Comma-separated keywords
- **Image/Image Path**: For About Us sections

---

## About Us Page

The About Us page displays up to 3 sections. Create separate content entries for each.

### Section 1: About Us

**Settings:**
- **Content Type**: `about`
- **Title**: `About Us` (must contain "About" in the title)
- **Slug**: `aboutus` (optional, but helpful)
- **Status**: Published

**Body** (copy and paste this exactly):
```
At Fit fuel we are dedicated to providing premium gym supplements designed to support and elevate your fitness journey. Our carefully curated selection of products meets the highest industry standards, ensuring optimal quality, safety, and effectiveness. Whether you're an athlete, a fitness professional, or someone committed to personal wellness, we offer the tools you need to achieve your performance and health goals. We pride ourselves on delivering trusted solutions that help you unlock your full potential.
```

**SEO Fields** (optional but recommended):
- **SEO Title**: `About FitFuel | Premium Gym Supplements`
- **SEO Description**: `Learn about FitFuel's mission to deliver safe, high-quality supplements for performance, recovery, and wellness.`
- **SEO Keywords**: `fitfuel, about fitfuel, gym supplements, performance, recovery`

**Image** (optional):
- Upload an image or set `image_path` to: `uploads/content/about_hero.jpg`

---

### Section 2: Mission & Vision

**Settings:**
- **Content Type**: `about`
- **Title**: `Our Mission & Vision` (must contain "Mission" in the title)
- **Slug**: `mission-vision`
- **Status**: Published

**Body** (copy and paste this exactly):
```
Our Mission
Our mission is to empower individuals to achieve their fitness goals by offering scientifically backed, high-quality gym supplements that enhance performance, accelerate recovery, and support overall well-being. We are committed to delivering excellence in every product and experience, fostering a healthier, more active lifestyle for all.

Our Vision
Our vision is to become the leading e-commerce platform for gym supplements, recognized for our commitment to excellence, innovation, and customer satisfaction. We aspire to build a trusted community where individuals are equipped with the knowledge and products they need to optimize their health, performance, and quality of life.
```

**SEO Fields**:
- **SEO Title**: `FitFuel Mission & Vision | Empowering Fitness Goals`
- **SEO Description**: `See how FitFuel inspires healthier, more active lives through science-backed supplements.`
- **SEO Keywords**: `fitfuel mission, fitfuel vision, fitness goals, science backed supplements`

**Image** (optional):
- `uploads/content/mission_vision.jpg`

---

### Section 3: Core Values

**Settings:**
- **Content Type**: `about`
- **Title**: `Our Core Values` (must contain "Values" in the title)
- **Slug**: `core-values`
- **Status**: Published

**Body** (copy and paste this exactly):
```
Excellence
We are unwavering in our commitment to providing superior products that meet rigorous standards of quality, potency, and safety.

Integrity
We uphold transparency, honesty, and ethical business practices, ensuring our customers can make informed decisions with confidence.

Customer Focus
We prioritize the needs and satisfaction of our customers, striving to exceed expectations with exceptional service and tailored experiences.

Innovation
We continuously explore advancements in sports nutrition and fitness science to offer cutting-edge products that deliver results.
```

**SEO Fields**:
- **SEO Title**: `FitFuel Core Values | Excellence, Integrity, Customer Focus`
- **SEO Description**: `The principles that guide our products and service: Excellence, Integrity, Customer Focus, and Innovation.`
- **SEO Keywords**: `fitfuel values, excellence, integrity, customer focus, innovation`

**Image** (optional):
- `uploads/content/core_values.jpg`

---

## FAQs Page

Create **ONE content entry per FAQ question**. Each entry becomes one Q&A item on the page.

### FAQ Entry Template

**Settings (for each FAQ):**
- **Content Type**: `faq`
- **Title**: The question (e.g., `What types of supplements do you offer?`)
- **Slug**: Short key (e.g., `faq-what-supplements`)
- **Body**: The answer (plain text, no special formatting needed)
- **Status**: Published

---

### Pre-written FAQs (Copy & Paste Ready)

#### FAQ 1
- **Content Type**: `faq`
- **Title**: `What types of supplements do you offer?`
- **Slug**: `faq-what-supplements`
- **Body**:
```
We offer a wide range of gym supplements, including protein powders, pre-workouts, BCAAs, fat burners, multivitamins, and more to support your fitness goals.
```

#### FAQ 2
- **Content Type**: `faq`
- **Title**: `Are your supplements safe to use?`
- **Slug**: `faq-supplement-safety`
- **Body**:
```
Yes, all our supplements are sourced from reputable brands and undergo strict quality control to ensure safety and effectiveness.
```

#### FAQ 3
- **Content Type**: `faq`
- **Title**: `How do I choose the right supplement for my fitness goals?`
- **Slug**: `faq-choosing-supplements`
- **Body**:
```
Our product descriptions provide detailed benefits and usage recommendations. You can also reach out to our support team for personalized advice.
```

#### FAQ 4
- **Content Type**: `faq`
- **Title**: `Do you offer discounts or promotions?`
- **Slug**: `faq-discounts`
- **Body**:
```
Yes! We regularly run promotions and offer discounts for first-time buyers, bulk purchases, and loyal customers. Check our website or subscribe to our newsletter for updates.
```

#### FAQ 5
- **Content Type**: `faq`
- **Title**: `What payment methods do you accept?`
- **Slug**: `faq-payment-methods`
- **Body**:
```
We accept major credit/debit cards, digital wallets, and bank transfers. More payment options may be available depending on your location.
```

#### FAQ 6
- **Content Type**: `faq`
- **Title**: `How long does shipping take?`
- **Slug**: `faq-shipping-time`
- **Body**:
```
Shipping times vary by location. Typically, orders are delivered within 3-7 business days for domestic shipping and 7-14 business days for international orders.
```

#### FAQ 7
- **Content Type**: `faq`
- **Title**: `Do you ship internationally?`
- **Slug**: `faq-international-shipping`
- **Body**:
```
Yes! We offer international shipping to select countries. Shipping fees and delivery times vary based on your location.
```

#### FAQ 8
- **Content Type**: `faq`
- **Title**: `Can I return or exchange a product?`
- **Slug**: `faq-returns`
- **Body**:
```
We accept returns or exchanges within 7 days of delivery, provided the product is unopened and in its original condition. See our return policy for details.
```

#### FAQ 9
- **Content Type**: `faq`
- **Title**: `Are your supplements FDA-approved?`
- **Slug**: `faq-fda-approval`
- **Body**:
```
We only carry products that comply with industry safety standards. However, regulatory approvals may vary by country. Please check individual product labels for certifications.
```

#### FAQ 10
- **Content Type**: `faq`
- **Title**: `How can I contact your support team?`
- **Slug**: `faq-contact-support`
- **Body**:
```
You can reach us via email at siafitfuel@gmail.com, through our live chat, or by calling our hotline during business hours.
```

---

## Contact Us Page

Create **ONE content entry** with contact information formatted as key:value pairs.

### Contact Entry

**Settings:**
- **Content Type**: `contact`
- **Title**: `Contact Info` (can be anything)
- **Slug**: `contact`
- **Status**: Published

**Body** (CRITICAL: Must be in this exact format - one key:value per line, no quotes):
```
email: siafitfuel@gmail.com
address: Anonas LRT, Aurora Blvd, Quezon City
phone: 09123456789
facebook: https://facebook.com/yourpage
twitter: https://twitter.com/yourhandle
instagram: https://instagram.com/yourhandle
tiktok: https://tiktok.com/@yourhandle
```

**Important Notes:**
- Each line must be in `key: value` format
- Use a colon (:) after the key, then a space, then the value
- One key:value pair per line
- No quotes around values
- If multiple contact entries exist, the most recently updated one is used

**Example SEO Fields:**
- **SEO Title**: `Contact FitFuel | Get in Touch`
- **SEO Description**: `Get in touch with FitFuel via email, phone, or social media channels.`
- **SEO Keywords**: `contact fitfuel, fitfuel email, fitfuel phone, customer support`

---

## SEO Fields

All three page types support optional SEO metadata.

### SEO Title
- Keep it under 60 characters
- Include your brand name
- Example: `About FitFuel | Premium Gym Supplements`

### SEO Description
- Keep it between 150-160 characters
- Summarize the content
- Example: `Learn about FitFuel's mission to deliver safe, high-quality supplements for performance, recovery, and wellness.`

### SEO Keywords
- Comma-separated keywords
- Example: `fitfuel, gym supplements, fitness, protein, pre-workout`

---

## Images

### For About Us Sections

If your CMS supports image uploads, the pages will automatically use:
1. The uploaded `image` field (if available), OR
2. The `image_path` field (if available)

**Recommended Image Paths:**
- About section: `uploads/content/about_hero.jpg`
- Mission & Vision: `uploads/content/mission_vision.jpg`
- Core Values: `uploads/content/core_values.jpg`

**Image Requirements:**
- Format: JPG, PNG, or WEBP
- Recommended size: 800x600px minimum
- File size: Keep under 2MB for web performance

---

## Troubleshooting

### Content Not Appearing on Page

**Check:**
1. ✅ Is Status set to **"Published"**? (Not "Draft" or "Pending")
2. ✅ Is Content Type exactly `about`, `faq`, or `contact`? (Case-sensitive)
3. ✅ Hard refresh the page (Ctrl+F5 or Cmd+Shift+R)
4. ✅ Clear browser cache

---

### About Us Sections Not Mapping Correctly

**Problem:** Wrong section showing wrong content

**Solution:**
- Ensure titles contain keywords: "About", "Mission", or "Values"
- The page picks the latest published item per keyword
- If issues persist, we can add explicit `section` field support

---

### Contact Info Not Showing

**Problem:** Contact page shows defaults instead of CMS content

**Check:**
1. Body format must be exact `key: value` (one per line)
2. No quotes around values
3. Colon after key, space before value
4. Example of **WRONG** format:
   ```
   "email": "siafitfuel@gmail.com"  ❌
   email=siafitfuel@gmail.com       ❌
   ```
5. Example of **CORRECT** format:
   ```
   email: siafitfuel@gmail.com      ✅
   ```

---

### Images Not Loading

**Problem:** Default placeholder images showing instead of uploaded images

**Solutions:**
1. Check if `image` or `image_path` field is populated in CMS
2. Verify file path exists on server (e.g., `uploads/content/about_hero.jpg`)
3. Check file permissions (should be readable by web server)
4. Use relative paths from site root (no leading slash)
5. Correct: `uploads/content/image.jpg` ✅
6. Wrong: `/uploads/content/image.jpg` ❌

---

### FAQs Not Displaying

**Problem:** FAQ page is empty or showing wrong questions

**Check:**
1. Status must be **Published** for each FAQ
2. Content Type must be exactly `faq`
3. Multiple FAQs are displayed (not just one)
4. Each FAQ needs its own content entry

---

### Can't Find Content Type Option

**Solution:**
- If your CMS uses a dropdown, ensure these options exist:
  - `about`
  - `faq`
  - `contact`
- If not available, contact your developer to add these types

---

## Quick Reference

### Content Type Quick Guide

| Page | Content Type | How Many | Title Contains |
|------|--------------|----------|----------------|
| About Us | `about` | Up to 3 | "About", "Mission", or "Values" |
| FAQs | `faq` | As many as needed | Any question text |
| Contact | `contact` | 1 | Any (preferably "Contact") |

### Field Priority

**About Us:**
- Latest published item with `type='about'` matching title keywords

**FAQs:**
- ALL published items with `type='faq'` are displayed

**Contact:**
- Most recently updated published item with `type='contact'`

---

## Step-by-Step: Adding Your First FAQ

1. **Go to Admin** → **Contents** → **+ Add Content**
2. **Set Content Type** dropdown to: `faq`
3. **Title**: Type your question (e.g., `What types of supplements do you offer?`)
4. **Slug**: Type short key (e.g., `faq-supplements`)
5. **Body**: Paste the answer text
6. **SEO Title**: Optional (e.g., `FAQ | Types of Supplements`)
7. **SEO Description**: Optional (e.g., `Learn about our supplement categories`)
8. **SEO Keywords**: Optional (e.g., `faq, supplements, protein`)
9. **Status**: Change to **Published** (IMPORTANT!)
10. **Click Save/Publish**
11. **Visit** `faq.php` and hard refresh (Ctrl+F5) to see your FAQ

---

## Step-by-Step: Setting Up Contact Info

1. **Go to Admin** → **Contents** → **+ Add Content**
2. **Set Content Type** dropdown to: `contact`
3. **Title**: Type `Contact Info`
4. **Slug**: Type `contact`
5. **Body** - Paste EXACTLY this format (no quotes!):
   ```
   email: siafitfuel@gmail.com
   address: Anonas LRT, Aurora Blvd, Quezon City
   phone: 09123456789
   facebook: https://facebook.com/yourpage
   twitter: https://twitter.com/yourhandle
   instagram: https://instagram.com/yourhandle
   tiktok: https://tiktok.com/@yourhandle
   ```
6. **SEO fields**: Optional (fill if you want)
7. **Status**: Change to **Published**
8. **Click Save/Publish**
9. **Visit** `contact.php` and refresh to see your contact info

---

## Email Configuration (Optional)

For newsletter subscriptions and contact form emails, configure SMTP via environment variables:

```
SMTP_ENABLED=1
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_smtp_username
SMTP_PASSWORD=your_smtp_password
SMTP_SECURE=tls
MAIL_FROM=no-reply@yourdomain.com
MAIL_FROM_NAME=FitFuel
MAIL_ADMIN=admin@yourdomain.com
```

If not configured, the system attempts to use PHP's `mail()` function (may not work on all servers).

---

## Need Help?

If content still isn't appearing after checking all troubleshooting steps:

1. **Check Database Directly:**
   ```sql
   SELECT * FROM contents WHERE type='faq' AND status='published';
   ```

2. **Verify Content Type Values:**
   - Should be lowercase: `about`, `faq`, `contact`
   - Not: `About`, `FAQ`, `Contact`

3. **Check File Paths:**
   - About Us images: `uploads/content/` directory exists
   - Permissions are set correctly (755 for directories, 644 for files)

4. **Clear Cache:**
   - Browser cache (Ctrl+Shift+Delete)
   - Server-side cache (if enabled)
   - Hard refresh page (Ctrl+F5)

---

## Summary Checklist

Before publishing any content, verify:
- [ ] Content Type is set correctly (`about`, `faq`, or `contact`)
- [ ] Title is filled in
- [ ] Body contains proper content (Contact uses key:value format)
- [ ] Status is set to **"Published"** (not Draft)
- [ ] Slug is filled (optional but recommended)
- [ ] SEO fields are filled (optional but recommended)
- [ ] Image is uploaded or path is set (About Us only, optional)

After publishing:
- [ ] Hard refresh the page (Ctrl+F5 or Cmd+Shift+R)
- [ ] Check that content appears correctly
- [ ] Verify images load (if applicable)
- [ ] Test on mobile device (responsive check)

---

**Last Updated:** 2025
**Version:** 1.0


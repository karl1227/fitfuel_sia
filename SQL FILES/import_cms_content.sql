-- ============================================================================
-- FitFuel CMS Content Import SQL
-- This file imports all pre-written content for About Us, FAQs, and Contact
-- ============================================================================
-- IMPORTANT: Run this after your main database is set up
-- This will add content entries that power the public pages:
--   - aboutus.php
--   - faq.php  
--   - contact.php
-- ============================================================================

-- First, extend the type enum to support 'about' and 'contact' if not already present
-- (This is safe - will only add if they don't exist)
ALTER TABLE `contents` 
MODIFY COLUMN `type` ENUM('page','banner','homepage','faq','about','contact') NOT NULL;

-- ============================================================================
-- ABOUT US CONTENT (3 sections)
-- ============================================================================

-- 1. About Us Section
INSERT INTO `contents` (
    `title`, 
    `type`, 
    `status`, 
    `body`, 
    `description`,
    `seo_title`, 
    `seo_description`, 
    `seo_keywords`, 
    `slug`, 
    `placement`,
    `author_user_id`,
    `created_at`, 
    `updated_at`
) VALUES (
    'About Us',
    'about',
    'published',
    'At Fit fuel we are dedicated to providing premium gym supplements designed to support and elevate your fitness journey. Our carefully curated selection of products meets the highest industry standards, ensuring optimal quality, safety, and effectiveness. Whether you\'re an athlete, a fitness professional, or someone committed to personal wellness, we offer the tools you need to achieve your performance and health goals. We pride ourselves on delivering trusted solutions that help you unlock your full potential.',
    'At Fit fuel we are dedicated to providing premium gym supplements designed to support and elevate your fitness journey. Our carefully curated selection of products meets the highest industry standards, ensuring optimal quality, safety, and effectiveness. Whether you\'re an athlete, a fitness professional, or someone committed to personal wellness, we offer the tools you need to achieve your performance and health goals. We pride ourselves on delivering trusted solutions that help you unlock your full potential.',
    'About FitFuel | Premium Gym Supplements',
    'Learn about FitFuel\'s mission to deliver safe, high-quality supplements for performance, recovery, and wellness.',
    'fitfuel, about fitfuel, gym supplements, performance, recovery',
    'aboutus',
    'about',
    1,
    NOW(),
    NOW()
);

-- 2. Mission & Vision Section
INSERT INTO `contents` (
    `title`, 
    `type`, 
    `status`, 
    `body`, 
    `description`,
    `seo_title`, 
    `seo_description`, 
    `seo_keywords`, 
    `slug`, 
    `placement`,
    `author_user_id`,
    `created_at`, 
    `updated_at`
) VALUES (
    'Our Mission & Vision',
    'about',
    'published',
    'Our Mission
Our mission is to empower individuals to achieve their fitness goals by offering scientifically backed, high-quality gym supplements that enhance performance, accelerate recovery, and support overall well-being. We are committed to delivering excellence in every product and experience, fostering a healthier, more active lifestyle for all.

Our Vision
Our vision is to become the leading e-commerce platform for gym supplements, recognized for our commitment to excellence, innovation, and customer satisfaction. We aspire to build a trusted community where individuals are equipped with the knowledge and products they need to optimize their health, performance, and quality of life.',
    'Our Mission
Our mission is to empower individuals to achieve their fitness goals by offering scientifically backed, high-quality gym supplements that enhance performance, accelerate recovery, and support overall well-being. We are committed to delivering excellence in every product and experience, fostering a healthier, more active lifestyle for all.

Our Vision
Our vision is to become the leading e-commerce platform for gym supplements, recognized for our commitment to excellence, innovation, and customer satisfaction. We aspire to build a trusted community where individuals are equipped with the knowledge and products they need to optimize their health, performance, and quality of life.',
    'FitFuel Mission & Vision | Empowering Fitness Goals',
    'See how FitFuel inspires healthier, more active lives through science-backed supplements.',
    'fitfuel mission, fitfuel vision, fitness goals, science backed supplements',
    'mission-vision',
    'mission',
    1,
    NOW(),
    NOW()
);

-- 3. Core Values Section
INSERT INTO `contents` (
    `title`, 
    `type`, 
    `status`, 
    `body`, 
    `description`,
    `seo_title`, 
    `seo_description`, 
    `seo_keywords`, 
    `slug`, 
    `placement`,
    `author_user_id`,
    `created_at`, 
    `updated_at`
) VALUES (
    'Our Core Values',
    'about',
    'published',
    'Excellence
We are unwavering in our commitment to providing superior products that meet rigorous standards of quality, potency, and safety.

Integrity
We uphold transparency, honesty, and ethical business practices, ensuring our customers can make informed decisions with confidence.

Customer Focus
We prioritize the needs and satisfaction of our customers, striving to exceed expectations with exceptional service and tailored experiences.

Innovation
We continuously explore advancements in sports nutrition and fitness science to offer cutting-edge products that deliver results.',
    'Excellence
We are unwavering in our commitment to providing superior products that meet rigorous standards of quality, potency, and safety.

Integrity
We uphold transparency, honesty, and ethical business practices, ensuring our customers can make informed decisions with confidence.

Customer Focus
We prioritize the needs and satisfaction of our customers, striving to exceed expectations with exceptional service and tailored experiences.

Innovation
We continuously explore advancements in sports nutrition and fitness science to offer cutting-edge products that deliver results.',
    'FitFuel Core Values | Excellence, Integrity, Customer Focus',
    'The principles that guide our products and service: Excellence, Integrity, Customer Focus, and Innovation.',
    'fitfuel values, excellence, integrity, customer focus, innovation',
    'core-values',
    'values',
    1,
    NOW(),
    NOW()
);

-- ============================================================================
-- FAQ CONTENT (10 FAQs)
-- ============================================================================

-- FAQ 1
INSERT INTO `contents` (
    `title`, 
    `type`, 
    `status`, 
    `body`, 
    `description`,
    `seo_title`, 
    `seo_description`, 
    `seo_keywords`, 
    `slug`, 
    `placement`,
    `author_user_id`,
    `created_at`, 
    `updated_at`
) VALUES (
    'What types of supplements do you offer?',
    'faq',
    'published',
    'We offer a wide range of gym supplements, including protein powders, pre-workouts, BCAAs, fat burners, multivitamins, and more to support your fitness goals.',
    'We offer a wide range of gym supplements, including protein powders, pre-workouts, BCAAs, fat burners, multivitamins, and more to support your fitness goals.',
    'FAQ | Types of Supplements',
    'Learn about our supplement categories for performance and recovery.',
    'faq, supplements, protein, pre-workout, bcaa',
    'faq-what-supplements',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 2
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'Are your supplements safe to use?',
    'faq',
    'published',
    'Yes, all our supplements are sourced from reputable brands and undergo strict quality control to ensure safety and effectiveness.',
    'Yes, all our supplements are sourced from reputable brands and undergo strict quality control to ensure safety and effectiveness.',
    'faq-supplement-safety',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 3
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'How do I choose the right supplement for my fitness goals?',
    'faq',
    'published',
    'Our product descriptions provide detailed benefits and usage recommendations. You can also reach out to our support team for personalized advice.',
    'Our product descriptions provide detailed benefits and usage recommendations. You can also reach out to our support team for personalized advice.',
    'faq-choosing-supplements',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 4
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'Do you offer discounts or promotions?',
    'faq',
    'published',
    'Yes! We regularly run promotions and offer discounts for first-time buyers, bulk purchases, and loyal customers. Check our website or subscribe to our newsletter for updates.',
    'Yes! We regularly run promotions and offer discounts for first-time buyers, bulk purchases, and loyal customers. Check our website or subscribe to our newsletter for updates.',
    'faq-discounts',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 5
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'What payment methods do you accept?',
    'faq',
    'published',
    'We accept major credit/debit cards, digital wallets, and bank transfers. More payment options may be available depending on your location.',
    'We accept major credit/debit cards, digital wallets, and bank transfers. More payment options may be available depending on your location.',
    'faq-payment-methods',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 6
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'How long does shipping take?',
    'faq',
    'published',
    'Shipping times vary by location. Typically, orders are delivered within 3-7 business days for domestic shipping and 7-14 business days for international orders.',
    'Shipping times vary by location. Typically, orders are delivered within 3-7 business days for domestic shipping and 7-14 business days for international orders.',
    'faq-shipping-time',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 7
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'Do you ship internationally?',
    'faq',
    'published',
    'Yes! We offer international shipping to select countries. Shipping fees and delivery times vary based on your location.',
    'Yes! We offer international shipping to select countries. Shipping fees and delivery times vary based on your location.',
    'faq-international-shipping',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 8
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'Can I return or exchange a product?',
    'faq',
    'published',
    'We accept returns or exchanges within 7 days of delivery, provided the product is unopened and in its original condition. See our return policy for details.',
    'We accept returns or exchanges within 7 days of delivery, provided the product is unopened and in its original condition. See our return policy for details.',
    'faq-returns',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 9
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'Are your supplements FDA-approved?',
    'faq',
    'published',
    'We only carry products that comply with industry safety standards. However, regulatory approvals may vary by country. Please check individual product labels for certifications.',
    'We only carry products that comply with industry safety standards. However, regulatory approvals may vary by country. Please check individual product labels for certifications.',
    'faq-fda-approval',
    'faq',
    1,
    NOW(),
    NOW()
);

-- FAQ 10
INSERT INTO `contents` (
    `title`, `type`, `status`, `body`, `description`, `slug`, `placement`, `author_user_id`, `created_at`, `updated_at`
) VALUES (
    'How can I contact your support team?',
    'faq',
    'published',
    'You can reach us via email at siafitfuel@gmail.com, through our live chat, or by calling our hotline during business hours.',
    'You can reach us via email at siafitfuel@gmail.com, through our live chat, or by calling our hotline during business hours.',
    'faq-contact-support',
    'faq',
    1,
    NOW(),
    NOW()
);

-- ============================================================================
-- CONTACT US CONTENT (1 entry with key:value format)
-- ============================================================================

INSERT INTO `contents` (
    `title`, 
    `type`, 
    `status`, 
    `body`, 
    `description`,
    `seo_title`, 
    `seo_description`, 
    `seo_keywords`, 
    `slug`, 
    `placement`,
    `author_user_id`,
    `created_at`, 
    `updated_at`
) VALUES (
    'Contact Info',
    'contact',
    'published',
    'email: siafitfuel@gmail.com
address: Anonas LRT, Aurora Blvd, Quezon City
phone: 09123456789
facebook: https://facebook.com/yourpage
twitter: https://twitter.com/yourhandle
instagram: https://instagram.com/yourhandle
tiktok: https://tiktok.com/@yourhandle',
    'email: siafitfuel@gmail.com
address: Anonas LRT, Aurora Blvd, Quezon City
phone: 09123456789
facebook: https://facebook.com/yourpage
twitter: https://twitter.com/yourhandle
instagram: https://instagram.com/yourhandle
tiktok: https://tiktok.com/@yourhandle',
    'Contact FitFuel | Get in Touch',
    'Get in touch with FitFuel via email, phone, or social media channels.',
    'contact fitfuel, fitfuel email, fitfuel phone, customer support',
    'contact',
    'contact',
    1,
    NOW(),
    NOW()
);

-- ============================================================================
-- IMPORT COMPLETE
-- ============================================================================
-- You should now see:
--   - 3 About Us sections on aboutus.php
--   - 10 FAQs on faq.php
--   - Contact info on contact.php
-- ============================================================================
-- Note: Update social media URLs in the Contact entry with your actual links
-- ============================================================================


INSERT INTO products 
(name, description, price, category_id, subcategory_id, stock_quantity, status, is_popular, is_best_seller) VALUES

-- Gym Accessories: Lifting Gear
('Weightlifting Gloves', 'Padded gloves for better grip and hand protection during heavy lifts.', 990.00, 1, 11, 50, 'active', 0, 0),
('Wrist Straps', 'Durable straps to support your wrists during intense workouts.', 650.00, 1, 11, 40, 'active', 0, 0),
('Weightlifting Belt', 'Provides back support for heavy lifting and powerlifting.', 1200.00, 1, 11, 30, 'active', 0, 0),
('Chalk Ball', 'Enhance grip and reduce sweat with high-quality gym chalk. Perfect for lifting, climbing, and CrossFit.', 190.00, 1, 11, 100, 'active', 0, 0),
('Barbell Pads', 'Protect your joints during intense workouts with padded barbell support.', 750.00, 1, 11, 45, 'active', 0, 0),

-- Gym Accessories: Recovery Tools
('Massage Gun', 'Deep tissue massage tool for faster muscle recovery.', 3500.00, 1, 12, 20, 'active', 0, 0),
('Gel Pack', 'Hot and cold gel pack for muscle relief.', 450.00, 1, 12, 50, 'active', 0, 0),
('Compression Sleeves', 'Enhances blood flow and reduces soreness.', 700.00, 1, 12, 35, 'active', 0, 0),
('Stretching Strap', 'Assists in improving flexibility and stretching.', 400.00, 1, 12, 60, 'active', 0, 0),
('Resistance Band', 'Multi-purpose band for rehab and warm-up routines.', 300.00, 1, 12, 80, 'active', 0, 0),

-- Gym Accessories: Hydration & Storage
('Shaker Bottle', 'Durable shaker for protein shakes and supplements.', 350.00, 1, 13, 100, 'active', 0, 0),
('Duffle Bag', 'Spacious gym bag for gear and clothes.', 1500.00, 1, 13, 40, 'active', 0, 0),
('Meal Prep Box', 'Keeps meals fresh and organized for fitness diets.', 800.00, 1, 13, 50, 'active', 0, 0),
('Cooling Towel', 'Stay cool during intense workouts with this quick-dry towel.', 450.00, 1, 13, 60, 'active', 0, 0),
('Electrolyte Tablets', 'Replenishes lost minerals during heavy sweating.', 300.00, 1, 13, 70, 'active', 0, 0),

-- Gym Equipment: Weights
('Dumbbell Set', 'High-quality dumbbells for home or gym use which is ideal for strength, toning, and full-body workouts.', 3500.00, 2, 14, 25, 'active', 0, 0),
('Kettlebell', 'Durable kettlebell for swings, squats, and functional training.', 1750.00, 2, 14, 30, 'active', 0, 0),
('Barbell', 'Olympic and standard barbells for heavy lifting.', 2200.00, 2, 14, 20, 'active', 0, 0),
('Weight Plates', 'Plates for Olympic and standard barbells.', 2800.00, 2, 14, 35, 'active', 0, 0),
('Medicine Ball', 'Perfect for strength and core training exercises.', 1200.00, 2, 14, 30, 'active', 0, 0),

-- Gym Equipment: Calisthenic Equipment
('Jump Rope', 'Adjustable speed rope for cardio and endurance.', 400.00, 2, 15, 60, 'active', 0, 0),
('Parallette Bars', 'Perfect for calisthenics and bodyweight training.', 2200.00, 2, 15, 15, 'active', 0, 0),
('Dip Belts', 'Adds extra weight for dips and pull-ups.', 1500.00, 2, 15, 20, 'active', 0, 0),
('Pull-up Bar', 'Lockable pull-up bar for doorway strength training.', 2500.00, 2, 15, 15, 'active', 0, 0),
('Gymnastic Rings', 'Adjustable rings for advanced bodyweight exercises.', 1800.00, 2, 15, 20, 'active', 0, 0),

-- Gym Equipment: Mobility Tools
('Foam Roller', 'Helps relieve muscle tension and improve mobility.', 900.00, 2, 16, 35, 'active', 0, 0),
('Massage Stick', 'Portable tool for deep tissue massage.', 600.00, 2, 16, 40, 'active', 0, 0),
('Mobility Ball', 'Small ball for targeted muscle release.', 300.00, 2, 16, 70, 'active', 0, 0),
('Stretching Strap', 'Assists in deep stretches for flexibility.', 400.00, 2, 16, 50, 'active', 0, 0),
('Yoga Mat', 'Non-slip mat for yoga, pilates, and stretching.', 1200.00, 2, 16, 50, 'active', 0, 0),

-- Gym Supplements: Protein Powders
('Whey Protein', 'High-quality whey protein for muscle recovery and growth.', 2200.00, 3, 17, 40, 'active', 0, 0),
('Casein Protein', 'Slow-digesting protein perfect for nighttime recovery.', 2300.00, 3, 17, 35, 'active', 0, 0),
('Plant-Based Protein', 'Vegan protein blend for clean nutrition.', 2400.00, 3, 17, 30, 'active', 0, 0),
('Isolate Whey', 'Ultra-pure whey isolate with fast absorption.', 2500.00, 3, 17, 35, 'active', 0, 0),
('Mass Gainer', 'High-calorie protein blend for bulking.', 2600.00, 3, 17, 25, 'active', 0, 0),

-- Gym Supplements: Pre-workout Boosters
('Pre-workout Booster', 'Energy and focus enhancer for improved workout performance.', 1500.00, 3, 18, 50, 'active', 0, 0),
('Caffeine Booster', 'Fast-acting energy formula for intense training.', 1200.00, 3, 18, 45, 'active', 0, 0),
('Beta-Alanine Formula', 'Improves endurance and reduces fatigue.', 1300.00, 3, 18, 40, 'active', 0, 0),
('Nitric Oxide Booster', 'Enhances blood flow and pumps during workouts.', 1400.00, 3, 18, 35, 'active', 0, 0),
('Creatine Monohydrate', 'Boost strength and power during high-intensity workouts.', 1200.00, 3, 18, 45, 'active', 0, 0),

-- Gym Supplements: Vitamins
('Multivitamins', 'Daily vitamins to support overall health and wellness.', 800.00, 3, 19, 60, 'active', 0, 0),
('Vitamin D3', 'Supports bone and immune health.', 500.00, 3, 19, 70, 'active', 0, 0),
('Vitamin C', 'Boosts immunity and reduces fatigue.', 400.00, 3, 19, 80, 'active', 0, 0),
('Omega-3 Fish Oil', 'Supports heart and brain health.', 900.00, 3, 19, 55, 'active', 0, 0),
('B-Complex Vitamins', 'Helps energy production and nervous system health.', 650.00, 3, 19, 65, 'active', 0, 0);

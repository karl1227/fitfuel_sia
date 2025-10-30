<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$message = trim((string)($input['message'] ?? ''));
$intent = strtolower(trim((string)($input['intent'] ?? '')));

// Allow empty message if just loading quick replies (for initial UI load)
$loadQuickOnly = ($message === '' && $intent === '' && empty($input['faq_key']));

$pdo = getDBConnection();

// Get categories for context
function getCategories(PDO $pdo): array {
    $stmt = $pdo->query("SELECT category_id, name, description FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Enhanced FAQ data loaded from database + static
function getFAQs(PDO $pdo): array {
    $faqs = [
        'shipping' => "We ship nationwide across the Philippines. Standard delivery takes 3-5 business days. Shipping fees vary by region (Metro Manila: ₱50, North/South Luzon: ₱50-60, Visayas: ₱100, Mindanao: ₱80).",
        'returns' => "You can request a return within 7 days of delivery if items are unused and in original packaging. Contact our support team to initiate a return.",
        'payment methods' => "We support PayPal and Cash on Delivery (COD). For COD, payment is made upon delivery.",
        'order status' => "Track your order status in 'My Orders'. Statuses include: Pending → Processing → Shipped → Delivered. You'll receive notifications for each update.",
        'categories' => "We offer three main categories:\n1. Gym Accessories (Lifting Gear, Recovery Tools, Hydration & Storage)\n2. Gym Equipments (Weights, Calisthenic Equipment, Mobility Tools)\n3. Gym Supplements (Protein Powders, Pre-workout Boosters, Vitamins)",
        'supplements' => "We offer Gym Supplements including protein powders (whey, casein, plant-based), pre-workout boosters, and vitamins. Browse our supplement category for all available products.",
        'equipment' => "We offer Gym Equipments including weights (dumbbells, kettlebells, plates), calisthenic equipment (jump ropes, pull-up bars), and mobility tools (foam rollers, yoga mats).",
        'accessories' => "We offer Gym Accessories including lifting gear (gloves, straps, belts), recovery tools (massage guns, compression sleeves), and hydration & storage (shaker bottles, meal prep boxes).",
    ];
    
    // Try to load additional FAQs from CMS
    try {
        $stmt = $pdo->prepare("SELECT title, description FROM contents WHERE status='published' AND (type='faq' OR placement='faq') ORDER BY updated_at DESC LIMIT 20");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $q = strtolower(trim($row['title'] ?? ''));
            $a = trim($row['description'] ?? '');
            if ($q && $a && !isset($faqs[$q])) {
                // Extract key terms for matching
                $faqs[$q] = $a;
            }
        }
    } catch (Throwable $e) {
        // Continue with static FAQs
    }
    
    return $faqs;
}

// Smart product search with category awareness
function searchProducts(PDO $pdo, string $q, ?int $categoryId = null): array {
    $q = trim($q);
    if ($q === '') return [];
    
    // Category mappings
    $categoryKeywords = [
        1 => ['accessories', 'accessory', 'gear', 'gloves', 'straps', 'belt', 'recovery', 'shaker', 'bottle', 'bag', 'duffle'],
        2 => ['equipment', 'equipments', 'weights', 'dumbbell', 'kettlebell', 'plate', 'barbell', 'jump rope', 'pull-up', 'calisthenic', 'foam roller', 'yoga mat'],
        3 => ['supplements', 'supplement', 'protein', 'whey', 'casein', 'pre-workout', 'preworkout', 'vitamin', 'multivitamin', 'bcaa', 'fat burner'],
    ];
    
    // Try to detect category from query
    if (!$categoryId) {
        $lower = strtolower($q);
        foreach ($categoryKeywords as $catId => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lower, $keyword) !== false) {
                    $categoryId = $catId;
                    break 2;
                }
            }
        }
    }
    
    $sql = "SELECT p.product_id, p.name, p.price, p.description, p.status, 
                   c.name AS category_name, c.category_id,
                   sc.name AS subcategory_name,
                   CASE 
                       WHEN p.sale_percentage > 0 THEN p.price * (1 - p.sale_percentage / 100)
                       ELSE p.price
                   END AS final_price
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.subcategory_id
            WHERE p.status = 'active'";
    
    $params = [];
    $conditions = [];
    
    if ($categoryId) {
        $conditions[] = "p.category_id = ?";
        $params[] = $categoryId;
    }
    
    // Enhanced search - name, description, category, subcategory
    $searchConditions = [
        "p.name LIKE ?",
        "p.description LIKE ?",
        "c.name LIKE ?",
        "sc.name LIKE ?"
    ];
    
    $like = '%' . $q . '%';
    foreach ($searchConditions as $cond) {
        $conditions[] = $cond;
        $params[] = $like;
    }
    
    $sql .= " AND (" . implode(' OR ', $conditions) . ")";
    $sql .= " ORDER BY 
                CASE WHEN p.name LIKE ? THEN 1 ELSE 2 END,
                p.is_best_seller DESC,
                p.is_popular DESC,
                p.name ASC
            LIMIT 10";
    $params[] = $q . '%'; // Exact match boost
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Intelligent response generation
function generateResponse(PDO $pdo, string $message): array {
    $lower = strtolower($message);
    $replyParts = [];
    $products = [];
    $categories = getCategories($pdo);
    $faqs = getFAQs($pdo);
    
    // Check for category queries
    if (preg_match('/\b(equipment|equipments|weights?|dumbbell|kettlebell|barbell|calisthenic|pull.?up|jump.?rope|mobility)\b/i', $lower)) {
        $products = searchProducts($pdo, $message, 2);
        if (empty($products)) {
            $replyParts[] = "We offer Gym Equipments including weights, calisthenic equipment, and mobility tools. You can browse our equipment category at shop.php?category=2";
        }
    } elseif (preg_match('/\b(supplement|protein|whey|casein|pre.?workout|preworkout|vitamin|multivitamin|bcaa|fat.?burner)\b/i', $lower)) {
        $products = searchProducts($pdo, $message, 3);
        if (empty($products)) {
            $replyParts[] = "We offer Gym Supplements including protein powders, pre-workout boosters, and vitamins. You can browse our supplements at shop.php?category=3";
        }
    } elseif (preg_match('/\b(accessories?|gear|gloves?|straps?|belt|recovery|shaker|bottle|bag|duffle)\b/i', $lower)) {
        $products = searchProducts($pdo, $message, 1);
        if (empty($products)) {
            $replyParts[] = "We offer Gym Accessories including lifting gear, recovery tools, and hydration & storage. Browse at shop.php?category=1";
        }
    } else {
        // General product search
        $products = searchProducts($pdo, $message);
    }
    
    // Handle common non-product queries
    $categoryMatches = [
        'what.*categories?.*you.*have' => 'categories',
        'what.*do.*you.*sell' => 'categories',
        'what.*products?' => 'categories',
        'supplements?' => 'supplements',
        'equipment' => 'equipment',
        'accessories?' => 'accessories',
    ];
    
    foreach ($categoryMatches as $pattern => $faqKey) {
        if (preg_match('/' . $pattern . '/i', $lower)) {
            if (isset($faqs[$faqKey])) {
                $replyParts[] = $faqs[$faqKey];
            }
            break;
        }
    }
    
    // FAQ matching (enhanced)
    foreach ($faqs as $key => $answer) {
        if ($key !== 'categories' && $key !== 'supplements' && $key !== 'equipment' && $key !== 'accessories') {
            $keywords = explode(' ', $key);
            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 3 && strpos($lower, $keyword) !== false) {
                    $replyParts[] = $answer;
                    break;
                }
            }
        }
    }
    
    // Product results
    if (!empty($products)) {
        $lines = ["I found " . count($products) . " product(s) matching your search:"];
        foreach ($products as $p) {
            $finalPrice = isset($p['final_price']) ? (float)$p['final_price'] : (float)$p['price'];
            $originalPrice = (float)$p['price'];
            $url = 'product_detail.php?product_id=' . urlencode($p['product_id']);
            $priceText = number_format($finalPrice, 2);
            if ($finalPrice < $originalPrice) {
                $priceText .= ' (was ₱' . number_format($originalPrice, 2) . ')';
            }
            $categoryText = $p['category_name'] ? " ({$p['category_name']})" : "";
            $lines[] = sprintf("• %s%s - ₱%s\n  %s", 
                htmlspecialchars($p['name']), 
                $categoryText,
                $priceText,
                $url
            );
        }
        $replyParts[] = implode("\n", $lines);
    } elseif ($message !== '' && empty($replyParts)) {
        // No products found - helpful response
        $lowerMsg = strtolower($message);
        if (preg_match('/\b(creatine|steroid|anabolic|testosterone)\b/i', $lowerMsg)) {
            $replyParts[] = "I don't see that product in our inventory. We currently offer:\n• Gym Equipments (weights, calisthenic equipment, mobility tools)\n• Gym Supplements (protein powders, pre-workout, vitamins)\n• Gym Accessories (lifting gear, recovery tools, hydration)\n\nTry searching for 'protein', 'dumbbells', or 'gloves' instead. You can also browse by category in our shop!";
        } else {
            $replyParts[] = "I couldn't find products matching '" . htmlspecialchars($message) . "'.\n\nWe offer:\n• Gym Equipments\n• Gym Supplements\n• Gym Accessories\n\nTry searching for specific items like 'protein', 'weights', or 'gloves', or browse by category in our shop!";
        }
    }
    
    if (empty($replyParts) && !$loadQuickOnly) {
        $replyParts[] = "I can help you find products, answer questions about categories, shipping, returns, payment methods, and order status.\n\nTry asking:\n• 'What supplements do you have?'\n• 'Show me equipment'\n• 'Shipping information'\n• Or search for a specific product!";
    }
    
    return [
        'reply' => implode("\n\n", $replyParts),
        'products' => $products
    ];
}

// Quick intent routing for FAQ buttons
if ($intent === 'faq' || isset($input['faq_key'])) {
    $key = strtolower((string)($input['faq_key'] ?? ''));
    $faqs = getFAQs($pdo);
    
    if (isset($faqs[$key])) {
        echo json_encode([
            'success' => true, 
            'reply' => $faqs[$key],
            'quick_replies' => [
                ['label' => 'Shop Categories', 'faq_key' => 'categories'],
                ['label' => 'Shipping Info', 'faq_key' => 'shipping'],
                ['label' => 'Returns Policy', 'faq_key' => 'returns'],
                ['label' => 'Payment Methods', 'faq_key' => 'payment methods'],
            ]
        ]);
        exit;
    }
}

// Main message processing
$response = generateResponse($pdo, $message);

echo json_encode([
    'success' => true,
    'reply' => $loadQuickOnly ? '' : $response['reply'],
    'quick_replies' => [
        ['label' => 'Shop Categories', 'faq_key' => 'categories'],
        ['label' => 'Shipping Info', 'faq_key' => 'shipping'],
        ['label' => 'Returns Policy', 'faq_key' => 'returns'],
        ['label' => 'Payment Methods', 'faq_key' => 'payment methods'],
    ]
]);
?>

<?php
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pageTitle = 'FAQs';

// Default FAQs
$faqs = [
	['q' => 'What types of supplements do you offer?', 'a' => "We offer a wide range of gym supplements, including protein powders, pre-workouts, BCAAs, fat burners, multivitamins, and more to support your fitness goals."],
	['q' => 'Are your supplements safe to use?', 'a' => "Yes, all our supplements are sourced from reputable brands and undergo strict quality control to ensure safety and effectiveness."],
	['q' => 'How do I choose the right supplement for my fitness goals?', 'a' => "Our product descriptions provide detailed benefits and usage recommendations. You can also reach out to our support team for personalized advice."],
	['q' => 'Do you offer discounts or promotions?', 'a' => "We regularly run promotions and offer discounts for first-time buyers, bulk purchases, and loyal customers. Check our website or subscribe to our newsletter for updates."],
	['q' => 'What payment methods do you accept?', 'a' => "We accept major credit/debit cards, digital wallets, and bank transfers. More payment options may be available depending on your location."],
	['q' => 'How long does shipping take?', 'a' => "Shipping times vary by location. Typically, orders are delivered within 3–7 business days for domestic shipping and 7–14 business days for international orders."],
	['q' => 'Do you ship internationally?', 'a' => "Yes! We offer international shipping to select countries. Shipping fees and delivery times vary based on your location."],
	['q' => 'Can I return or exchange a product?', 'a' => "We accept returns or exchanges within 7 days of delivery, provided the product is unopened and in its original condition. See our return policy for details."],
	['q' => 'Are your supplements FDA-approved?', 'a' => "We only carry products that comply with industry safety standards. However, regulatory approvals may vary by country. Please check individual product labels for certifications."],
	['q' => 'Are your supplements FDA-approved?', 'a' => "You can reach us via email at support@fitfuel.com, through our live chat, or by calling our hotline during business hours."],
];

// Load FAQs from CMS contents table
try {
	$pdo = getDBConnection();
	$stmt = $pdo->prepare("SELECT title, description FROM contents WHERE status='published' AND (type='faq' OR placement='faq') ORDER BY updated_at DESC LIMIT 100");
	$stmt->execute();
	$rows = $stmt->fetchAll();
	$tmp = [];
	foreach ($rows as $row) {
		$title = trim((string)$row['title']);
		$desc  = trim((string)$row['description']);
		if ($title !== '' && $desc !== '') { $tmp[] = ['q' => $title, 'a' => $desc]; }
	}
	if (!empty($tmp)) { $faqs = $tmp; }
} catch (Throwable $e) {
	// fallback to defaults
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($pageTitle); ?> - FitFuel</title>
	<link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css">
	<style>
		.faq-card { border: 1px solid #e5e7eb; }
		.faq-hero { background-image: url('img/Banner/3.png'); background-size: cover; background-position: center; }
		.faq-question { font-family: monospace; }
	</style>
</head>
<body class="font-body bg-white text-slate-700">
	<!-- Header (same as contact/about/index) -->
	<nav class="bg-white text-black py-2">
		<div class="container mx-auto px-4">
			<div class="flex justify-end space-x-6 text-sm">
				<a href="testimonials.php" class="hover:text-emerald-400 transition-colors">Review</a>
				<a href="contact.php" class="hover:text-emerald-400 transition-colors">Help</a>
                <?php if (empty($_SESSION['user_id'])): ?>
					<a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
				<?php endif; ?>
			</div>
		</div>
	</nav>
	<nav class="sticky top-0 z-50 bg-black border-b border-white py-4 backdrop-blur">
		<div class="container mx-auto px-4">
			<div class="flex items-center justify-between">
				<a href="index.php" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO"></a>
				<div class="hidden md:flex space-x-8">
					<a href="shop.php?category=1" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Accessories</a>
					<a href="shop.php?category=3" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Supplements</a>
					<a href="shop.php?category=2" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Equipment</a>
				</div>
				<div class="flex items-center space-x-4">
					<form method="GET" action="shop.php" class="relative hidden md:block">
						<input type="text" name="search" placeholder="Search products..." class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
						<button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-600"><i class="fas fa-search"></i></button>
					</form>
                    <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors"><i class="fas fa-shopping-cart text-xl"></i></a>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="relative" id="profileMenu">
                        <button id="profileBtn"
                                class="p-2 text-white hover:text-emerald-600 transition-colors rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user text-xl"></i>
                        </button>
                        <div id="profileDropdown"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                             role="menu" aria-labelledby="profileBtn" style="display: none;">
                            <a href="profile.php"   class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Account</a>
                            <a href="my_orders.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Purchase</a>
                            <a href="wishlist.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Wishlist</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="p-2 text-white hover:text-emerald-600 transition-colors"><i class="fas fa-user text-xl"></i></a>
                    <?php endif; ?>
				</div>
			</div>
		</div>
	</nav>

	<!-- Hero with search -->
	<section class="faq-hero relative h-64 flex items-center justify-center text-center text-white">
		<div class="absolute inset-0 bg-black/60"></div>
		<div class="relative z-10 w-full max-w-3xl px-4">
			<h1 class="text-3xl md:text-5xl font-extrabold tracking-wide mb-6">HOW CAN WE HELP?</h1>
			<div class="relative">
				<input id="faqSearch" type="text" placeholder="Search" class="w-full px-4 py-3 rounded bg-white/90 text-black focus:outline-none">
				<i class="fas fa-search absolute right-4 top-3.5 text-black/60"></i>
			</div>
		</div>
	</section>

	<section class="py-12">
		<div class="container mx-auto px-4 max-w-5xl">
			<h2 class="text-center text-2xl md:text-3xl font-bold text-black mb-8">Frequently Asked Question</h2>
			<div id="faqList" class="space-y-4">
				<?php foreach ($faqs as $i => $item): ?>
				<div class="faq-card rounded">
					<button class="w-full text-left px-4 py-3 font-semibold faq-question" onclick="toggleFaq(<?php echo $i; ?>)">
						<?php echo ($i+1) . '. ' . htmlspecialchars($item['q']); ?>
					</button>
					<div id="faq-<?php echo $i; ?>" class="px-4 pb-4 text-sm leading-relaxed hidden">
						<?php echo nl2br(htmlspecialchars($item['a'])); ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="text-xs text-center text-gray-500 mt-6">If you have any question or concerns, please contact us at <a class="underline" href="mailto:siafitfuel@gmail.com">siafitfuel@gmail.com</a></div>
		</div>
	</section>

	<?php include 'includes/footer.php'; ?>

	<script>
    // Profile dropdown (copied behavior from testimonials.php)
    (function(){
        function init(){
            var btn  = document.getElementById('profileBtn');
            var menu = document.getElementById('profileDropdown');
            var container = document.getElementById('profileMenu');
            if (!btn || !menu) return;
            function close(){ menu.style.display = 'none'; btn.setAttribute('aria-expanded','false'); }
            function open(){ menu.style.display = 'block'; btn.setAttribute('aria-expanded','true'); }
            btn.addEventListener('click', function(e){ e.stopPropagation(); menu.style.display === 'none' ? open() : close(); });
            document.addEventListener('click', function(e){ if (!container.contains(e.target)) close(); });
            document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    })();
	</script>

	<script>
	function toggleFaq(i){
		const el = document.getElementById('faq-' + i);
		if (!el) return;
		el.classList.toggle('hidden');
	}
	const search = document.getElementById('faqSearch');
	search && search.addEventListener('input', function(){
		const q = this.value.toLowerCase();
		document.querySelectorAll('#faqList .faq-card').forEach(card => {
			const t = card.innerText.toLowerCase();
			card.style.display = t.includes(q) ? '' : 'none';
		});
	});
	</script>
</body>
</html>



<?php
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pageTitle = 'Contact Us';

// Pull contact info from CMS if available
$contactInfo = [
	'email' => 'siafitfuel@gmail.com',
	'address' => 'Anonas LRT, Aurora Blvd, Quezon City',
	'phone' => '09123456789',
	'facebook' => '#',
	'twitter' => '#',
	'instagram' => '#',
	'tiktok' => '#',
];

try {
	$pdo = getDBConnection();
	$stmt = $pdo->prepare("SELECT title, description, image, image_path FROM contents WHERE status='published' AND (type='contact' OR placement='contact') ORDER BY updated_at DESC LIMIT 5");
	$stmt->execute();
	$items = $stmt->fetchAll();
	foreach ($items as $it) {
		$desc = trim((string)($it['description'] ?? ''));
		if (!$desc) continue;
		// Key=Value lines
		foreach (preg_split('/\r?\n/', $desc) as $line) {
			if (strpos($line, ':') === false) continue;
			[$k, $v] = array_map('trim', explode(':', $line, 2));
			$lk = strtolower($k);
			if (isset($contactInfo[$lk]) && $v !== '') { $contactInfo[$lk] = $v; }
		}
	}
} catch (Throwable $e) {
	// ignore; defaults remain
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
</head>
<body class="font-body bg-white text-slate-700">
    <!-- First Navigation Bar -->
    <nav class="bg-white text-black py-2">
      <div class="container mx-auto px-4">
        <div class="flex justify-end space-x-6 text-sm">
          <a href="testimonials.php" class="hover:text-emerald-400 transition-colors">Review</a>
          <a href="faq.php" class="hover:text-emerald-400 transition-colors">Help</a>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="logout.php" class="hover:text-emerald-400 transition-colors">Logout</a>
          <?php else: ?>
            <a href="login.php" class="hover:text-emerald-400 transition-colors">Login</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Second Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-black border-b border-white py-4 backdrop-blur">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <a href="index.php" class="flex items-center">
            <img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
          </a>

          <!-- Primary categories -->
          <div class="hidden md:flex space-x-8">
            <a href="shop.php?category=1" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Accessories</a>
            <a href="shop.php?category=3" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Supplements</a>
            <a href="shop.php?category=2" class="font-medium text-white hover:text-emerald-600 transition-colors">Gym Equipment</a>
          </div>

          <!-- Search and Icons -->
          <div class="flex items-center space-x-4">
            <!-- Search -->
            <form method="GET" action="shop.php" class="relative hidden md:block">
              <input type="text" name="search" placeholder="Search products..." class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <button type="submit" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-600">
                <i class="fas fa-search"></i>
              </button>
            </form>

            <!-- Bell -->
            <button class="relative p-2 text-white hover:text-emerald-600 transition-colors">
              <i class="fas fa-bell text-xl"></i>
            </button>

            <!-- Cart -->
            <?php
            $cart_count = 0;
            if (!empty($_SESSION['user_id'])) {
              try {
                $cart_sql = "SELECT COALESCE(SUM(ci.quantity), 0) AS count
                             FROM cart c
                             LEFT JOIN cart_items ci ON c.cart_id = ci.cart_id
                             WHERE c.user_id = ?";
                $cart_stmt = $pdo->prepare($cart_sql);
                $cart_stmt->execute([$_SESSION['user_id']]);
                $cart_count = (int)($cart_stmt->fetchColumn() ?: 0);
              } catch (PDOException $e) {
                $cart_count = 0;
              }
            }
            ?>
            <a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
              <i class="fas fa-shopping-cart text-xl"></i>
              <?php if ($cart_count > 0): ?>
                <span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                  <?php echo $cart_count; ?>
                </span>
              <?php endif; ?>
            </a>

            <!-- Profile dropdown -->
            <div class="relative" id="profileMenu">
              <button id="profileBtn"
                  class="p-2 text-white hover:text-emerald-600 transition-colors rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user text-xl"></i>
              </button>
              <div id="profileDropdown"
                   class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                   role="menu" aria-labelledby="profileBtn" style="display: none;">
                <?php if (!empty($_SESSION['user_id'])): ?>
                  <a href="profile.php"   class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Account</a>
                  <a href="my_orders.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Purchase</a>
                  <a href="wishlist.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Wishlist</a>
                <?php else: ?>
                  <a href="login.php"         class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Login</a>
                  <a href="registration.php"  class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Create Account</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

	<section class="py-16">
		<div class="container mx-auto px-4 max-w-6xl">
			<h1 class="text-4xl md:text-5xl font-bold text-black mb-10">Contact Us</h1>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-12">
				<!-- Form -->
				<div>
					<form id="contactForm" method="POST" action="contact_submit.php" class="space-y-6" onsubmit="return submitContact(event, this);">
						<div>
							<label class="block text-sm font-semibold mb-2">Full Name</label>
							<input type="text" name="name" required placeholder="Enter name" class="w-full px-4 py-2 border border-gray-300 rounded" />
						</div>
						<div>
							<label class="block text-sm font-semibold mb-2">Email Address</label>
							<input type="email" name="email" required placeholder="Enter email address" class="w-full px-4 py-2 border border-gray-300 rounded" />
						</div>
						<div>
							<label class="block text-sm font-semibold mb-2">Telephone Number</label>
							<input type="text" name="phone" placeholder="Enter telephone number" class="w-full px-4 py-2 border border-gray-300 rounded" />
						</div>
						<div>
							<label class="block text-sm font-semibold mb-2">Write Your Message</label>
							<textarea name="message" required rows="6" class="w-full px-4 py-2 border border-gray-300 rounded" placeholder="Message"></textarea>
						</div>
						<button type="submit" class="px-6 py-2 bg-black text-white rounded hover:bg-gray-800">Send Message</button>
					</form>
				</div>

				<!-- Contact Info -->
				<div>
					<h2 class="text-2xl font-semibold text-black mb-6">Connect With Us</h2>
					<div class="space-y-6">
						<div class="flex items-start space-x-3">
							<i class="far fa-envelope mt-1"></i>
							<div>
								<div class="font-semibold">Email</div>
								<a href="mailto:<?php echo htmlspecialchars($contactInfo['email']); ?>" class="underline"><?php echo htmlspecialchars($contactInfo['email']); ?></a>
							</div>
						</div>
						<div class="flex items-start space-x-3">
							<i class="fas fa-location-dot mt-1"></i>
							<div>
								<div class="font-semibold">Address</div>
								<div><?php echo htmlspecialchars($contactInfo['address']); ?></div>
							</div>
						</div>
						<div class="flex items-start space-x-3">
							<i class="fas fa-phone mt-1"></i>
							<div>
								<div class="font-semibold">Telephone</div>
								<a href="tel:<?php echo htmlspecialchars($contactInfo['phone']); ?>" class="underline"><?php echo htmlspecialchars($contactInfo['phone']); ?></a>
							</div>
						</div>

						<h3 class="text-xl font-semibold text-black mt-6">Follow Us On</h3>
						<ul class="space-y-3 mt-2">
							<li class="flex items-center space-x-3"><i class="fab fa-facebook"></i><a href="<?php echo htmlspecialchars($contactInfo['facebook']); ?>" class="underline">Facebook</a></li>
							<li class="flex items-center space-x-3"><i class="fab fa-twitter"></i><a href="<?php echo htmlspecialchars($contactInfo['twitter']); ?>" class="underline">Twitter</a></li>
							<li class="flex items-center space-x-3"><i class="fab fa-instagram"></i><a href="<?php echo htmlspecialchars($contactInfo['instagram']); ?>" class="underline">Instagram</a></li>
							<li class="flex items-center space-x-3"><i class="fab fa-tiktok"></i><a href="<?php echo htmlspecialchars($contactInfo['tiktok']); ?>" class="underline">TikTok</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php include 'includes/footer.php'; ?>

	<script>
	async function submitContact(e, form){
		e.preventDefault();
		const fd = new FormData(form);
		const res = await fetch('contact_submit.php', { method: 'POST', body: fd });
		try { const data = await res.json(); alert(data.message); } catch (err) { alert('Thanks! We will get back to you.'); }
		form.reset();
		return false;
	}
	</script>
</body>
</html>



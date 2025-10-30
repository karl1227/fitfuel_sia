<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>FitFuel</title>
	<link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<!-- Google Fonts - Load before CSS for better performance -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="css/style.css">
</head>
<body class="font-body bg-white text-slate-600">
<nav class="sticky-nav bg-black border-b border-white py-4">
	<div class="container mx-auto px-4">
		<div class="flex items-center justify-between">
			<a href="index.php" class="flex items-center">
				<img src="img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
			</a>
			<div class="hidden md:flex space-x-6">
				<a href="shop.php" class="text-white hover:text-emerald-500">Shop</a>
				<a href="aboutus.php" class="text-white hover:text-emerald-500">About</a>
				<a href="faq.php" class="text-white hover:text-emerald-500">FAQ</a>
				<a href="testimonials.php" class="text-white hover:text-emerald-500">Testimonials</a>
				<a href="contact.php" class="text-white hover:text-emerald-500">Contact</a>
			</div>
			<div class="flex items-center space-x-4">
				<?php if (isset($_SESSION['user_id'])): ?>
				<div class="relative">
					<button id="notif-bell" class="relative p-2.5 text-white hover:text-emerald-600 transition-all duration-200 hover:scale-110" aria-label="Notifications" title="Notifications">
						<i class="fas fa-bell text-xl"></i>
						<span id="notif-count" class="hidden absolute top-0 right-0 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center transform translate-x-1/2 -translate-y-1/2 shadow-lg">0</span>
					</button>
					<div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-96 bg-white text-slate-800 rounded-lg shadow-2xl border border-gray-200 z-[9999] overflow-hidden">
						<div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
							<span class="font-semibold text-gray-900 text-base flex items-center">
								<i class="fas fa-bell mr-2 text-emerald-600"></i>
								Notifications
							</span>
							<button id="notif-mark-all" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium px-2 py-1 rounded hover:bg-emerald-50 transition-colors">
								Mark all read
							</button>
						</div>
						<div id="notif-list" class="max-h-96 overflow-y-auto">
							<div class="px-4 py-8 text-center text-gray-500">
								<i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
								<p class="text-sm">Loading notifications...</p>
							</div>
						</div>
						<div class="px-4 py-2 border-t border-gray-200 bg-gray-50 text-center">
							<a href="my_orders.php" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View all orders</a>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<a href="cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
					<i class="fas fa-shopping-cart text-xl"></i>
				</a>
				<?php if (isset($_SESSION['user_id'])): ?>
				<div class="relative">
					<button id="profileBtn" class="p-2 text-white hover:text-emerald-600 transition-colors flex items-center gap-2" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-user text-xl"></i>
						<i class="fas fa-chevron-down text-xs"></i>
					</button>
					<div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white text-slate-800 rounded shadow-lg z-[9999]">
						<a href="profile.php" class="block px-4 py-2 text-sm hover:bg-slate-50">My Profile</a>
						<a href="my_orders.php" class="block px-4 py-2 text-sm hover:bg-slate-50">My Orders</a>
						<div class="border-t"></div>
						<a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
					</div>
				</div>
				<?php else: ?>
				<a href="login.php" class="p-2 text-white hover:text-emerald-600 transition-colors">
					<i class="fas fa-user text-xl"></i>
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</nav>


<script>
// Lightweight profile dropdown toggle for all pages
(function(){
	function initProfile(){
		var btn = document.getElementById('profileBtn');
		var dd  = document.getElementById('profileDropdown');
		if (!btn || !dd) return;
		function close(){ dd.classList.add('hidden'); btn.setAttribute('aria-expanded','false'); }
		function open(){ dd.classList.remove('hidden'); btn.setAttribute('aria-expanded','true'); }
		btn.addEventListener('click', function(e){
			e.preventDefault(); e.stopPropagation();
			(dd.classList.contains('hidden') ? open() : close());
		});
		document.addEventListener('click', function(e){
			if (!dd.contains(e.target) && !btn.contains(e.target)) close();
		});
		document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initProfile); else initProfile();
})();
</script>


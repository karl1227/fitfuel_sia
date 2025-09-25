<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>FitFuel</title>
	<link rel="icon" href="/img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<!-- Google Fonts - Load before CSS for better performance -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-body bg-white text-slate-600">
<nav class="sticky-nav bg-black border-b border-white py-4">
	<div class="container mx-auto px-4">
		<div class="flex items-center justify-between">
			<a href="/index.php" class="flex items-center">
				<img src="/img/LOGO-Fitfuel.png" width="75" height="auto" alt="LOGO">
			</a>
			<div class="hidden md:flex space-x-6">
				<a href="/shop.php" class="text-white hover:text-emerald-500">Shop</a>
				<a href="/aboutus.php" class="text-white hover:text-emerald-500">About</a>
				<a href="/faq.php" class="text-white hover:text-emerald-500">FAQ</a>
				<a href="/contact.php" class="text-white hover:text-emerald-500">Contact</a>
			</div>
			<div class="flex items-center space-x-4">
				<a href="/cart.php" class="relative p-2 text-white hover:text-emerald-600 transition-colors">
					<i class="fas fa-shopping-cart text-xl"></i>
				</a>
				<a href="/login.php" class="p-2 text-white hover:text-emerald-600 transition-colors">
					<i class="fas fa-user text-xl"></i>
				</a>
			</div>
		</div>
	</div>
</nav>



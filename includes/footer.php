<!-- Newsletter CTA -->
<section class="py-16 bg-emerald-600">
	<div class="container mx-auto px-4 text-center">
		<h2 class="font-heading text-3xl font-bold text-white mb-4">Stay Updated</h2>
		<p class="text-emerald-100 mb-8 text-lg">Get the latest fitness tips, product updates, and exclusive offers</p>
	<form method="POST" action="subscribe_newsletter.php" class="max-w-md mx-auto flex" onsubmit="return submitNewsletter(event, this);">
		<input name="email" type="email" required placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-300">
		<button type="submit" class="bg-slate-800 text-white px-6 py-3 rounded-r-lg hover:bg-slate-700 transition-colors">Subscribe</button>
	</form>
	</div>
</section>

<!-- Footer -->
<footer class="bg-slate-800 text-white py-12">
	<div class="container mx-auto px-4">
		<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
			<div>
				<h3 class="font-heading text-2xl font-bold text-white mb-4">FitFuel</h3>
				<p class="text-slate-300 mb-4">Your destination for premium fitness equipment, supplements, and accessories.</p>
				<div class="flex space-x-4">
					<a href="https://facebook.com" class="text-slate-300 hover:text-emerald-400 transition-colors" aria-label="Facebook"><i class="fab fa-facebook text-xl"></i></a>
					<a href="https://instagram.com" class="text-slate-300 hover:text-emerald-400 transition-colors" aria-label="Instagram"><i class="fab fa-instagram text-xl"></i></a>
					<a href="https://twitter.com" class="text-slate-300 hover:text-emerald-400 transition-colors" aria-label="Twitter"><i class="fab fa-twitter text-xl"></i></a>
					<a href="https://youtube.com" class="text-slate-300 hover:text-emerald-400 transition-colors" aria-label="YouTube"><i class="fab fa-youtube text-xl"></i></a>
				</div>
			</div>
			<div>
				<h4 class="font-semibold text-lg mb-4">Quick Links</h4>
				<ul class="space-y-2">
					<li><a href="aboutus.php" class="text-slate-300 hover:text-emerald-400 transition-colors">About Us</a></li>
					<li><a href="contact.php" class="text-slate-300 hover:text-emerald-400 transition-colors">Contact</a></li>
					<li><a href="testimonials.php" class="text-slate-300 hover:text-emerald-400 transition-colors">Reviews</a></li>
					<li><a href="faq.php" class="text-slate-300 hover:text-emerald-400 transition-colors">FAQs</a></li>
				</ul>
			</div>
			<div>
				<h4 class="font-semibold text-lg mb-4">Categories</h4>
				<ul class="space-y-2">
					<li><a href="shop.php?category=2" class="text-slate-300 hover:text-emerald-400 transition-colors">Equipment</a></li>
					<li><a href="shop.php?category=3" class="text-slate-300 hover:text-emerald-400 transition-colors">Supplements</a></li>
					<li><a href="shop.php?category=1" class="text-slate-300 hover:text-emerald-400 transition-colors">Accessories</a></li>
					<li><a href="shop.php" class="text-slate-300 hover:text-emerald-400 transition-colors">All Products</a></li>
				</ul>
			</div>
			<div>
				<h4 class="font-semibold text-lg mb-4">Customer Service</h4>
				<ul class="space-y-2">
					<li><a href="faq.php" class="text-slate-300 hover:text-emerald-400 transition-colors">Shipping Info</a></li>
					<li><a href="faq.php#returns" class="text-slate-300 hover:text-emerald-400 transition-colors">Returns</a></li>
					<li><a href="faq.php" class="text-slate-300 hover:text-emerald-400 transition-colors">Size Guide</a></li>
					<li><a href="my_orders.php" class="text-slate-300 hover:text-emerald-400 transition-colors">Track Order</a></li>
				</ul>
			</div>
		</div>
		<div class="border-t border-slate-700 mt-8 pt-8 text-center">
			<p class="text-slate-300">&copy; <?php echo date('Y'); ?> FitFuel. All rights reserved.</p>
		</div>
	</div>
</footer>

<script src="JS/index.js"></script>
<script>
	async function submitNewsletter(e, form){
		e.preventDefault();
		const fd = new FormData(form);
		const res = await fetch('subscribe_newsletter.php', { method:'POST', body: fd });
		try { 
			const data = await res.json(); 
			alert(data.message); 
		} catch(e){ 
			alert('Thank you for subscribing!'); 
		}
		form.reset();
		return false;
	}
</script>
</body>
</html>



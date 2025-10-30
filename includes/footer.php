<?php
// Hide newsletter on checkout page
$current_page = basename($_SERVER['PHP_SELF']);
$hide_newsletter = ($current_page === 'checkout.php');
?>
<?php if (!$hide_newsletter): ?>
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
<?php endif; ?>

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
<script>
// Notifications client
(function(){
	// Wait for DOM to be ready
	function initNotifications() {
		const bell = document.getElementById('notif-bell');
		const dropdown = document.getElementById('notif-dropdown');
		const list = document.getElementById('notif-list');
		const countEl = document.getElementById('notif-count');
		const markAllBtn = document.getElementById('notif-mark-all');
		
		if (!bell || !dropdown) {
			console.warn('Notification elements not found. Bell:', !!bell, 'Dropdown:', !!dropdown);
			return;
		}

		let open = false;
		function render(notifs){
			if (!list) return;
			list.innerHTML = '';
			if (!notifs || !notifs.length){
				list.innerHTML = '<div class="px-4 py-12 text-center"><i class="fas fa-bell-slash text-3xl text-gray-300 mb-3"></i><p class="text-sm text-gray-500">No notifications</p></div>';
				return;
			}
			notifs.forEach(n => {
				const a = document.createElement('a');
				a.href = n.link || '#';
				const isUnread = parseInt(n.is_read) === 0;
			a.className = 'block px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors ' + (isUnread ? 'bg-emerald-50 border-l-4 border-l-emerald-500' : '');
			const icon = n.type === 'order_status' ? 'fa-shopping-cart' : 
						n.type === 'promotion' ? 'fa-tag' : 
						n.type === 'system' ? 'fa-info-circle' : 'fa-bell';
			const iconColor = isUnread ? 'text-emerald-600' : 'text-gray-400';
			a.innerHTML = `<div class="flex items-start gap-3">
				<div class="flex-shrink-0 mt-0.5">
					<i class="fas ${icon} ${iconColor}"></i>
				</div>
				<div class="flex-1 min-w-0">
					<div class="text-sm font-semibold text-gray-900 mb-1">${escapeHtml(n.title)}</div>
					<div class="text-sm text-gray-600 mb-1">${escapeHtml(n.message)}</div>
					<div class="text-xs text-gray-400">${new Date(n.created_at).toLocaleString()}</div>
				</div>
				${isUnread ? '<div class="flex-shrink-0"><span class="inline-block w-2 h-2 bg-emerald-500 rounded-full"></span></div>' : ''}
			</div>`;
			a.addEventListener('click', (e) => {
				if (n.link && n.link !== '#') {
					markRead(n.notification_id);
				} else {
					e.preventDefault();
					markRead(n.notification_id);
				}
			});
			list.appendChild(a);
		});
	}

		async function fetchNotifs(){
		try{
			const res = await fetch('get_notifications.php');
			const data = await res.json();
			if (data && data.success){
				render(data.notifications||[]);
				const unread = data.unread || 0;
				if (unread > 0 && countEl){
					countEl.textContent = unread > 99 ? '99+' : unread;
					countEl.classList.remove('hidden');
					// Add animation for new notifications
					countEl.classList.add('animate-pulse');
					setTimeout(() => countEl.classList.remove('animate-pulse'), 2000);
				} else if (countEl) {
					countEl.classList.add('hidden');
				}
			}
		}catch(e){
			console.error('Failed to fetch notifications:', e);
			if (list) {
				list.innerHTML = '<div class="px-4 py-12 text-center"><i class="fas fa-exclamation-triangle text-3xl text-red-300 mb-3"></i><p class="text-sm text-gray-500">Failed to load notifications</p></div>';
			}
		}
	}

		async function markRead(id){
			try{
				await fetch('mark_notification_read.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({notification_id: id})});
				fetchNotifs();
			}catch(e){}
	}

		if (markAllBtn){
			markAllBtn.addEventListener('click', async (e)=>{
				e.preventDefault();
				try{
					await fetch('mark_notification_read.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({all: true})});
					fetchNotifs();
				}catch(e){}
			});
	}

		bell.addEventListener('click', (e)=>{
			e.preventDefault();
			e.stopPropagation();
			open = !open;
			dropdown.classList.toggle('hidden', !open);
			if (open) fetchNotifs();
		});

		document.addEventListener('click', (e)=>{
			if (dropdown && bell && !dropdown.contains(e.target) && !bell.contains(e.target)){
				open = false;
				dropdown.classList.add('hidden');
			}
		});

		// Poll every 30s
		setInterval(fetchNotifs, 30000);
		// Initial load
		fetchNotifs();
	}
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initNotifications);
	} else {
		// DOM already loaded
		initNotifications();
	}
})();

<?php include __DIR__ . '/chatbot.php'; ?>
</body>
</html>



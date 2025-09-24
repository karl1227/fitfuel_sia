<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$user_id = (int) $_SESSION['user_id'];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$alert = ['type'=>'','msg'=>''];

try {
	$pdo = getDBConnection();
	if (method_exists($pdo, 'setAttribute')) {
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}

	// Ensure shipping_addresses table has required cols (including address_line3)
	try {
		$cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipping_addresses'")
					->fetchAll(PDO::FETCH_COLUMN);
		if (is_array($cols)) {
			if (!in_array('address_line3', $cols, true)) {
				$pdo->exec("ALTER TABLE shipping_addresses ADD COLUMN address_line3 varchar(255) NULL AFTER address_line2");
			}
		}
	} catch (Throwable $e) {}

	// Load user basic info (for generating sample address if needed)
	$u = $pdo->prepare("SELECT username, first_name, last_name, phone FROM users WHERE user_id = ?");
	$u->execute([$user_id]);
	$user = $u->fetch() ?: ['username'=>'User','first_name'=>'','last_name'=>'','phone'=>''];

	// Load all addresses for this user
	$addrStmt = $pdo->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC, created_at DESC");
	$addrStmt->execute([$user_id]);
	$addresses = $addrStmt->fetchAll();

	// If fewer than 3 addresses, auto-insert one placeholder non-default
	if (count($addresses) < 3) {
		$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
		if ($fullName === '') $fullName = (string)($user['username'] ?? 'User');
		$phone = $user['phone'] ?? '';
		if ($phone === '') $phone = '09123456789';

		$ins = $pdo->prepare("INSERT INTO shipping_addresses (user_id, full_name, phone, address_line1, address_line2, address_line3, city, state, postal_code, country, is_default, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,? ,0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
		$ins->execute([
			$user_id,
			$fullName,
			$phone,
			'Sample Street 123',
			'Unit 2B',
			'Landmark XYZ',
			'Quezon City',
			'National Capital Region',
			'1100',
			'Philippines'
		]);

		$addrStmt->execute([$user_id]);
		$addresses = $addrStmt->fetchAll();
	}

	// Cart count for header
	$cart_count = 0;
	try {
		$cs = $pdo->prepare("SELECT COALESCE(SUM(ci.quantity),0) c FROM cart c LEFT JOIN cart_items ci ON c.cart_id=ci.cart_id WHERE c.user_id=?");
		$cs->execute([$user_id]);
		$cart_count = (int)($cs->fetch()['c'] ?? 0);
	} catch(Throwable $e){}

} catch (Throwable $e) {
	$alert = ['type'=>'error','msg'=>$e->getMessage()];
	$addresses = [];
	$cart_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>My Addresses - FitFuel</title>
	<link rel="icon" href="img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		.orange-btn{background:#ee4d2d}
		.orange-btn:hover{background:#d63f20}
	</style>
</head>
<body class="font-body bg-[#f6f6f6] text-slate-700">
	<!-- top bars -->
	<nav class="bg-white text-black py-2">
		<div class="container mx-auto px-4">
			<div class="flex justify-end space-x-6 text-sm">
				<a href="#" class="hover:text-emerald-400">Review</a>
				<a href="#" class="hover:text-emerald-400">Help</a>
				<a href="logout.php" class="hover:text-emerald-400">Logout</a>
			</div>
		</div>
	</nav>
	<nav class="bg-black py-4">
		<div class="container mx-auto px-4">
			<div class="flex items-center justify-between">
				<a href="index.html" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" alt="LOGO"></a>
				<div class="hidden md:flex items-center space-x-8">
					<a href="index.html" class="text-white hover:text-emerald-600">Home</a>
					<a href="shop.php" class="text-white hover:text-emerald-600">Shop</a>
					<a href="#" class="text-white hover:text-emerald-600">About</a>
					<a href="#" class="text-white hover:text-emerald-600">Contact</a>
				</div>
				<div class="flex items-center space-x-4">
					<a href="cart.php" class="relative p-2 text-white hover:text-emerald-600">
						<i class="fas fa-shopping-cart text-xl"></i>
						<?php if ($cart_count>0): ?>
							<span class="absolute -top-1 -right-1 bg-emerald-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
						<?php endif; ?>
					</a>
					<a href="profile.php" class="p-2 text-white hover:text-emerald-600"><i class="fas fa-user text-xl"></i></a>
				</div>
			</div>
		</div>
	</nav>

	<div class="container mx-auto px-4 py-8">
		<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
			<!-- LEFT SIDEBAR -->
			<aside class="md:col-span-1 bg-white rounded-lg border border-gray-200 p-4">
				<nav class="space-y-4 text-[15px]">
					<div>
						<div class="text-gray-400 uppercase text-xs mb-2">My Account</div>
						<a href="profile.php" class="block hover:text-emerald-600">Profile</a>
						<a class="block text-emerald-600 font-medium">Addresses</a>
						<a href="#" class="block hover:text-emerald-600">Notification Settings</a>
					</div>
					<div>
						<div class="text-gray-400 uppercase text-xs mb-2">My Purchase</div>
						<a href="my_orders.php" class="block hover:text-emerald-600">My Orders</a>
					</div>
				</nav>
			</aside>

			<!-- MAIN CARD -->
			<section class="md:col-span-3 bg-white rounded-lg border border-gray-200">
				<div class="p-6 border-b">
					<h1 class="text-[20px] font-semibold text-slate-900">My Addresses</h1>
					<p class="text-gray-500 text-sm">Manage your shipping addresses</p>
				</div>

				<?php if ($alert['type']==='error'): ?>
					<div class="mx-6 mt-4 rounded bg-red-50 text-red-700 px-4 py-3 border border-red-200">
						<i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo h($alert['msg']); ?>
					</div>
				<?php endif; ?>

				<div class="p-6 space-y-4">
					<div class="flex justify-end">
						<button onclick="openAddressModal()" class="orange-btn text-white px-4 py-2 rounded shadow-sm"><i class="fa fa-plus mr-2"></i>Add Address</button>
					</div>

					<?php if (empty($addresses)): ?>
						<div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
							<i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
							<p class="text-gray-500 mb-4">No addresses found</p>
							<button onclick="openAddressModal()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors">Add Address</button>
						</div>
					<?php else: ?>
						<div class="space-y-3">
							<?php foreach ($addresses as $a): ?>
								<div class="border border-gray-200 rounded-lg p-4">
									<div class="flex items-start justify-between">
										<div class="flex-1">
											<div class="flex items-center space-x-3">
												<h3 class="font-semibold text-slate-800"><?php echo h($a['full_name']); ?></h3>
												<span class="text-gray-500 text-sm">|</span>
												<p class="text-gray-600 text-sm"><?php echo h($a['phone']); ?></p>
												<?php if ((int)$a['is_default'] === 1): ?>
													<span class="ml-2 bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full">Default</span>
												<?php endif; ?>
											</div>
											<p class="text-gray-600 text-sm mt-1">
												<?php echo h($a['address_line1']); ?>
												<?php if (!empty($a['address_line2'])): ?>, <?php echo h($a['address_line2']); ?><?php endif; ?>
												<?php if (!empty($a['address_line3'])): ?>, <?php echo h($a['address_line3']); ?><?php endif; ?>
												<?php echo ', ' . h(($a['city'] ?? '')); ?>
												<?php if (!empty($a['state'])): ?>, <?php echo h($a['state']); ?><?php endif; ?>
												<?php if (!empty($a['postal_code'])): ?> <?php echo h($a['postal_code']); ?><?php endif; ?>
											</p>
										</div>
										<div class="flex items-center space-x-2">
											<button class="px-3 py-1 text-sm border rounded hover:bg-gray-50" onclick="openEditModal(this)"
												data-id="<?php echo (int)$a['address_id']; ?>"
												data-full_name="<?php echo h($a['full_name']); ?>"
												data-phone="<?php echo h($a['phone']); ?>"
												data-line1="<?php echo h($a['address_line1']); ?>"
												data-line2="<?php echo h($a['address_line2']); ?>"
												data-line3="<?php echo h($a['address_line3']); ?>"
												data-city="<?php echo h($a['city']); ?>"
												data-state="<?php echo h($a['state']); ?>"
												data-postal="<?php echo h($a['postal_code']); ?>"
												data-default="<?php echo (int)$a['is_default']; ?>">
												<i class="fa fa-pen mr-1"></i>Edit
											</button>
											<?php if ((int)$a['is_default'] !== 1): ?>
												<button class="px-3 py-1 text-sm border rounded text-emerald-700 border-emerald-600 hover:bg-emerald-50" onclick="setDefault(<?php echo (int)$a['address_id']; ?>)">
													<i class="fa fa-check mr-1"></i>Set Default
												</button>
											<?php endif; ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
	</div>

	<footer class="bg-slate-800 text-white py-12 mt-12">
		<div class="container mx-auto px-4 text-center">
			<p>&copy; 2024 FitFuel. All rights reserved.</p>
		</div>
	</footer>

	<!-- Address Modal (add/edit) -->
	<div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
				<div class="p-6">
					<div class="flex items-center justify-between mb-6">
						<h3 id="modalTitle" class="text-xl font-semibold text-slate-800">Add Address</h3>
						<button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
					</div>

					<form id="addressForm" onsubmit="submitAddress(event)">
						<input type="hidden" name="address_id" id="address_id">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Full Name *</label>
								<input type="text" name="full_name" id="full_name" required autocomplete="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Phone Number *</label>
								<input type="tel" name="phone" id="phone" required autocomplete="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
						</div>

						<div class="mb-4">
							<label class="block text-sm font-semibold text-slate-800 mb-2">Street / Address Line 1 *</label>
							<input type="text" name="address_line1" id="address_line1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
						</div>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Address Line 2</label>
								<input type="text" name="address_line2" id="address_line2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Address Line 3</label>
								<input type="text" name="address_line3" id="address_line3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">City *</label>
								<input type="text" name="city" id="city" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">State/Region *</label>
								<input type="text" name="state" id="state" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Postal Code *</label>
								<input type="text" name="postal_code" id="postal_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
						</div>

						<div class="flex items-center justify-between mb-6">
							<div class="flex items-center space-x-2">
								<input type="checkbox" id="is_default" name="is_default" class="w-4 h-4">
								<label for="is_default" class="text-sm text-slate-700">Set as default</label>
							</div>
							<div>
								<span class="text-sm text-gray-500">Country</span>
								<div class="text-sm font-medium">Philippines</div>
							</div>
						</div>

						<div class="flex justify-end space-x-3">
							<button type="button" onclick="closeAddressModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
							<button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<script>
function openAddressModal(){
	document.getElementById('modalTitle').textContent = 'Add Address';
	document.getElementById('address_id').value = '';
	['full_name','phone','address_line1','address_line2','address_line3','city','state','postal_code'].forEach(id=>document.getElementById(id).value='');
	document.getElementById('is_default').checked = false;
	document.getElementById('addressModal').classList.remove('hidden');
	document.body.style.overflow='hidden';
}
function openEditModal(btn){
	document.getElementById('modalTitle').textContent = 'Edit Address';
	document.getElementById('address_id').value = btn.dataset.id || '';
	document.getElementById('full_name').value = btn.dataset.full_name || '';
	document.getElementById('phone').value = btn.dataset.phone || '';
	document.getElementById('address_line1').value = btn.dataset.line1 || '';
	document.getElementById('address_line2').value = btn.dataset.line2 || '';
	document.getElementById('address_line3').value = btn.dataset.line3 || '';
	document.getElementById('city').value = btn.dataset.city || '';
	document.getElementById('state').value = btn.dataset.state || '';
	document.getElementById('postal_code').value = btn.dataset.postal || '';
	document.getElementById('is_default').checked = (btn.dataset.default === '1');
	document.getElementById('addressModal').classList.remove('hidden');
	document.body.style.overflow='hidden';
}
function closeAddressModal(){
	document.getElementById('addressModal').classList.add('hidden');
	document.body.style.overflow='';
}
function submitAddress(e){
	e.preventDefault();
	const fd = new FormData(e.target);
	const addressId = fd.get('address_id');
	if (addressId) {
		// Update existing address via update_address.php
		const payload = Object.fromEntries(fd.entries());
		payload.is_default = fd.get('is_default') ? 1 : 0;
		payload.country = 'Philippines';
		fetch('update_address.php', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify(payload) })
			.then(r=>r.json())
			.then(d=>{ if(d.success){ closeAddressModal(); location.reload(); } else { alert(d.message||'Error saving address'); } })
			.catch(()=> alert('Error saving address'));
	} else {
		// Create new default/non-default via save_address.php API (expects different keys)
		const payload = {
			full_name: fd.get('full_name') || '',
			phone: fd.get('phone') || '',
			street_address: fd.get('address_line1') || '',
			postal_code: fd.get('postal_code') || '',
			city: fd.get('city') || '',
			region: fd.get('state') || '',
			is_default: fd.get('is_default') ? 1 : 0
		};
		fetch('save_address.php', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify(payload) })
			.then(r=>r.json())
			.then(d=>{ if(d.success){ closeAddressModal(); location.reload(); } else { alert(d.message||'Error saving address'); } })
			.catch(()=> alert('Error saving address'));
	}
}
function setDefault(addressId){
	if (!confirm('Set this address as default?')) return;
	fetch('set_default_address.php', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ address_id: addressId }) })
		.then(r=>r.json())
		.then(d=>{ if(d.success){ location.reload(); } else { alert(d.message||'Error updating default address'); } })
		.catch(()=> alert('Error updating default address'));
}
	</script>
</body>
</html>

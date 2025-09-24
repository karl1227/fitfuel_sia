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

	// Load user basic info (for generating sample address if needed and sidebar display)
	$u = $pdo->prepare("SELECT username, first_name, last_name, phone, profile_picture FROM users WHERE user_id = ?");
	$u->execute([$user_id]);
	$user = $u->fetch() ?: ['username'=>'User','first_name'=>'','last_name'=>'','phone'=>'','profile_picture'=>''];

	// Load all addresses for this user
	$addrStmt = $pdo->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC, created_at DESC");
	$addrStmt->execute([$user_id]);
	$addresses = $addrStmt->fetchAll();

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
<body class="font-body bg-[#f6f6f6] text-slate-700 min-h-screen flex flex-col">
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
				<a href="index.php" class="flex items-center"><img src="img/LOGO-Fitfuel.png" width="75" alt="LOGO"></a>
				<div class="hidden md:flex items-center space-x-8">
					<a href="index.php" class="text-white hover:text-emerald-600">Home</a>
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

	<div class="flex-grow">
		<div class="container mx-auto px-4 py-8">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
			<!-- LEFT SIDEBAR -->
			<aside class="md:col-span-1 bg-white rounded-lg border border-gray-200 p-4">
				<div class="flex items-center space-x-3 mb-4">
					<?php $avatar = !empty($user['profile_picture']) ? $user['profile_picture'] : 'img/placeholder.svg'; ?>
					<img src="<?php echo h($avatar); ?>" class="w-12 h-12 rounded-full object-cover border" alt="">
					<div>
						<div class="font-semibold"><?php echo h($user['username']); ?></div>
						<div class="text-xs text-gray-500"><i class="fa-regular fa-pen-to-square mr-1"></i>Edit Profile</div>
					</div>
				</div>
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
					<div>
						<div class="text-gray-400 uppercase text-xs mb-2">My Wishlist</div>
						<a href="wishlist.php" class="block hover:text-emerald-600">Wishlist</a>
					</div>
					<div>
						<div class="text-gray-400 uppercase text-xs mb-2">Notifications</div>
						<a href="#" class="block hover:text-emerald-600">Inbox</a>
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
					<?php if (count($addresses) < 3): ?>
					<div class="flex justify-end">
						<button onclick="openAddressModal()" class="orange-btn text-white px-4 py-2 rounded shadow-sm"><i class="fa fa-plus mr-2"></i>Add Address</button>
					</div>
					<?php elseif (count($addresses) >= 3): ?>
					<div class="flex justify-end">
						<div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded-lg text-sm">
							<i class="fas fa-info-circle mr-2"></i>Maximum of 3 addresses reached
						</div>
					</div>
					<?php endif; ?>

					<?php if (empty($addresses)): ?>
						<div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
							<i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
							<p class="text-gray-500 mb-4">No addresses found</p>
							<?php if (count($addresses) < 3): ?>
							<button onclick="openAddressModal()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors">Add Address</button>
							<?php endif; ?>
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
												data-default="<?php echo (int)$a['is_default']; ?>"
												data-region_name="<?php echo h($a['region_name'] ?? ''); ?>"
												data-region_code="<?php echo h($a['region_code'] ?? ''); ?>"
												data-province_name="<?php echo h($a['province_name'] ?? ''); ?>"
												data-province_code="<?php echo h($a['province_code'] ?? ''); ?>"
												data-city_muni_name="<?php echo h($a['city_muni_name'] ?? ''); ?>"
												data-city_muni_code="<?php echo h($a['city_muni_code'] ?? ''); ?>"
												data-barangay_name="<?php echo h($a['barangay_name'] ?? ''); ?>"
												data-barangay_code="<?php echo h($a['barangay_code'] ?? ''); ?>">
												<i class="fa fa-pen mr-1"></i>Edit
											</button>
											<?php if ((int)$a['is_default'] !== 1): ?>
												<button class="px-3 py-1 text-sm border rounded text-emerald-700 border-emerald-600 hover:bg-emerald-50" onclick="setDefault(<?php echo (int)$a['address_id']; ?>)">
													<i class="fa fa-check mr-1"></i>Set Default
												</button>
											<?php endif; ?>
											<button class="px-3 py-1 text-sm border rounded text-red-700 border-red-600 hover:bg-red-50" onclick="deleteAddress(<?php echo (int)$a['address_id']; ?>)">
												<i class="fa fa-trash mr-1"></i>Delete
											</button>
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
	</div>

	<footer class="bg-slate-800 text-white py-12">
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
						<button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
							<i class="fas fa-times text-xl"></i>
						</button>
					</div>

					<form id="addressForm" onsubmit="saveAddress(event)">
						<input type="hidden" name="address_id" id="address_id">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Full Name *</label>
								<input type="text" name="full_name" id="full_name" required autocomplete="name"
									   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Phone Number *</label>
								<input type="tel" name="phone" id="phone" required autocomplete="tel"
									   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
						</div>

						<!-- PSGC selects -->
						<div class="mb-4">
							<label class="block text-sm font-semibold text-slate-800 mb-2">Region *</label>
							<select id="region" name="region" required class="w-full px-3 py-2 border rounded-lg"></select>
						</div>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Province *</label>
								<select id="province" name="province" required class="w-full px-3 py-2 border rounded-lg"></select>
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">City/Municipality *</label>
								<select id="city" name="city" required class="w-full px-3 py-2 border rounded-lg"></select>
							</div>
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Barangay *</label>
								<select id="barangay" name="barangay" required class="w-full px-3 py-2 border rounded-lg"></select>
							</div>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
							<div>
								<label class="block text-sm font-semibold text-slate-800 mb-2">Postal Code *</label>
								<input type="text" name="postal_code" id="postal_code" required autocomplete="postal-code"
									   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
							</div>
						</div>

						<div class="mb-6">
							<label class="block text-sm font-semibold text-slate-800 mb-2">Street Name, Building, House No. *</label>
							<input type="text" name="street_address" id="street_address" required autocomplete="address-line1"
								   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
						</div>

						<div class="flex items-center justify-between mb-6">
							<div class="flex items-center space-x-2">
								<input type="checkbox" id="is_default" name="is_default" class="w-4 h-4">
								<label for="is_default" class="text-sm text-slate-700">Set as default</label>
							</div>
						</div>

						<div class="flex justify-end space-x-3">
							<button type="button" onclick="closeAddressModal()"
									class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
							<button type="submit"
									class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Save Address</button>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>

	<script>
/* ===== PSGC address loader (handles NCR & district-based cities) ===== */
const PSGC = "https://psgc.gitlab.io/api";

function setOptions(el, items, { placeholder = "Select...", value = "code", label = "name" } = {}) {
  el.innerHTML = "";
  const opt = document.createElement("option");
  opt.value = ""; opt.textContent = placeholder;
  el.appendChild(opt);
  (items || []).forEach(it => {
    const o = document.createElement("option");
    o.value = it[value]; o.textContent = it[label];
    el.appendChild(o);
  });
  el.disabled = false;
}
function setNA(el, text = "Not applicable") {
  el.innerHTML = `<option value="">${text}</option>`;
  el.disabled = true;
}
async function jget(url, fallback = []) {
  try {
    console.log('[PSGC] Fetching:', url);
    const r = await fetch(url);
    if (!r.ok) throw new Error("HTTP " + r.status);
    const data = await r.json();
    console.log('[PSGC] Response:', data.length || 'non-array', 'items');
    return data;
  } catch (e) {
    console.error("[PSGC] Error:", url, e);
    return fallback;
  }
}
async function loadRegions() {
  const regions = await jget(`${PSGC}/regions/`);
  setOptions(document.getElementById("region"), regions, { placeholder: "Select Region" });
}
async function loadProvincesOrCities(regionCode) {
  const provSel = document.getElementById("province");
  const citySel = document.getElementById("city");
  const brgySel = document.getElementById("barangay");
  provSel.disabled = citySel.disabled = brgySel.disabled = true;
  provSel.innerHTML = `<option value="">Loading…</option>`;
  citySel.innerHTML = `<option value="">Select City/Municipality</option>`;
  brgySel.innerHTML = `<option value="">Select Barangay</option>`;

  const provs = await jget(`${PSGC}/regions/${regionCode}/provinces/`, []);
  if (provs.length > 0) {
    setOptions(provSel, provs, { placeholder: "Select Province" });
    provSel.disabled = false;
  } else {
    // NCR-like regions without provinces: load cities/municipalities from region
    setNA(provSel, "Not applicable");
    const [cities, munis] = await Promise.all([
      jget(`${PSGC}/regions/${regionCode}/cities/`, []),
      jget(`${PSGC}/regions/${regionCode}/municipalities/`, []),
    ]);
    const merged = [...cities, ...munis];
    if (merged.length > 0) {
      setOptions(citySel, merged, { placeholder: "Select City/Municipality" });
      citySel.disabled = false;
    } else {
      setOptions(citySel, [], { placeholder: "No cities/municipalities found" });
      citySel.disabled = true;
    }
  }
}
async function loadCitiesFromProvince(provCode) {
  const citySel = document.getElementById("city");
  const brgySel = document.getElementById("barangay");
  citySel.disabled = brgySel.disabled = true;
  citySel.innerHTML = `<option value="">Loading…</option>`;
  brgySel.innerHTML = `<option value="">Select Barangay</option>`;

  const [cities, munis] = await Promise.all([
    jget(`${PSGC}/provinces/${provCode}/cities/`, []),
    jget(`${PSGC}/provinces/${provCode}/municipalities/`, []),
  ]);
  const merged = [...cities, ...munis];
  if (merged.length > 0) {
    setOptions(citySel, merged, { placeholder: "Select City/Municipality" });
    citySel.disabled = false;
  } else {
    setOptions(citySel, [], { placeholder: "No cities/municipalities found" });
    citySel.disabled = true;
  }
}
async function loadBarangays(cityOrMuniCode) {
  const brgySel = document.getElementById("barangay");
  brgySel.disabled = true;
  brgySel.innerHTML = `<option value="">Loading…</option>`;

  try {
    console.log('Loading barangays for city/muni:', cityOrMuniCode);
    
    // Try City → then Municipality → then Districts (e.g., Manila)
    console.log('Trying cities API...');
    let brgys = await jget(`${PSGC}/cities/${cityOrMuniCode}/barangays/`, []);
    console.log('Cities API result:', brgys.length, 'barangays');
    
    if (brgys.length === 0) {
      console.log('Trying municipalities API...');
      brgys = await jget(`${PSGC}/municipalities/${cityOrMuniCode}/barangays/`, []);
      console.log('Municipalities API result:', brgys.length, 'barangays');
    }
    
    if (brgys.length === 0) {
      console.log('Trying districts API...');
      const districts = await jget(`${PSGC}/cities/${cityOrMuniCode}/districts/`, []);
      console.log('Districts found:', districts.length);
      
      if (districts.length > 0) {
        console.log('Loading barangays from districts...');
        const perDistrict = await Promise.all(
          districts.map(d => jget(`${PSGC}/districts/${d.code}/barangays/`, []))
        );
        brgys = perDistrict.flat();
        console.log('Districts API result:', brgys.length, 'barangays');
      }
    }
    
    console.log('Final barangays loaded:', brgys.length);

    if (brgys.length > 0) {
      setOptions(brgySel, brgys, { placeholder: "Select Barangay" });
      brgySel.disabled = false;
      console.log('Barangay dropdown enabled with', brgys.length, 'options');
    } else {
      setOptions(brgySel, [], { placeholder: "No barangays found" });
      brgySel.disabled = true;
      console.log('No barangays found, dropdown disabled');
    }
  } catch (error) {
    console.error('Error loading barangays:', error);
    setOptions(brgySel, [], { placeholder: "Error loading barangays" });
    brgySel.disabled = true;
  }
}

function openAddressModal(){
	document.getElementById('modalTitle').textContent = 'Add Address';
	document.getElementById('address_id').value = '';
	['full_name','phone','street_address','postal_code'].forEach(id=>document.getElementById(id).value='');
	document.getElementById('is_default').checked = false;
	document.getElementById('addressModal').classList.remove('hidden');
	document.body.style.overflow='hidden';
}
function openEditModal(btn){
	document.getElementById('modalTitle').textContent = 'Edit Address';
	document.getElementById('address_id').value = btn.dataset.id || '';
	document.getElementById('full_name').value = btn.dataset.full_name || '';
	document.getElementById('phone').value = btn.dataset.phone || '';
	document.getElementById('street_address').value = btn.dataset.line1 || '';
	document.getElementById('postal_code').value = btn.dataset.postal || '';
	document.getElementById('is_default').checked = (btn.dataset.default === '1');
	
	// Store PSGC data for later use
	window.editPSGCData = {
		region_name: btn.dataset.region_name || '',
		region_code: btn.dataset.region_code || '',
		province_name: btn.dataset.province_name || '',
		province_code: btn.dataset.province_code || '',
		city_muni_name: btn.dataset.city_muni_name || '',
		city_muni_code: btn.dataset.city_muni_code || '',
		barangay_name: btn.dataset.barangay_name || '',
		barangay_code: btn.dataset.barangay_code || ''
	};
	
	document.getElementById('addressModal').classList.remove('hidden');
	document.body.style.overflow='hidden';
	
	// Load PSGC data after modal is shown
	setTimeout(() => {
		loadEditPSGCData();
	}, 100);
}
function loadEditPSGCData() {
	if (!window.editPSGCData) return;
	
	const data = window.editPSGCData;
	console.log('Loading edit PSGC data:', data);
	
	// Set region
	if (data.region_code) {
		console.log('Setting region:', data.region_code);
		document.getElementById('region').value = data.region_code;
		document.getElementById('region').dispatchEvent(new Event('change'));
		
		// Wait for provinces to load, then set province
		setTimeout(() => {
			if (data.province_code) {
				console.log('Setting province:', data.province_code);
				document.getElementById('province').value = data.province_code;
				document.getElementById('province').dispatchEvent(new Event('change'));
				
				// Wait for cities to load, then set city
				setTimeout(() => {
					if (data.city_muni_code) {
						console.log('Setting city:', data.city_muni_code);
						document.getElementById('city').value = data.city_muni_code;
						document.getElementById('city').dispatchEvent(new Event('change'));
						
						// Wait for barangays to load, then set barangay
						setTimeout(() => {
							if (data.barangay_code) {
								console.log('Setting barangay:', data.barangay_code);
								const barangaySelect = document.getElementById('barangay');
								console.log('Barangay select element:', barangaySelect);
								console.log('Barangay select disabled:', barangaySelect.disabled);
								console.log('Barangay select options:', barangaySelect.options.length);
								
								if (barangaySelect.options.length > 1) {
									barangaySelect.value = data.barangay_code;
									console.log('Barangay value set to:', data.barangay_code);
								} else {
									console.log('Barangay dropdown not loaded yet, trying again...');
									// Try again after another delay
									setTimeout(() => {
										if (barangaySelect.options.length > 1) {
											barangaySelect.value = data.barangay_code;
											console.log('Barangay value set to (retry):', data.barangay_code);
										} else {
											console.log('Barangay dropdown still not loaded');
										}
									}, 1000);
								}
							}
						}, 1000); // Increased timeout for barangays
					}
				}, 500); // Increased timeout for cities
			}
		}, 500); // Increased timeout for provinces
	}
}

function closeAddressModal(){
	document.getElementById('addressModal').classList.add('hidden');
	document.body.style.overflow='';
	// Clear edit data
	window.editPSGCData = null;
}
function saveAddress(event) {
  event.preventDefault();
  const pick = (id) => {
    const el = document.getElementById(id);
    return { code: el.value, name: el.options[el.selectedIndex]?.text || "" };
  };
  const region   = pick('region');
  const province = pick('province');
  const city     = pick('city');
  const barangay = pick('barangay');

  const formData = new FormData(event.target);
  const addressData = {
    full_name: formData.get('full_name'),
    phone: formData.get('phone'),
    postal_code: formData.get('postal_code'),
    street_address: formData.get('street_address'),
    is_default: formData.get('is_default') ? 1 : 0,
    region_name: region.name,   region_code: region.code,
    province_name: province.name, province_code: province.code,
    city_muni_name: city.name,  city_muni_code: city.code,
    barangay_name: barangay.name, barangay_code: barangay.code
  };

  // Add address_id for edits
  const addressId = document.getElementById('address_id').value;
  if (addressId) {
    addressData.address_id = addressId;
  }

  fetch('save_address.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(addressData)
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) { closeAddressModal(); location.reload(); }
    else { alert('Error saving address: ' + (d.message || 'Unknown error')); }
  })
  .catch(() => { alert('Error saving address'); });
}
function setDefault(addressId){
	if (!confirm('Set this address as default?')) return;
	fetch('set_default_address.php', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ address_id: addressId }) })
		.then(r=>r.json())
		.then(d=>{ if(d.success){ location.reload(); } else { alert(d.message||'Error updating default address'); } })
		.catch(()=> alert('Error updating default address'));
}

function deleteAddress(addressId){
	if (!confirm('Are you sure you want to delete this address?')) return;
	fetch('delete_address.php', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ address_id: addressId }) })
		.then(r=>r.json())
		.then(d=>{ if(d.success){ location.reload(); } else { alert(d.message||'Error deleting address'); } })
		.catch(()=> alert('Error deleting address'));
}

/* ===== Init PSGC ===== */
document.addEventListener("DOMContentLoaded", async () => {
  const regionSel = document.getElementById("region");
  const provSel   = document.getElementById("province");
  const citySel   = document.getElementById("city");
  const brgySel   = document.getElementById("barangay");

  if (!regionSel || !provSel || !citySel || !brgySel) {
    console.error("[PSGC] Missing selects (region/province/city/barangay).");
    return;
  }

  // Initial placeholders
  setOptions(regionSel, [], { placeholder: "Loading Regions…" });
  setOptions(provSel,   [], { placeholder: "Select Province" });
  setOptions(citySel,   [], { placeholder: "Select City/Municipality" });
  setOptions(brgySel,   [], { placeholder: "Select Barangay" });
  provSel.disabled = citySel.disabled = brgySel.disabled = true;

  await loadRegions();

  // Bind change events
  regionSel.addEventListener("change", (e) => {
    const regionCode = e.target.value;
    if (!regionCode) {
      setOptions(provSel, [], { placeholder: "Select Province" }); provSel.disabled = true;
      setOptions(citySel, [], { placeholder: "Select City/Municipality" }); citySel.disabled = true;
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadProvincesOrCities(regionCode);
  });
  provSel.addEventListener("change", (e) => {
    const provCode = e.target.value;
    if (!provCode) {
      setOptions(citySel, [], { placeholder: "Select City/Municipality" }); citySel.disabled = true;
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadCitiesFromProvince(provCode);
  });
  citySel.addEventListener("change", (e) => {
    const code = e.target.value;
    if (!code) {
      setOptions(brgySel, [], { placeholder: "Select Barangay" }); brgySel.disabled = true;
      return;
    }
    loadBarangays(code);
  });
});
	</script>
</body>
</html>

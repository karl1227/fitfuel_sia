<?php
/* =========================================================================
   path: sidebar.php
   purpose: Account sidebar with improved active highlight (no green fill)
   notes: Safe to include on any page; does not require globals.
   ========================================================================= */

if (!isset($user)) {
    // fallback when including on pages that didn't load $user
    $user = ['username' => 'User', 'profile_picture' => null];
}
if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');

/** Menu definition (labels, href, Font Awesome icon) */
$sections = [
    'MY ACCOUNT' => [
        ['label' => 'Profile',               'href' => 'profile.php',          'icon' => 'fa-user'],
        ['label' => 'Banks & Cards',         'href' => 'bank_cards.php',            'icon' => 'fa-credit-card'],
        ['label' => 'Addresses',             'href' => 'addresses.php',        'icon' => 'fa-location-dot'],
        ['label' => 'Change Password',       'href' => 'change_password.php',  'icon' => 'fa-key'],
        ['label' => 'Privacy Settings',      'href' => 'privacy_settings.php',          'icon' => 'fa-shield-halved'],
        ['label' => 'Notification Settings', 'href' => 'notifications_settings.php',    'icon' => 'fa-bell'],
    ],
    'MY PURCHASE' => [
        ['label' => 'My Orders',             'href' => 'my_orders.php',           'icon' => 'fa-box'],
    ],
    'MY WISHLIST' => [
        ['label' => 'Wishlist',              'href' => 'wishlist.php',         'icon' => 'fa-heart'],
    ],
    'NOTIFICATIONS' => [
        ['label' => 'Inbox',                 'href' => 'inbox.php',            'icon' => 'fa-inbox'],
    ],
];

/** Styles */
$baseLink = 'flex items-center px-4 py-2 rounded-lg mb-1 transition-colors';
$inactive = 'text-slate-700 hover:bg-gray-50';
$active   = 'pl-3 border-l-4 border-emerald-600 bg-gray-100 text-slate-900 font-medium'; // why: consistent, subtle active state
?>
<aside class="bg-white rounded-lg border border-gray-200">
  <div class="p-6 border-b">
    <div class="flex items-center space-x-3">
      <div class="w-12 h-12 rounded-full overflow-hidden border">
        <img
          src="<?php echo h(!empty($user['profile_picture']) ? $user['profile_picture'] : 'img/placeholder.svg'); ?>"
          class="w-full h-full object-cover"
          alt="avatar"
        >
      </div>
      <div>
        <div class="text-slate-900 font-semibold"><?php echo h($user['username']); ?></div>
        <a href="profile_edit.php" class="text-emerald-700 hover:underline text-sm inline-flex items-center gap-1">
          <i class="fa-solid fa-pen"></i> Edit Profile
        </a>
      </div>
    </div>
  </div>

  <nav class="p-4">
    <?php foreach ($sections as $title => $links): ?>
      <div class="text-xs text-gray-400 font-semibold tracking-wide px-2 mt-3 mb-2">
        <?php echo h($title); ?>
      </div>

      <?php foreach ($links as $item):
        $isActive = ($current === basename($item['href']));
        $classes = $baseLink . ' ' . ($isActive ? $active : $inactive);
      ?>
        <a href="<?php echo h($item['href']); ?>" class="<?php echo $classes; ?>">
          <i class="fa-solid <?php echo h($item['icon']); ?> w-5 mr-3"></i>
          <span class="text-sm"><?php echo h($item['label']); ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
</aside>

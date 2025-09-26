<?php
// sidebar.php
// Uses $user (username, profile_picture), h(), and active_link() from the parent page.

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('active_link')) {
  function active_link($file){
    $curr = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    return $curr === $file ? 'text-emerald-600 font-semibold' : 'hover:text-emerald-600';
  }
}

$avatar = !empty($user['profile_picture']) ? $user['profile_picture'] : 'img/placeholder.svg';
?>
<aside class="md:col-span-1 bg-white rounded-lg border border-gray-200 p-4">
  <div class="flex items-center space-x-3 mb-4">
    <img src="<?php echo h($avatar); ?>" class="w-12 h-12 rounded-full object-cover border" alt="">
    <div>
      <div class="font-semibold"><?php echo h($user['username'] ?? ''); ?></div>
      <div class="text-xs text-gray-500">
        <i class="fa-regular fa-pen-to-square mr-1"></i>Edit Profile
      </div>
    </div>
  </div>

  <div class="text-gray-400 uppercase text-xs mb-2">My Account</div>
  <nav class="text-[15px]">
    <a href="profile.php"               class="block mb-2 <?php echo active_link('profile.php'); ?>">Profile</a>
    <a href="banks_cards.php"           class="block mb-2 <?php echo active_link('banks_cards.php'); ?>">Banks &amp; Cards</a>
    <a href="addresses.php"             class="block mb-2 <?php echo active_link('addresses.php'); ?>">Addresses</a>
    <a href="change_password.php"       class="block mb-2 <?php echo active_link('change_password.php'); ?>">Change Password</a>
    <a href="notification_settings.php" class="block mb-2 <?php echo active_link('notification_settings.php'); ?>">Notification Settings</a>
  </nav>

  <div class="text-gray-400 uppercase text-xs mt-6 mb-2">My Purchase</div>
  <nav class="text-[15px]">
    <a href="my_orders.php" class="block mb-2 <?php echo active_link('my_orders.php'); ?>">My Orders</a>
  </nav>

  <div class="text-gray-400 uppercase text-xs mt-6 mb-2">My Wishlist</div>
  <nav class="text-[15px]">
    <a href="wishlist.php" class="block mb-2 <?php echo active_link('wishlist.php'); ?>">Wishlist</a>
  </nav>

  <div class="text-gray-400 uppercase text-xs mt-6 mb-2">Notifications</div>
  <nav class="text-[15px]">
    <a href="inbox.php" class="block mb-2 <?php echo active_link('inbox.php'); ?>">Inbox</a>
  </nav>
</aside>

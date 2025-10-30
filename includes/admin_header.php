<?php
/**
 * Render the admin header with notifications and user menu
 */
?>
<!-- Header -->
<header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6 shadow-md">
    <div class="flex items-center space-x-3">
        <img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
        <div class="w-px h-6 bg-white opacity-30"></div>
        <h1 class="text-xl font-bold uppercase tracking-wide">Admin Dashboard</h1>
    </div>
    <div class="flex items-center space-x-3">
        <!-- Notifications -->
        <div class="relative">
            <button id="admin-notif-bell" class="relative p-2.5 hover:bg-gray-800 rounded-lg transition-all duration-200 hover:scale-105" aria-label="Notifications" title="Notifications">
                <i class="fas fa-bell text-xl"></i>
                <span id="admin-notif-count" class="hidden absolute top-0 right-0 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center transform translate-x-1/2 -translate-y-1/2 shadow-lg animate-pulse">0</span>
            </button>
            <div id="admin-notif-dropdown" class="hidden absolute right-0 mt-2 w-96 bg-white text-gray-800 rounded-lg shadow-2xl border border-gray-200 z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <span class="font-semibold text-gray-900 text-base flex items-center">
                        <i class="fas fa-bell mr-2 text-emerald-600"></i>
                        Notifications
                    </span>
                    <button id="admin-notif-mark-all" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium px-2 py-1 rounded hover:bg-emerald-50 transition-colors">
                        Mark all read
                    </button>
                </div>
                <div id="admin-notif-list" class="max-h-96 overflow-y-auto">
                    <div class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p class="text-sm">Loading notifications...</p>
                    </div>
                </div>
                <div class="px-4 py-2 border-t border-gray-200 bg-gray-50 text-center">
                    <a href="notifications.php" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View all notifications</a>
                </div>
            </div>
        </div>
        <!-- User Menu -->
        <div class="relative">
            <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 hover:bg-gray-800 rounded-lg transition-all duration-200 hover:scale-105">
                <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center ring-2 ring-gray-500">
                    <i class="fas fa-user text-white text-sm"></i>
                </div>
                <span class="hidden md:block text-sm font-medium"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <i class="fas fa-chevron-down text-xs ml-1"></i>
            </button>
            <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-2xl border border-gray-200 py-2 z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                    <span class="inline-block mt-2 px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></span>
                </div>
                <a href="notifications.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell mr-3 text-gray-400 w-4"></i>
                    Notifications
                </a>
                <a href="settings.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-cog mr-3 text-gray-400 w-4"></i>
                    Settings
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-user-cog mr-3 text-gray-400 w-4"></i>
                    Profile Settings
                </a>
                <div class="border-t border-gray-200 my-1"></div>
                <a href="../logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fas fa-sign-out-alt mr-3 text-red-500 w-4"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
// User menu toggle
function toggleUserMenu(){
    const menu = document.getElementById('userMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
    if (!userButton && userMenu && !userMenu.contains(event.target)) {
        userMenu.classList.add('hidden');
    }
});

// Admin Notifications functionality
(function(){
    const bell = document.getElementById('admin-notif-bell');
    const dropdown = document.getElementById('admin-notif-dropdown');
    const list = document.getElementById('admin-notif-list');
    const countEl = document.getElementById('admin-notif-count');
    const markAllBtn = document.getElementById('admin-notif-mark-all');
    
    if (!bell || !dropdown) return;

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
                    // Let the link navigate
                    markRead(n.notification_id);
                } else {
                    e.preventDefault();
                    markRead(n.notification_id);
                }
            });
            list.appendChild(a);
        });
    }

    function escapeHtml(s){
        return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[c]));
    }

    async function fetchNotifs(){
        try{
            const res = await fetch('../get_notifications.php');
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
            await fetch('../mark_notification_read.php', {
                method:'POST', 
                headers:{'Content-Type':'application/json'}, 
                body: JSON.stringify({notification_id: id})
            });
            fetchNotifs();
        }catch(e){
            console.error('Failed to mark notification as read:', e);
        }
    }

    if (markAllBtn){
        markAllBtn.addEventListener('click', async (e)=>{
            e.preventDefault();
            try{
                await fetch('../mark_notification_read.php', {
                    method:'POST', 
                    headers:{'Content-Type':'application/json'}, 
                    body: JSON.stringify({all: true})
                });
                fetchNotifs();
            }catch(e){
                console.error('Failed to mark all as read:', e);
            }
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
})();
</script>


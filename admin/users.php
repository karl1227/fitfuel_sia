<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';

$pdo = getDBConnection();
$message = '';
$error = '';

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        switch ($action) {
            case 'add_admin':
                if (!$isAdmin) { throw new Exception('Unauthorized'); }
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $role = $_POST['role']; // admin | manager | staff
                $status = $_POST['status']; // active | suspended | inactive
                $password = $_POST['password'];
                if ($username === '' || $email === '' || $password === '') { throw new Exception('All fields are required'); }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hash, $role, $status]);
                $message = 'Admin/Staff user created successfully';
                break;
            case 'edit_user':
                $userId = intval($_POST['user_id']);
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $status = $_POST['status'];
                $newPassword = $_POST['new_password'] ?? '';
                $role = $_POST['role'] ?? '';

                // Fetch existing role to enforce rules
                $cur = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
                $cur->execute([$userId]);
                $existing = $cur->fetch();
                if (!$existing) { throw new Exception('User not found'); }

                $isTargetAdminish = in_array($existing['role'], ['admin','manager','staff']);
                if ($isTargetAdminish && !$isAdmin) { throw new Exception('Only admin can edit admin/staff'); }

                // Build update
                $fields = ['username' => $username, 'email' => $email, 'status' => $status];
                $params = [$username, $email, $status];
                $setSql = 'username = ?, email = ?, status = ?';

                if ($isAdmin && $role && in_array($role, ['admin','manager','staff','customer'])) {
                    $setSql .= ', role = ?';
                    $params[] = $role;
                }
                if ($isTargetAdminish && $newPassword !== '') {
                    $setSql .= ', password_hash = ?';
                    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                $params[] = $userId;
                $sql = "UPDATE users SET $setSql WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = 'User updated successfully';
                break;
            case 'delete_admin':
                if (!$isAdmin) { throw new Exception('Unauthorized'); }
                $userId = intval($_POST['user_id']);
                if ($userId === intval($_SESSION['user_id'])) { throw new Exception('You cannot delete your own account'); }
                // Allow delete only for admin/manager/staff accounts
                $chk = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
                $chk->execute([$userId]);
                $row = $chk->fetch();
                if (!$row || !in_array($row['role'], ['admin','manager','staff'])) { throw new Exception('Only admin/staff accounts can be deleted'); }
                $del = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
                $del->execute([$userId]);
                $message = 'Admin/Staff user deleted';
                break;
            default:
                // no-op
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Independent filters & search per section
$adminSearch = $_GET['admin_search'] ?? '';
$adminStatus = $_GET['admin_status'] ?? '';
$customerSearch = $_GET['customer_search'] ?? '';
$customerStatus = $_GET['customer_status'] ?? '';

// Admin/Staff/Manager list
$adminConds = ["role IN ('admin','manager','staff')"];
$adminParams = [];
if ($adminSearch) { $adminConds[] = '(username LIKE ? OR email LIKE ?)'; $adminParams[] = "%$adminSearch%"; $adminParams[] = "%$adminSearch%"; }
if ($adminStatus) { $adminConds[] = 'status = ?'; $adminParams[] = $adminStatus; }
$adminWhere = 'WHERE ' . implode(' AND ', $adminConds);
$adminSql = "SELECT user_id, username, email, role, status, created_at FROM users $adminWhere ORDER BY FIELD(role,'admin','manager','staff'), username";
$adminStmt = $pdo->prepare($adminSql);
$adminStmt->execute($adminParams);
$admins = $adminStmt->fetchAll();

// Customers list
$custConds = ["role = 'customer'"];
$custParams = [];
if ($customerSearch) { $custConds[] = '(username LIKE ? OR email LIKE ?)'; $custParams[] = "%$customerSearch%"; $custParams[] = "%$customerSearch%"; }
if ($customerStatus) { $custConds[] = 'status = ?'; $custParams[] = $customerStatus; }
$custWhere = 'WHERE ' . implode(' AND ', $custConds);
$custSql = "SELECT user_id, username, email, role, status, created_at FROM users $custWhere ORDER BY username";
$custStmt = $pdo->prepare($custSql);
$custStmt->execute($custParams);
$customers = $custStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - FitFuel Admin</title>
    <link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-item.active { background-color:#f3f4f6; border-right:3px solid #000; }
        .sidebar-item:hover { background-color:#f9fafb; }
        .badge { padding:2px 8px; border-radius:9999px; font-size:12px; font-weight:600; }
        .badge-admin { background:#e0e7ff; color:#3730a3; }
        .badge-customer { background:#dcfce7; color:#166534; }
        .badge-active { background:#d1fae5; color:#065f46; }
        .badge-suspended { background:#fee2e2; color:#991b1b; }
    </style>
    <script>
        function openAddAdmin(){ document.getElementById('addAdminModal').classList.remove('hidden'); }
        function closeAddAdmin(){ document.getElementById('addAdminModal').classList.add('hidden'); }
        function openEdit(id, username, email, role, status){
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_status').value = status;
            // Role editable only for admins
            const roleSel = document.getElementById('edit_role');
            if (roleSel){ roleSel.value = role; }
            document.getElementById('editUserModal').classList.remove('hidden');
        }
        function closeEdit(){ document.getElementById('editUserModal').classList.add('hidden'); }
        function confirmDelete(id){
            if(confirm('Delete this admin/staff account?')){
                const f = document.getElementById('deleteForm');
                f.user_id.value = id; f.submit();
            }
        }
        function filterAdmin(){
            const s = document.getElementById('adminSearch').value;
            const st = document.getElementById('adminStatus').value;
            const params = new URLSearchParams(window.location.search);
            params.set('admin_search', s);
            params.set('admin_status', st);
            window.location = '?' + params.toString();
        }
        function filterCustomer(){
            const s = document.getElementById('customerSearch').value;
            const st = document.getElementById('customerStatus').value;
            const params = new URLSearchParams(window.location.search);
            params.set('customer_search', s);
            params.set('customer_status', st);
            window.location = '?' + params.toString();
        }
    </script>
</head>
<body class="font-body bg-gray-50">
    <header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white rounded flex items-center justify-center"><span class="text-black font-bold text-lg">F</span></div>
            <h1 class="text-xl font-bold uppercase">Admin</h1>
        </div>
        <div class="flex items-center space-x-4">
            <button class="p-2 hover:bg-gray-800 rounded-lg transition-colors"><i class="fas fa-bell text-xl"></i></button>
            <button class="p-2 hover:bg-gray-800 rounded-lg transition-colors" onclick="location.href='../logout.php'"><i class="fas fa-user text-xl"></i></button>
        </div>
    </header>
    <aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 overflow-y-auto">
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-th-large text-gray-600"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="product.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cube text-gray-600"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-shopping-cart text-gray-600"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="inventory.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-archive text-gray-600"></i>
                        <span>Inventory</span>
                    </a>
                </li>
                <li>
                    <a href="users.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-users text-gray-600"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-chart-line text-gray-600"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-file-alt text-gray-600"></i>
                        <span>Contents</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-history text-gray-600"></i>
                        <span>Audit Trail</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-bell text-gray-600"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
                        <i class="fas fa-cog text-gray-600"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 bg-black text-white p-4">
            <div class="flex items-center space-x-3"><div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center"><i class="fas fa-user text-white"></i></div><div><p class="font-medium">Admin User</p><p class="text-sm text-gray-300"><?php echo htmlspecialchars($_SESSION['email']); ?></p></div></div>
        </div>
    </aside>
    <main class="ml-64 pt-24 pb-6 px-6">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2"><i class="fas fa-users text-2xl text-gray-600"></i><h1 class="text-3xl font-bold text-gray-900">Users</h1></div>
                    <p class="text-gray-600">Manage Customers And Admin Users</p>
                </div>
                <?php if ($isAdmin): ?>
                <button onclick="openAddAdmin()" class="px-4 py-2 bg-black text-white rounded-lg"><i class="fas fa-user-plus mr-2"></i>Add Users</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="bg-white rounded-lg border border-gray-200">
            <!-- Top global search removed per request -->
            <div class="overflow-x-auto">
                <h3 class="text-lg font-bold text-gray-800 px-6 pt-4">ADMIN</h3>
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-6 pb-2">
                    <div class="relative">
                        <input id="adminSearch" type="text" placeholder="Search admins..." class="w-full sm:w-64 pl-4 pr-10 py-2 rounded-lg border border-gray-300" value="<?php echo htmlspecialchars($adminSearch); ?>" onkeypress="if(event.key==='Enter') filterAdmin()">
                        <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="mt-2 lg:mt-0">
                        <select id="adminStatus" class="px-4 py-2 rounded-lg border border-gray-300" onchange="filterAdmin()">
                            <option value="" <?php echo $adminStatus===''?'selected':''; ?>>All Status</option>
                            <option value="active" <?php echo $adminStatus==='active'?'selected':''; ?>>Active</option>
                            <option value="suspended" <?php echo $adminStatus==='suspended'?'selected':''; ?>>Suspended</option>
                            <option value="inactive" <?php echo $adminStatus==='inactive'?'selected':''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <table class="w-full mb-8">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-users text-4xl mb-4"></i><p>No admin/staff accounts</p></td></tr>
                    <?php else: foreach ($admins as $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (in_array($u['role'], ['admin','manager','staff'])): ?>
                                    <span class="badge badge-admin"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-customer">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-suspended"><?php echo htmlspecialchars(ucfirst($u['status'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900" onclick="openEdit(<?php echo $u['user_id']; ?>,'<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>','<?php echo $u['role']; ?>','<?php echo $u['status']; ?>')"><i class="fas fa-edit"></i></button>
                                <?php if ($isAdmin && in_array($u['role'], ['admin','manager','staff']) && intval($u['user_id']) !== intval($_SESSION['user_id'])): ?>
                                <button class="text-red-600 hover:text-red-900 ml-3" onclick="confirmDelete(<?php echo $u['user_id']; ?>)"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <h3 class="text-lg font-bold text-gray-800 px-6 pt-2">CUSTOMERS</h3>
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-6 pb-2">
                    <div class="relative">
                        <input id="customerSearch" type="text" placeholder="Search customers..." class="w-full sm:w-64 pl-4 pr-10 py-2 rounded-lg border border-gray-300" value="<?php echo htmlspecialchars($customerSearch); ?>" onkeypress="if(event.key==='Enter') filterCustomer()">
                        <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="mt-2 lg:mt-0">
                        <select id="customerStatus" class="px-4 py-2 rounded-lg border border-gray-300" onchange="filterCustomer()">
                            <option value="" <?php echo $customerStatus===''?'selected':''; ?>>All Status</option>
                            <option value="active" <?php echo $customerStatus==='active'?'selected':''; ?>>Active</option>
                            <option value="suspended" <?php echo $customerStatus==='suspended'?'selected':''; ?>>Suspended</option>
                            <option value="inactive" <?php echo $customerStatus==='inactive'?'selected':''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500"><i class="fas fa-user text-4xl mb-4"></i><p>No customers found</p></td></tr>
                    <?php else: foreach ($customers as $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($u['username']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge badge-active">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-suspended"><?php echo htmlspecialchars(ucfirst($u['status'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900" onclick="openEdit(<?php echo $u['user_id']; ?>,'<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($u['email'], ENT_QUOTES); ?>','customer','<?php echo $u['status']; ?>')"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination removed for split lists -->
        </div>

        <?php if ($isAdmin): ?>
        <!-- Add Admin/Staff Modal -->
        <div id="addAdminModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6"><h3 class="text-xl font-bold text-gray-900">Add Admin/Staff</h3><button onclick="closeAddAdmin()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button></div>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_admin">
                            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Username</label><input name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"/></div>
                            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Email</label><input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"/></div>
                            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Password</label><input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"/></div>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div><label class="block text-sm font-medium text-gray-700 mb-2">Role</label><select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="staff">Staff</option><option value="manager">Manager</option><option value="admin">Admin</option></select></div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="active">Active</option><option value="suspended">Suspended</option><option value="inactive">Inactive</option></select></div>
                            </div>
                            <div class="flex justify-end space-x-3"><button type="button" onclick="closeAddAdmin()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 bg-black text-white rounded-lg">Create</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit User Modal -->
        <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6"><h3 class="text-xl font-bold text-gray-900">Edit User</h3><button onclick="closeEdit()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button></div>
                        <form method="POST">
                            <input type="hidden" name="action" value="edit_user">
                            <input type="hidden" name="user_id" id="edit_user_id">
                            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Username</label><input name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"/></div>
                            <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Email</label><input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"/></div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="active">Active</option><option value="suspended">Suspended</option><option value="inactive">Inactive</option></select></div>
                                <?php if ($isAdmin): ?>
                                <div><label class="block text-sm font-medium text-gray-700 mb-2">Role</label><select name="role" id="edit_role" class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="staff">Inventory Staff</option><option value="manager">Manager</option><option value="admin">Admin</option><option value="customer">Customer</option></select></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isAdmin): ?>
                            <div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-2">New Password (Admins/Staff Only)</label><input type="password" name="new_password" id="edit_new_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Leave blank to keep current"/></div>
                            <?php endif; ?>
                            <div class="flex justify-end space-x-3"><button type="button" onclick="closeEdit()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 bg-black text-white rounded-lg">Save</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <form id="deleteForm" method="POST" class="hidden">
            <input type="hidden" name="action" value="delete_admin"/>
            <input type="hidden" name="user_id" value=""/>
        </form>
        <?php endif; ?>
    </main>
</body>
</html>



<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/audit.php';

$pdo = getDBConnection();
ensureAuditSchema($pdo);

// Filters
$q = trim($_GET['q'] ?? '');
$module = trim($_GET['module'] ?? '');
$action = trim($_GET['action'] ?? '');
$status = trim($_GET['status'] ?? '');
$date = trim($_GET['date'] ?? '');

$where = [];
$params = [];
if ($q !== '') { $where[] = '(username LIKE ? OR role LIKE ? OR ip_address LIKE ? OR user_agent LIKE ? OR JSON_SEARCH(COALESCE(old_values,"{}"), "all", ?) IS NOT NULL OR JSON_SEARCH(COALESCE(new_values,"{}"), "all", ?) IS NOT NULL)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($module !== '') { $where[] = 'module = ?'; $params[] = $module; }
if ($action !== '') { $where[] = 'action = ?'; $params[] = $action; }
if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
if ($date !== '') { $where[] = 'DATE(created_at) = ?'; $params[] = $date; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT * FROM audit_logs $whereSql ORDER BY created_at DESC LIMIT 500");
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Audit Log - Admin</title>
	<link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
	<style>
		.sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
		.sidebar-item:hover { background-color: #f9fafb; }
		.json-block { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; white-space: pre-wrap; }
	</style>
</head>
<body class="font-body bg-gray-50">
	<header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
		<div class="flex items-center space-x-3">
			<img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
			<div class="w-px h-6 bg-white"></div>
			<h1 class="text-xl font-bold uppercase">Admin</h1>
		</div>
		<div></div>
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
					<a href="orders.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
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
					<a href="users.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
						<i class="fas fa-users text-gray-600"></i>
						<span>Users</span>
					</a>
				</li>
				<li>
					<a href="audit_log.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800">
						<i class="fas fa-history text-gray-600"></i>
						<span>Audit Trail</span>
					</a>
				</li>
			</ul>
		</nav>
	</aside>
	<main class="ml-64 pt-24 pb-6 px-6">
		<div class="mb-8">
			<div class="flex items-center space-x-3 mb-2"><i class="fas fa-history text-2xl text-gray-600"></i><h1 class="text-3xl font-bold text-gray-900">Audit Log</h1></div>
			<p class="text-gray-600">Security and activity trail across modules.</p>
		</div>

		<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
			<form method="get" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
				<div class="md:col-span-4 relative">
					<i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
					<input name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search user, IP, UA, JSON values" class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300" />
				</div>
				<div class="md:col-span-2">
					<select name="module" class="w-full px-3 py-2 rounded-lg border border-gray-300">
						<option value="">All Modules</option>
						<?php foreach (['auth','users','products','orders','profile','checkout','settings'] as $m): ?>
						<option value="<?php echo $m; ?>" <?php echo $module===$m?'selected':''; ?>><?php echo ucfirst($m); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="md:col-span-2">
					<input type="text" name="action" value="<?php echo htmlspecialchars($action); ?>" placeholder="Action" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
				</div>
				<div class="md:col-span-2">
					<select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-300">
						<option value="">All Status</option>
						<?php foreach (['success','failure','info'] as $st): ?>
						<option value="<?php echo $st; ?>" <?php echo $status===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="md:col-span-1">
					<input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300" />
				</div>
				<div class="md:col-span-1 md:justify-self-end">
					<a href="audit_log.php" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 inline-block whitespace-nowrap">Clear</a>
				</div>
			</form>
		</div>

		<div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
			<table class="min-w-full text-sm">
				<thead class="bg-gray-50">
					<tr>
						<th class="text-left p-3">When</th>
						<th class="text-left p-3">User</th>
						<th class="text-left p-3">Module</th>
						<th class="text-left p-3">Action</th>
						<th class="text-left p-3">Status</th>
						<th class="text-left p-3">IP</th>
						<th class="text-left p-3">Details</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!$logs): ?>
					<tr><td colspan="7" class="p-6 text-center text-gray-500">No audit entries.</td></tr>
					<?php else: foreach ($logs as $log): ?>
					<tr class="border-t">
						<td class="p-3"><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))); ?></td>
						<td class="p-3"><?php echo htmlspecialchars($log['username'] ?: ('#'.$log['user_id'])); ?> <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($log['role'] ?: ''); ?></span></td>
						<td class="p-3"><?php echo htmlspecialchars(ucfirst($log['module'])); ?></td>
						<td class="p-3"><?php echo htmlspecialchars($log['action']); ?></td>
						<td class="p-3">
							<span class="px-2 py-1 rounded-full text-xs font-semibold <?php 
								$cls = 'bg-gray-100 text-gray-800 border border-gray-200';
								if ($log['status']==='success') $cls='bg-green-100 text-green-800 border border-green-200';
								elseif ($log['status']==='failure') $cls='bg-red-100 text-red-800 border border-red-200';
								echo $cls; ?>"><?php echo htmlspecialchars(ucfirst($log['status'])); ?></span>
						</td>
						<td class="p-3"><?php echo htmlspecialchars($log['ip_address'] ?: '-'); ?></td>
						<td class="p-3">
							<div class="text-xs text-gray-600">UA: <?php echo htmlspecialchars(substr((string)$log['user_agent'],0,60)); ?><?php echo strlen((string)$log['user_agent'])>60?'…':''; ?></div>
							<?php if (!empty($log['old_values'])): ?>
								<div class="mt-1">
									<div class="text-[11px] text-gray-500">Old</div>
									<pre class="json-block bg-gray-50 border rounded p-2"><?php echo htmlspecialchars($log['old_values']); ?></pre>
								</div>
							<?php endif; ?>
							<?php if (!empty($log['new_values'])): ?>
								<div class="mt-1">
									<div class="text-[11px] text-gray-500">New</div>
									<pre class="json-block bg-gray-50 border rounded p-2"><?php echo htmlspecialchars($log['new_values']); ?></pre>
								</div>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</main>
</body>
</html>

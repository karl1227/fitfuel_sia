<?php
require_once '../admin_auth_check.php';
require_once '../config/database.php';
require_once '../config/audit_logger.php';

// -------------------------------
// Schema bootstrap (idempotent)
// -------------------------------
function ensureContentSchema(PDO $pdo): void {
	$pdo->exec("CREATE TABLE IF NOT EXISTS contents (
		content_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		type ENUM('page','banner','homepage') NOT NULL,
		status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
		body LONGTEXT NULL,
		seo_title VARCHAR(255) NULL,
		seo_description VARCHAR(500) NULL,
		seo_keywords VARCHAR(500) NULL,
		image_path VARCHAR(500) NULL,
		link_url VARCHAR(500) NULL,
		schedule_start DATETIME NULL,
		schedule_end DATETIME NULL,
		placement VARCHAR(100) NULL,
		author_user_id BIGINT UNSIGNED NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (content_id),
		KEY idx_type_status (type, status),
		KEY idx_updated (updated_at)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

	// Frontend compatibility: add slug, image, description, short_description; extend type to include 'faq'
	try { $pdo->exec("ALTER TABLE contents ADD COLUMN slug VARCHAR(255) NULL UNIQUE"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE contents ADD COLUMN image VARCHAR(500) NULL"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE contents ADD COLUMN description LONGTEXT NULL"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE contents ADD COLUMN short_description VARCHAR(500) NULL"); } catch (Throwable $e) {}
	try { $pdo->exec("ALTER TABLE contents MODIFY COLUMN type ENUM('page','banner','homepage','faq') NOT NULL"); } catch (Throwable $e) {}
}

$pdo = getDBConnection();
$auditLogger = new AuditLogger();
ensureContentSchema($pdo);

// -------------------------------
// Role helpers
// -------------------------------
$currentRole = $_SESSION['role'] ?? 'staff';
function canPublishDelete(string $role): bool {
	return in_array($role, ['admin','manager'], true);
}

// -------------------------------
// Actions
// -------------------------------
$message = null;
$error = null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;

function handleContentImageUpload(string $fieldName = 'image'): ?string {
	if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
	$uploadDirFs = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
	if (!is_dir($uploadDirFs)) { @mkdir($uploadDirFs, 0777, true); }
	$ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
	$allowed = ['jpg','jpeg','png','gif','webp'];
	if (!in_array($ext, $allowed, true)) return null;
	$fname = uniqid('', true) . '_' . time() . '.' . $ext;
	$dest = $uploadDirFs . $fname;
	if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $dest)) {
		return 'uploads/content/' . $fname; // web path relative to site root
	}
	return null;
}

try {
	if ($action === 'create' || $action === 'update') {
		$contentId = (int)($_POST['content_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
		$type = $_POST['type'] ?? 'page';
		$status = $_POST['status'] ?? 'draft';
		$body = $_POST['body'] ?? null;
		$shortDescription = trim($_POST['short_description'] ?? '') ?: null;
		$seoTitle = trim($_POST['seo_title'] ?? '') ?: null;
		$seoDesc = trim($_POST['seo_description'] ?? '') ?: null;
		$seoKeywords = trim($_POST['seo_keywords'] ?? '') ?: null;
		$linkUrl = trim($_POST['link_url'] ?? '') ?: null;
		$scheduleStart = trim($_POST['schedule_start'] ?? '') ?: null;
		$scheduleEnd = trim($_POST['schedule_end'] ?? '') ?: null;
		$placement = trim($_POST['placement'] ?? '') ?: null;
		$authorId = (int)($_SESSION['user_id'] ?? 0);

		if ($title === '') { throw new InvalidArgumentException('Title is required'); }
		if (!in_array($type, ['page','banner','homepage'], true)) { throw new InvalidArgumentException('Invalid type'); }
		if (!in_array($status, ['draft','published','archived'], true)) { throw new InvalidArgumentException('Invalid status'); }

		// Enforce role for publish
		if ($status === 'published' && !canPublishDelete($currentRole)) {
			throw new RuntimeException('You are not authorized to publish.');
		}

        $imagePath = handleContentImageUpload('image');
        if ($slug === '' && $title !== '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
        }

		if ($action === 'create') {
            $descVal = $body ?: null;
            $imgVal = $imagePath ?: null;
            $stmt = $pdo->prepare("INSERT INTO contents (title, slug, type, status, body, description, short_description, seo_title, seo_description, seo_keywords, image_path, image, link_url, schedule_start, schedule_end, placement, author_user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $slug ?: null, $type, $status, $body, $descVal, $shortDescription, $seoTitle, $seoDesc, $seoKeywords, $imagePath, $imgVal, $linkUrl, $scheduleStart ?: null, $scheduleEnd ?: null, $placement, $authorId]);
			$contentId = $pdo->lastInsertId();
			
			// Log content creation
			$auditLogger->log(
				'content_create',
				'content_management',
				"Content created: {$title} ({$type})",
				null,
				['title' => $title, 'type' => $type, 'status' => $status],
				$contentId,
				'content',
				'medium',
				'success',
				$authorId
			);
			
			$message = 'Content created.';
		} else {
			// Keep existing image if new one not uploaded
            $existing = $pdo->prepare('SELECT image_path, image FROM contents WHERE content_id = ?');
			$existing->execute([$contentId]);
            $row = $existing->fetch();
            $prevPath = $row['image_path'] ?? null;
            $prevImg = $row['image'] ?? null;
            $finalPath = $imagePath ?: $prevPath;
            $finalImg = $imagePath ?: $prevImg;
            $descVal = $body ?: null;
            $stmt = $pdo->prepare("UPDATE contents SET title=?, slug=?, type=?, status=?, body=?, description=?, short_description=?, seo_title=?, seo_description=?, seo_keywords=?, image_path=?, image=?, link_url=?, schedule_start=?, schedule_end=?, placement=? WHERE content_id=?");
            $stmt->execute([$title, $slug ?: null, $type, $status, $body, $descVal, $shortDescription, $seoTitle, $seoDesc, $seoKeywords, $finalPath, $finalImg, $linkUrl, $scheduleStart ?: null, $scheduleEnd ?: null, $placement, $contentId]);
			
			// Log content update
			$auditLogger->log(
				'content_update',
				'content_management',
				"Content updated: {$title} ({$type})",
				$row,
				['title' => $title, 'type' => $type, 'status' => $status],
				$contentId,
				'content',
				'medium',
				'success',
				$authorId
			);
			
			$message = 'Content updated.';
		}
	}
	if ($action === 'archive') {
		$id = (int)($_POST['content_id'] ?? 0);
		
		// Get content details for audit log
		$contentStmt = $pdo->prepare('SELECT title, type FROM contents WHERE content_id = ?');
		$contentStmt->execute([$id]);
		$content = $contentStmt->fetch();
		
		$stmt = $pdo->prepare('UPDATE contents SET status = "archived" WHERE content_id = ?');
		$stmt->execute([$id]);
		
		// Log content archive
		if ($content) {
			$auditLogger->log(
				'content_archive',
				'content_management',
				"Content archived: {$content['title']} ({$content['type']})",
				['status' => 'published'],
				['status' => 'archived'],
				$id,
				'content',
				'medium',
				'success',
				$_SESSION['user_id']
			);
		}
		
		$message = 'Content archived.';
	}
	if ($action === 'publish') {
		if (!canPublishDelete($currentRole)) { throw new RuntimeException('Not authorized'); }
		$id = (int)($_POST['content_id'] ?? 0);
		
		// Get content details for audit log
		$contentStmt = $pdo->prepare('SELECT title, type FROM contents WHERE content_id = ?');
		$contentStmt->execute([$id]);
		$content = $contentStmt->fetch();
		
		$stmt = $pdo->prepare('UPDATE contents SET status = "published" WHERE content_id = ?');
		$stmt->execute([$id]);
		
		// Log content publish
		if ($content) {
			$auditLogger->log(
				'content_publish',
				'content_management',
				"Content published: {$content['title']} ({$content['type']})",
				['status' => 'draft'],
				['status' => 'published'],
				$id,
				'content',
				'high',
				'success',
				$_SESSION['user_id']
			);
		}
		
		$message = 'Content published.';
	}
	if ($action === 'delete') {
		if (!canPublishDelete($currentRole)) { throw new RuntimeException('Not authorized'); }
		$id = (int)($_POST['content_id'] ?? 0);
		
		// Get content details for audit log before deletion
		$contentStmt = $pdo->prepare('SELECT title, type, status FROM contents WHERE content_id = ?');
		$contentStmt->execute([$id]);
		$content = $contentStmt->fetch();
		
		$stmt = $pdo->prepare('DELETE FROM contents WHERE content_id = ?');
		$stmt->execute([$id]);
		
		// Log content deletion
		if ($content) {
			$auditLogger->log(
				'content_delete',
				'content_management',
				"Content deleted: {$content['title']} ({$content['type']})",
				$content,
				null,
				$id,
				'content',
				'high',
				'success',
				$_SESSION['user_id']
			);
		}
		
		$message = 'Content deleted.';
	}
} catch (Throwable $ex) {
	$error = $ex->getMessage();
}

// -------------------------------
// Filters & fetch
// -------------------------------
$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all'; // all, pages, banners, drafts, published, archived, homepage

$where = [];
$params = [];
if ($search !== '') { $where[] = 'title LIKE ?'; $params[] = "%$search%"; }
switch ($filter) {
	case 'pages': $where[] = "type = 'page'"; break;
	case 'banners': $where[] = "type = 'banner'"; break;
	case 'homepage': $where[] = "type = 'homepage'"; break;
	case 'drafts': $where[] = "status = 'draft'"; break;
	case 'published': $where[] = "status = 'published'"; break;
	case 'archived': $where[] = "status = 'archived'"; break;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT c.*, u.username AS author
	FROM contents c
	LEFT JOIN users u ON u.user_id = c.author_user_id
	$whereSql
	ORDER BY c.updated_at DESC
	LIMIT 500");
$stmt->execute($params);
$contents = $stmt->fetchAll();

// Summary stats
$totalPages = (int)$pdo->query("SELECT COUNT(*) FROM contents WHERE type = 'page'")->fetchColumn();
$activeBanners = (int)$pdo->query("SELECT COUNT(*) FROM contents WHERE type = 'banner' AND status = 'published' AND (schedule_start IS NULL OR schedule_start <= NOW()) AND (schedule_end IS NULL OR schedule_end >= NOW())")->fetchColumn();
$draftItems = (int)$pdo->query("SELECT COUNT(*) FROM contents WHERE status = 'draft'")->fetchColumn();
$publishedItems = (int)$pdo->query("SELECT COUNT(*) FROM contents WHERE status = 'published'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Contents - FitFuel Admin</title>
	<link rel="icon" href="../img/LOGO-Fitfuel.png" type="image/png">
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../css/style.css">
	<style>
		.sidebar-item.active { background-color: #f3f4f6; border-right: 3px solid #000; }
		.sidebar-item:hover { background-color: #f9fafb; }
		.search-input { background-color: #f8f9fa; border: 1px solid #e9ecef; }
		.search-input:focus { background-color: #ffffff; border-color: #6c757d; }
		.filter-btn { background-color: #f8f9fa; border: 1px solid #e9ecef; color: #374151; }
		.add-btn { background-color: #000; color: #fff; }
		.add-btn:hover { background-color: #333; }
		.badge { padding: 2px 8px; border-radius: 9999px; font-size: 12px; }
	</style>
	<script>
		function toggleUserMenu(){
			const menu = document.getElementById('userMenu');
			menu.classList.toggle('hidden');
		}
		document.addEventListener('click', function(event) {
			const userMenu = document.getElementById('userMenu');
			const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
			if (!userButton && userMenu && !userMenu.contains(event.target)) {
				userMenu.classList.add('hidden');
			}
		});
	</script>
</head>
<body class="font-body bg-gray-50">
	<header class="bg-black text-white fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6">
		<div class="flex items-center space-x-3">
			<img src="../img/LOGO-Fitfuel.png" alt="FitFuel Logo" class="w-8 h-8 object-contain">
			<div class="w-px h-6 bg-white"></div>
			<h1 class="text-xl font-bold uppercase">Admin</h1>
		</div>
		<div class="flex items-center space-x-4">
			<button class="p-2 hover:bg-gray-800 rounded-lg transition-colors relative">
				<i class="fas fa-bell text-xl"></i>
				<span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
			</button>
			<div class="relative">
				<button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 hover:bg-gray-800 rounded-lg transition-colors">
					<div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
						<i class="fas fa-user text-white text-sm"></i>
					</div>
					<span class="hidden md:block text-sm"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
					<i class="fas fa-chevron-down text-xs"></i>
				</button>
				<div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
					<div class="px-4 py-2 border-b border-gray-200">
						<p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
						<p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
						<span class="inline-block mt-1 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></span>
					</div>
					<a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-cog mr-3 text-gray-400"></i>Profile Settings</a>
					<a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-3 text-gray-400"></i>Preferences</a>
					<div class="border-t border-gray-200 mt-2"></div>
					<a href="../logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt mr-3 text-red-500"></i>Logout</a>
				</div>
			</div>
		</div>
	</header>
	<aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-gray-200 overflow-y-auto">
		<nav class="p-4">
			<ul class="space-y-2">
				<li><a href="dashboard.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-th-large text-gray-600"></i><span>Dashboard</span></a></li>
				<li><a href="product.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-cube text-gray-600"></i><span>Products</span></a></li>
				<li><a href="orders.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-shopping-cart text-gray-600"></i><span>Orders</span></a></li>
				<li><a href="inventory.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-archive text-gray-600"></i><span>Inventory</span></a></li>
				<li><a href="users.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-users text-gray-600"></i><span>Users</span></a></li>
				<li><a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-chart-line text-gray-600"></i><span>Analytics</span></a></li>
				<li><a href="content.php" class="sidebar-item active flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-file-alt text-gray-600"></i><span>Contents</span></a></li>
				<li><a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-history text-gray-600"></i><span>Audit Trail</span></a></li>
				<li><a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-bell text-gray-600"></i><span>Notifications</span></a></li>
				<li><a href="#" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-800"><i class="fas fa-cog text-gray-600"></i><span>Settings</span></a></li>
			</ul>
		</nav>
	</aside>
	<main class="ml-64 pt-24 pb-6 px-6">
		<div class="mb-8">
			<div class="flex items-center space-x-3 mb-2">
				<i class="fas fa-file-alt text-2xl text-gray-600"></i>
				<h1 class="text-3xl font-bold text-gray-900">Contents</h1>
			</div>
			<p class="text-gray-600">Manage static pages, banners, and homepage sections.</p>
		</div>

		<?php if ($message): ?>
			<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><?php echo htmlspecialchars($message); ?></div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<!-- Top Summary Cards -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			<div class="bg-white rounded-lg p-6 border border-gray-200">
				<div class="text-sm text-gray-500">Total Pages</div>
				<div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $totalPages; ?></div>
			</div>
			<div class="bg-white rounded-lg p-6 border border-gray-200">
				<div class="text-sm text-gray-500">Active Banners</div>
				<div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $activeBanners; ?></div>
			</div>
			<div class="bg-white rounded-lg p-6 border border-gray-200">
				<div class="text-sm text-gray-500">Draft Items</div>
				<div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $draftItems; ?></div>
			</div>
			<div class="bg-white rounded-lg p-6 border border-gray-200">
				<div class="text-sm text-gray-500">Published</div>
				<div class="text-2xl font-bold text-gray-900 mt-1"><?php echo $publishedItems; ?></div>
			</div>
		</div>

		<!-- Action Bar -->
		<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
			<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
				<div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 flex-1">
					<form method="get" class="relative flex-1">
						<input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by title" class="search-input w-full pl-4 pr-10 py-2 rounded-lg focus:outline-none">
						<i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
					</form>
					<form method="get">
						<select name="filter" class="filter-btn px-4 py-2 rounded-lg focus:outline-none" onchange="this.form.submit()">
							<option value="all" <?php echo $filter==='all'?'selected':''; ?>>All</option>
							<option value="pages" <?php echo $filter==='pages'?'selected':''; ?>>Pages</option>
							<option value="banners" <?php echo $filter==='banners'?'selected':''; ?>>Banners</option>
							<option value="homepage" <?php echo $filter==='homepage'?'selected':''; ?>>Homepage</option>
							<option value="drafts" <?php echo $filter==='drafts'?'selected':''; ?>>Drafts</option>
							<option value="published" <?php echo $filter==='published'?'selected':''; ?>>Published</option>
							<option value="archived" <?php echo $filter==='archived'?'selected':''; ?>>Archived</option>
						</select>
					</form>
				</div>
				<button onclick="openContentModal()" class="add-btn px-6 py-2 rounded-lg flex items-center space-x-2 focus:outline-none">
					<i class="fas fa-plus text-white"></i><span>Add Content</span>
				</button>
			</div>
		</div>

		<!-- Contents Table -->
		<div class="bg-white rounded-lg border border-gray-200">
			<div class="p-6 border-b border-gray-200">
				<div class="flex items-center space-x-3">
					<i class="fas fa-list text-gray-600"></i>
					<h2 class="text-lg font-semibold text-gray-900">All Contents</h2>
				</div>
			</div>
			<div class="overflow-x-auto">
				<table class="w-full text-sm">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Modified</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<?php if (!$contents): ?>
						<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No content found</td></tr>
						<?php else: foreach ($contents as $c): ?>
						<tr class="hover:bg-gray-50">
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="flex items-center">
									<?php if ($c['image_path']): ?>
										<img src="../<?php echo htmlspecialchars($c['image_path']); ?>" class="w-12 h-12 rounded object-cover mr-3" alt="">
									<?php else: ?>
										<div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-3"><i class="fas fa-file text-gray-500"></i></div>
									<?php endif; ?>
									<div>
										<div class="font-medium text-gray-900"><?php echo htmlspecialchars($c['title']); ?></div>
										<?php if ($c['type'] === 'banner' && $c['link_url']): ?><div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($c['link_url']); ?></div><?php endif; ?>
									</div>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-gray-900"><?php echo ucfirst($c['type']); ?></td>
							<td class="px-6 py-4 whitespace-nowrap">
								<?php
									$cls = 'bg-gray-100 text-gray-800 border border-gray-200';
									switch ($c['status']) {
										case 'published': $cls = 'bg-green-100 text-green-800 border border-green-200'; break;
										case 'draft': $cls = 'bg-yellow-100 text-yellow-800 border border-yellow-200'; break;
										case 'archived': $cls = 'bg-red-100 text-red-800 border border-red-200'; break;
									}
								?>
								<span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $cls; ?>"><?php echo ucfirst($c['status']); ?></span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-gray-900"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($c['updated_at']))); ?></td>
							<td class="px-6 py-4 whitespace-nowrap text-gray-900"><?php echo htmlspecialchars($c['author'] ?? '—'); ?></td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="flex items-center space-x-2">
									<button class="text-blue-600 hover:text-blue-900" onclick='openContentModal(<?php echo json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'><i class="fas fa-edit"></i></button>
									<button class="text-green-600 hover:text-green-900" onclick='previewContent(<?php echo (int)$c['content_id']; ?>)'><i class="fas fa-eye"></i></button>
									<form method="post" class="inline" onsubmit="return confirm('Archive this item?');">
										<input type="hidden" name="action" value="archive">
										<input type="hidden" name="content_id" value="<?php echo (int)$c['content_id']; ?>">
										<button type="submit" class="text-yellow-700 hover:text-yellow-900"><i class="fas fa-box-archive"></i></button>
									</form>
									<?php if (canPublishDelete($currentRole)): ?>
										<form method="post" class="inline" onsubmit="return confirm('Publish this item?');">
											<input type="hidden" name="action" value="publish">
											<input type="hidden" name="content_id" value="<?php echo (int)$c['content_id']; ?>">
											<button type="submit" class="text-emerald-700 hover:text-emerald-900"><i class="fas fa-upload"></i></button>
										</form>
										<form method="post" class="inline" onsubmit="return confirm('Delete permanently?');">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="content_id" value="<?php echo (int)$c['content_id']; ?>">
											<button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
										</form>
									<?php endif; ?>
								</div>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</main>

	<!-- Add/Edit Content Modal -->
	<div id="contentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
				<div class="flex items-center justify-between p-6 border-b">
					<h3 id="contentModalTitle" class="text-xl font-semibold text-gray-900">Add Content</h3>
					<button onclick="closeContentModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
				</div>
				<form id="contentForm" method="post" enctype="multipart/form-data" class="p-6 space-y-5">
					<input type="hidden" name="action" id="contentAction" value="create">
					<input type="hidden" name="content_id" id="contentId" value="">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div class="md:col-span-2">
							<label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
							<select name="type" id="contentType" class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="toggleTypeFields()">
								<option value="page">Static Page</option>
								<option value="banner">Banner</option>
								<option value="homepage">Homepage Section</option>
							</select>
						</div>
                        <div class="md:col-span-2">
							<label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
							<input type="text" name="title" id="contentTitle" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
						</div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                            <input type="text" name="slug" id="contentSlug" placeholder="e.g., aboutus, contact, privacy" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from title.</p>
                        </div>
						<div id="pageFields" class="md:col-span-2 space-y-3">
							<label class="block text-sm font-medium text-gray-700">Body</label>
							<textarea name="body" id="contentBody" rows="8" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Write content..."></textarea>
							<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
								<input type="text" name="seo_title" id="seoTitle" placeholder="SEO Title" class="border border-gray-300 rounded-lg px-3 py-2">
								<input type="text" name="seo_description" id="seoDescription" placeholder="SEO Description" class="border border-gray-300 rounded-lg px-3 py-2">
								<input type="text" name="seo_keywords" id="seoKeywords" placeholder="SEO Keywords" class="border border-gray-300 rounded-lg px-3 py-2">
							</div>
						</div>
						<div id="bannerFields" class="md:col-span-2 space-y-3 hidden">
							<label class="block text-sm font-medium text-gray-700">Banner Image</label>
							<input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
							<input type="text" name="link_url" id="linkUrl" placeholder="Link URL" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
								<input type="datetime-local" name="schedule_start" id="scheduleStart" class="border border-gray-300 rounded-lg px-3 py-2">
								<input type="datetime-local" name="schedule_end" id="scheduleEnd" class="border border-gray-300 rounded-lg px-3 py-2">
							</div>
						</div>
						<div id="homepageFields" class="md:col-span-2 space-y-3 hidden">
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
								<textarea name="short_description" id="shortDescription" placeholder="Brief description for hero carousel (max 500 characters)" class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="3" maxlength="500"></textarea>
								<p class="text-xs text-gray-500 mt-1">This will be displayed as the subtitle in the hero carousel.</p>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700">Section Media (optional)</label>
								<input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
								<input type="text" name="placement" id="placement" placeholder="Placement (e.g., hero, featured, footer)" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
							</div>
						</div>
					</div>
					<div class="flex items-center justify-end space-x-3 pt-2">
						<button type="button" onclick="closeContentModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
						<button type="button" onclick="saveAsDraft()" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800">Save Draft</button>
						<button type="button" onclick="previewCurrent()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Preview</button>
						<?php if (canPublishDelete($currentRole)): ?>
							<button type="button" onclick="publishNow()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-900">Publish</button>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Simple Preview Modal -->
	<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
				<div class="flex items-center justify-between p-4 border-b">
					<h3 class="text-lg font-semibold">Preview</h3>
					<button onclick="closePreview()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
				</div>
				<div id="previewBody" class="p-4 prose max-w-none"></div>
			</div>
		</div>
	</div>

	<script>
		function openContentModal(data) {
			const form = document.getElementById('contentForm');
			form.reset();
			document.getElementById('contentModalTitle').textContent = data ? 'Edit Content' : 'Add Content';
			document.getElementById('contentAction').value = data ? 'update' : 'create';
			document.getElementById('contentId').value = data ? (data.content_id || '') : '';
			document.getElementById('contentType').value = data ? data.type : 'page';
			document.getElementById('contentTitle').value = data ? (data.title || '') : '';
            document.getElementById('contentBody').value = data ? (data.body || data.description || '') : '';
            document.getElementById('contentSlug').value = data ? (data.slug || '') : '';
			document.getElementById('shortDescription').value = data ? (data.short_description || '') : '';
			document.getElementById('seoTitle').value = data ? (data.seo_title || '') : '';
			document.getElementById('seoDescription').value = data ? (data.seo_description || '') : '';
			document.getElementById('seoKeywords').value = data ? (data.seo_keywords || '') : '';
			document.getElementById('linkUrl').value = data ? (data.link_url || '') : '';
			document.getElementById('scheduleStart').value = data && data.schedule_start ? data.schedule_start.replace(' ', 'T') : '';
			document.getElementById('scheduleEnd').value = data && data.schedule_end ? data.schedule_end.replace(' ', 'T') : '';
			document.getElementById('placement').value = data ? (data.placement || '') : '';
			toggleTypeFields();
			document.getElementById('contentModal').classList.remove('hidden');
		}
		function closeContentModal(){ document.getElementById('contentModal').classList.add('hidden'); }
		function toggleTypeFields(){
			const t = document.getElementById('contentType').value;
			document.getElementById('pageFields').classList.toggle('hidden', t !== 'page');
			document.getElementById('bannerFields').classList.toggle('hidden', t !== 'banner');
			document.getElementById('homepageFields').classList.toggle('hidden', t !== 'homepage');
		}
		function saveAsDraft(){
			document.getElementById('contentForm').insertAdjacentHTML('beforeend', '<input type="hidden" name="status" value="draft">');
			document.getElementById('contentForm').submit();
		}
		function publishNow(){
			document.getElementById('contentForm').insertAdjacentHTML('beforeend', '<input type="hidden" name="status" value="published">');
			document.getElementById('contentForm').submit();
		}
		function previewCurrent(){
			const t = document.getElementById('contentType').value;
			const title = document.getElementById('contentTitle').value;
			let html = '';
			if (t === 'page') {
				html = '<h1 class="text-2xl font-bold mb-2">' + escapeHtml(title) + '</h1>' + '<div class="mt-2 whitespace-pre-wrap">' + escapeHtml(document.getElementById('contentBody').value) + '</div>';
			} else if (t === 'banner') {
				html = '<div class="p-4 bg-gray-100 rounded">Banner: ' + escapeHtml(title) + '<br><small>' + escapeHtml(document.getElementById('linkUrl').value) + '</small></div>';
			} else {
				const shortDesc = document.getElementById('shortDescription').value;
				html = '<div class="p-4 bg-gray-100 rounded">Homepage Section: ' + escapeHtml(title) + 
					(shortDesc ? '<br><p class="mt-2 text-gray-600">' + escapeHtml(shortDesc) + '</p>' : '') + 
					'<br><small>Placement: ' + escapeHtml(document.getElementById('placement').value) + '</small></div>';
			}
			document.getElementById('previewBody').innerHTML = html;
			document.getElementById('previewModal').classList.remove('hidden');
		}
		function closePreview(){ document.getElementById('previewModal').classList.add('hidden'); }
		function previewContent(id){
			// For now, open a basic inline preview using row data already present would be better via AJAX.
			// Minimal implementation: scroll to row and open edit in preview mode
			const btn = document.querySelector('button[onclick^=\'openContentModal\'][onclick*=\"\\"content_id\\"\":'+id+']');
			if (btn) { btn.click(); setTimeout(previewCurrent, 200); }
		}
		function escapeHtml(str){ return (str||'').replace(/[&<>"']/g, s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s])); }
	</script>
</body>
</html>



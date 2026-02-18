<?php
require_once __DIR__ . '/../core/includes/check_admin_auth.php';
require_once __DIR__ . '/../core/session_check.php';
require_once __DIR__ . '/../core/config/db.php';
$pdo = getDB();

$currentUser = $_SESSION['user'];
$userId = $currentUser['id'] ?? null;
$firstName = $currentUser['first_name'] ?? '';
$lastName = $currentUser['last_name'] ?? '';
$displayName = trim($firstName . ' ' . $lastName) ?: ($currentUser['email'] ?? 'Admin');

$avatar = '';
if ($userId) {
  $stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ? LIMIT 1');
  $stmt->execute([$userId]);
  $row = $stmt->fetch();
  if ($row && !empty($row['avatar'])) {
    $avatar = $row['avatar'];
  }
}

$initials = strtoupper(substr($firstName,0,1) . substr($lastName,0,1));

// Stats
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalItems = (int) $pdo->query('SELECT COUNT(*) FROM food_items')->fetchColumn();
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status = 'completed'")->fetchColumn();

?>
<!DOCTYPE html>
 <html lang="en">
 <head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard | Marsla Restaurant</title>
   <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
   <script>window.currentUser = <?php echo json_encode($_SESSION['user']); ?>;</script>
  <link rel="stylesheet" href="../css/admin_dashboard.css?v=1.1">
   <link rel="stylesheet" href="../css/loader.css">
   <script src="../js/loader.js"></script>

 </head>
   <body onload="hideLoader()">
     
     <!-- Global Branded Loader -->
     <div id="globalLoader" class="loader-overlay active">
       <div class="loader-container">
         <div class="loader-ring"></div>
         <img src="../assets/favicon.png" alt="Loading..." class="loader-icon">
       </div>
     </div>
   
    <div class="overlay" id="overlay"></div>
 
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img src="../assets/favicon.svg" alt="Marsla" class="sidebar-logo-icon" id="sidebarLogoIcon">
        <span class="sidebar-logo-text" id="sidebarLogoText">Marsla Restaurant</span>
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="nav-item active" data-page="dashboard">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          <span class="nav-text">Dashboard</span>
        </a>
        <a href="#" class="nav-item" data-page="categories">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          <span class="nav-text">Categories</span>
        </a>
        <a href="#" class="nav-item" data-page="food-items">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2 v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
          <span class="nav-text">Food Items</span>
        </a>
        <a href="#" class="nav-item" data-page="orders">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
          <span class="nav-text">Orders</span>
        </a>
        <div class="nav-group" id="postsGroup">
          <a href="#" class="nav-item has-submenu" id="postsToggle">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="4 6 12 2 20 6"/><rect x="2" y="6" width="20" height="6"/></svg>
            <span class="nav-text">Posts</span>
            <svg class="chevron-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </a>
          <div class="submenu" id="postsSubmenu">
            <a href="#" class="nav-item submenu-item" data-page="gallery">
              <span class="nav-text">Gallery</span>
            </a>
            <a href="#" class="nav-item submenu-item" data-page="offers">
              <span class="nav-text">Offers</span>
            </a>
          </div>
        </div>
        <a href="#" class="nav-item" data-page="users">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span class="nav-text">Users</span>
        </a>
        <a href="#" class="nav-item" data-page="settings">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span class="nav-text">Settings</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="../logout.php" class="nav-item logout-btn">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span class="nav-text">Logout</span>
        </a>
      </div>
    </aside>
 
    <div class="main-wrapper" id="mainWrapper">
      <header class="header" id="header">
        <div class="header-left">
          <button class="hamburger-btn" id="hamburgerBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
        <div class="header-right">
          <button class="notification-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="notification-dot"></span>
          </button>
          <div class="user-info" id="userInfoDropdownTrigger">
            <div class="user-details">
              <span class="user-name"><?php echo htmlspecialchars($displayName); ?></span>
              <span class="user-role"><?php echo htmlspecialchars($currentUser['role'] ?? 'Admin'); ?></span>
            </div>
            <div class="avatar">
              <?php if (!empty($avatar)): ?>
                <img src="<?php echo htmlspecialchars('../' . $avatar); ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
              <?php else: ?>
                <?php echo htmlspecialchars($initials); ?>
              <?php endif; ?>
            </div>
            
            <div class="dropdown-menu" id="userDropdown">
                <a href="#" class="dropdown-item" id="myProfileLink">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>My Profile</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="dropdown-item logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Logout</span>
                </a>
            </div>
          </div>
        </div>
      </header>
 
      <main class="content" id="content">

        <section class="page active" id="page-dashboard">
          <div class="welcome-card">
            <h2 class="welcome-title">Hi, <?php echo htmlspecialchars($firstName ?: $displayName); ?></h2>
            <p class="welcome-subtitle">Here's what's happening with your restaurant today.</p>
          </div>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon revenue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
              <div class="stat-info">
                <span class="stat-label">Total Revenue</span>
                <span class="stat-value" id="statRevenue">TZS <?php echo number_format((float)$totalRevenue,2); ?></span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon orders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
              </div>
              <div class="stat-info">
                <span class="stat-label">Total Orders</span>
                <span class="stat-value" id="statOrders"><?php echo (int)$totalOrders; ?></span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon items">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
              </div>
              <div class="stat-info">
                <span class="stat-label">Total Food Items</span>
                <span class="stat-value" id="statItems"><?php echo (int)$totalItems; ?></span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
              <div class="stat-info">
                <span class="stat-label">Total Users</span>
                <span class="stat-value" id="statUsers"><?php echo (int)$totalUsers; ?></span>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent Activity & Logs</h3>
            </div>
            <div class="table-wrapper">
              <table class="table">
                <thead>
                  <tr>
                    <th>Activity</th>
                    <th>Type</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody id="activityTableBody">
                  
                </tbody>
              </table>
            </div>
            <div class="card-footer" style="padding: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: center; gap: 1rem;">
                <button class="btn btn-outline" id="viewFullHistoryBtn">
                    Download full history
                </button>
                <button class="btn btn-outline btn-danger" id="clearRecentsBtn" style="display: none;">
                    Clear Recents
                </button>
            </div>
          </div>
        </section>

        <section class="page" id="page-categories">
          <div class="page-header">
            <div class="search-box">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" class="search-input" placeholder="Search categories..." id="categorySearch">
            </div>
            <button class="btn btn-primary" id="addCategoryBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Category
            </button>
          </div>
          <div class="categories-grid" id="categoriesGrid">
          </div>
        </section>

        <section class="page" id="page-food-items">
          <div class="page-header">
            <div class="search-box">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" class="search-input" placeholder="Search food items..." id="foodSearch">
            </div>
            <button class="btn btn-primary" id="addFoodBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Item
            </button>
          </div>
          <div class="food-grid" id="foodGrid">

          </div>
        </section>
 
        <section class="page" id="page-orders">
          <div class="page-header">
            <div class="filters-row">
              <div class="search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" class="search-input" placeholder="Search orders..." id="orderSearch">
              </div>
              <select class="select-input" id="orderStatusFilter">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="card">
            <div class="table-wrapper">
              <table class="table">
                <thead>
                  <tr>
                    <th>S/N</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody id="ordersTableBody">
                </tbody>
              </table>
            </div>
          </div>
        </section>
 
        <section class="page" id="page-users">
          <div class="page-header">
            <div class="search-box">
              <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="text" class="search-input" placeholder="Search users..." id="userSearch">
            </div>
            <button class="btn btn-primary" id="addUserBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add User
            </button>
          </div>
          <div class="card">
            <div class="table-wrapper">
              <table class="table">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th class="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody id="usersTableBody">

                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Gallery Page -->
        <section class="page" id="page-gallery">
          <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
              <h2 class="card-title">Gallery Management</h2>
              <p class="card-description">Manage images and videos for your restaurant gallery.</p>
            </div>
            <button class="btn btn-primary" onclick="showGalleryModal()">
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 0.5rem;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add Media
            </button>
          </div>

          <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
              <div class="search-box" style="max-width: 300px;">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" id="gallerySearch" placeholder="Search by title or tags..." oninput="filterGallery()">
              </div>
              <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn btn-outline btn-sm active" data-filter="all" onclick="setGalleryFilter('all')">All</button>
                <button class="btn btn-outline btn-sm" data-filter="image" onclick="setGalleryFilter('image')">Images</button>
                <button class="btn btn-outline btn-sm" data-filter="video" onclick="setGalleryFilter('video')">Videos</button>
              </div>
            </div>
          </div>

          <div class="gallery-grid" id="galleryGrid">
            <!-- Items loaded via JS -->
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem 2rem;">
              <div class="loading-spinner" style="margin: 0 auto 1rem;"></div>
              <p style="color: hsl(var(--muted-foreground));">Loading gallery items...</p>
            </div>
          </div>
        </section>

        <!-- Offers Page -->
        <section class="page" id="page-offers">
          <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
              <h2 class="card-title">Special Offers Management</h2>
              <p class="card-description">Manage images, captions, and links for the special offers slider.</p>
            </div>
            <button class="btn btn-primary" onclick="showOfferModal()">
              <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 0.5rem;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add New Offer
            </button>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Live Slider Preview</h3>
              <p class="card-description">This is how your offers look to customers</p>
            </div>
            <div class="card-body">
              <div id="offersPreviewContainer" style="background: hsl(var(--muted)); border-radius: 0.5rem; padding: 1rem; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                <p>Loading preview...</p>
              </div>
            </div>
          </div>

          <div class="card" style="margin-top: 2rem;">
            <div class="table-wrapper">
              <table class="table">
                <thead>
                  <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Caption</th>
                    <th>Order</th>
                    <th class="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody id="offersTableBody">
                  <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem;">Loading offers...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <section class="page" id="page-settings">
          <div class="page-header">
            <h1 class="page-title">Settings</h1>
            <p class="page-subtitle">Manage your account preferences</p>
          </div>

          <div class="settings-container">
            <div class="tabs">
              <button class="tab active" data-tab="theme" onclick="showTab('theme')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                Theme
              </button>
              <button class="tab" data-tab="profile" onclick="showTab('profile')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile
              </button>
            </div>

            <div id="tab-theme" class="tab-content active">
              <div class="card">
                <div class="card-header">
                  <h2 class="card-title">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    Appearance
                  </h2>
                  <p class="card-description">Customize how the dashboard looks</p>
                </div>
                <div class="card-body">
                  <div class="theme-toggle-row">
                    <div class="theme-toggle-info">
                      <div class="theme-icon-wrapper" id="themeIconWrapper">
                        <svg id="themeIcon" class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                      </div>
                      <div>
                        <p class="theme-toggle-title">Dark Mode</p>
                        <p class="theme-toggle-subtitle" id="themeStatus">Currently using light theme</p>
                      </div>
                    </div>
                    <label class="switch">
                      <input type="checkbox" id="themeSwitch" onchange="toggleTheme()">
                      <span class="switch-slider"></span>
                    </label>
                  </div>

                  <div class="theme-preview-grid">
                    <button class="theme-preview active" id="lightPreview" onclick="setTheme('light')">
                      <div class="theme-preview-box light">
                        <div class="preview-bar"></div>
                        <div class="preview-accent"></div>
                      </div>
                      <p>Light</p>
                    </button>
                    <button class="theme-preview" id="darkPreview" onclick="setTheme('dark')">
                      <div class="theme-preview-box dark">
                        <div class="preview-bar"></div>
                        <div class="preview-accent"></div>
                      </div>
                      <p>Dark</p>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div id="tab-profile" class="tab-content">
              <div class="card">
                <div class="card-header">
                  <h2 class="card-title">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Personal Details
                  </h2>
                  <p class="card-description">Update your personal information</p>
                </div>
                <div class="card-body">
                  <form onsubmit="handleProfileUpdate(event)">
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" class="form-input" value="<?= htmlspecialchars($firstName) ?>">
                      </div>
                      <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" class="form-input" value="<?= htmlspecialchars($lastName) ?>">
                      </div>
                    </div>
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                        <label for="profileEmail">Email Address</label>
                        <input type="email" id="profileEmail" class="form-input" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>">
                      </div>
                      <div class="form-group">
                        <label for="profilePhone">Phone Number</label>
                        <input type="tel" id="profilePhone" class="form-input" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">Save Changes</button>
                  </form>
                </div>
              </div>

              <div class="card">
                <div class="card-header">
                  <h2 class="card-title">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                  </h2>
                  <p class="card-description">Update your account password</p>
                </div>
                <div class="card-body">
                  <form onsubmit="handlePasswordChange(event)">
                    <div class="form-group">
                      <label for="currentPassword">Current Password</label>
                      <div class="password-wrapper">
                        <input type="password" id="currentPassword" class="form-input" placeholder=" ">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('currentPassword')">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                      </div>
                    </div>
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                      <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="newPassword" class="form-input" placeholder=" ">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('newPassword')">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                          </div>
                      </div>
                      <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirmPassword" class="form-input" placeholder=" ">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword')">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                          </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Update Password</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
       </section>
      </main>
    </div>
 
    <div class="modal" id="categoryModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="categoryModalTitle">Add Category</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-input" placeholder=" " id="categoryNameInput">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-primary" id="saveCategoryBtn">Save</button>
        </div>
      </div>
    </div>
 
    <div class="modal" id="foodModal">
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h3 class="modal-title" id="foodModalTitle">Add Food Item</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Item Name</label>
            <input type="text" class="form-input" placeholder=" " id="foodNameInput">
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-input" id="foodCategoryInput">
              <option value="">Select category</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Price (TZS)</label>
            <input type="number" class="form-input" placeholder=" " id="foodPriceInput">
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-input" rows="3" placeholder=" " id="foodDescInput"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Image</label>
            <div class="upload-zone" id="uploadZone">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <p>Drag and drop an image or click to browse</p>
              <input type="file" accept="image/*" id="foodImageInput" hidden>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-primary" id="saveFoodBtn">Save</button>
        </div>
      </div>
    </div>
 
    <div class="modal" id="userModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="userModalTitle">Add User</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-input" placeholder=" " id="userNameInput">
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-input" placeholder=" " id="userEmailInput">
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" class="form-input" placeholder=" " id="userPhoneInput">
          </div>
          <div class="form-group" id="userPasswordGroup">
            <label class="form-label">Password</label>
            <div class="password-wrapper">
              <input type="password" class="form-input" placeholder=" " id="userPasswordInput">
              <button type="button" class="password-toggle" onclick="togglePasswordVisibility('userPasswordInput')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <select class="form-input" id="userRoleInput">
              <option value="Customer">Customer</option>
              <option value="Admin">Admin</option>
              <option value="Rider">Rider</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-primary" id="saveUserBtn">Save</button>
        </div>
      </div>
    </div>
 
    <div class="modal" id="orderModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Order Details</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body" id="orderModalBody">
 
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Close</button>
        </div>
      </div>
    </div>
 
    <div class="modal" id="deleteModal">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h3 class="modal-title" id="deleteModalTitle">Confirm Delete</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <p id="deleteModalMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
 
    
      <div class="toast" id="toast">
        <div class="toast-icon" id="toastIcon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <span class="toast-message" id="toastMessage"></span>
        <button class="toast-close" id="toastClose" aria-label="Close notification">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
 
     <!-- Confirmation Modal for Clearing Activities -->
     <div id="clearActivitiesModal" class="modal-overlay">
       <div class="modal-content confirmation-modal">
         <div class="modal-header">
           <h3>Clear Activity Logs?</h3>
         </div>
         <div class="modal-body">
           <p>This will permanently delete all recent activity history. This action cannot be undone.</p>
         </div>
         <div class="modal-footer">
           <button class="btn btn-outline" id="cancelClearBtn">Cancel</button>
           <button class="btn btn-danger" id="confirmClearBtn">Clear All</button>
         </div>
       </div>
     </div>
 
     <?php include '../core/includes/onboarding_modal.php'; ?>
 
     <!-- Category Items Modal -->
     <div id="categoryItemsModal" class="modal-overlay">
       <div class="modal-content category-items-modal">
         <div class="modal-header">
           <h3>Category Items</h3>
           <button class="modal-close" id="closeCategoryItemsBtn">&times;</button>
         </div>
         <div class="modal-body">
           <div id="categoryItemsList" class="category-items-grid">
             <!-- Food items will be loaded here -->
           </div>
           <div id="categoryItemsEmpty" class="empty-state" style="display: none;">
             No food items found in this category.
           </div>
           <div id="categoryItemsLoading" class="loading-spinner" style="display: none;">
             <div class="spinner"></div>
             <p>Loading items...</p>
           </div>
         </div>
       </div>
     </div>
 
     <!-- User Details Modal -->
     <div class="modal" id="userDetailsModal">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title">User Details</h3>
           <button class="modal-close" data-close-modal>&times;</button>
         </div>
         <div class="modal-body" id="userDetailsBody">
            <!-- Details will be loaded here -->
         </div>
         <div class="modal-footer">
           <button class="btn btn-outline" data-close-modal>Close</button>
           <button class="btn btn-primary" id="editUserFromDetailsBtn">Edit User</button>
         </div>
       </div>
     </div>
 
     <!-- Special Offer Modal -->
    <div class="modal" id="offerModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="offerModalTitle">Add Special Offer</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <form id="offerForm">
            <input type="hidden" id="offerIdInput">
            <div class="form-group">
              <label class="form-label">Offer Title</label>
              <input type="text" class="form-input" placeholder="e.g. Pizza Special" id="offerTitleInput" required>
            </div>
            <div class="form-group">
              <label class="form-label">Caption / Tag</label>
              <input type="text" class="form-input" placeholder="e.g. Buy 1 Get 1 Free!" id="offerCaptionInput">
            </div>
            <div class="form-group" style="margin-top: 1rem;">
              <label class="form-label">Display Order</label>
              <input type="number" class="form-input" value="0" id="offerOrderInput">
            </div>
            <div class="form-group" style="margin-top: 1rem;">
              <label class="form-label">Banner Image</label>
              <div class="upload-zone" id="offerUploadZone" style="border: 2px dashed hsl(var(--border)); border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s ease;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem; color: hsl(var(--muted-foreground));"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>Click to upload offer banner</p>
                <input type="file" accept="image/*" id="offerImageInput" hidden>
              </div>
              <div id="offerImagePreview" style="margin-top: 1rem; display: none; text-align: center;">
                <img src="" alt="Preview" style="max-width: 100%; border-radius: 0.5rem; height: 120px; object-fit: cover;">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-primary" onclick="handleOfferSubmit()">Save Offer</button>
        </div>
      </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal" id="galleryModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="galleryModalTitle">Add Gallery Media</h3>
          <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
          <form id="galleryForm">
            <input type="hidden" id="galleryIdInput">
            <div class="form-group">
              <label class="form-label">Title</label>
              <input type="text" class="form-input" id="galleryTitleInput" placeholder="Enter media title" required>
            </div>
            <div class="form-group">
              <label class="form-label">Caption</label>
              <textarea class="form-input" id="galleryCaptionInput" placeholder="Enter caption" rows="2"></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Tags (comma separated)</label>
              <input type="text" class="form-input" id="galleryTagsInput" placeholder="e.g. interior, food, events">
            </div>
            <div class="form-group">
              <label class="form-label">Media File</label>
              <div class="upload-zone" onclick="document.getElementById('galleryMediaInput').click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 32px; height: 32px; margin-bottom: 0.5rem; color: hsl(var(--muted-foreground));"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p id="galleryUploadText">Click to upload image or video</p>
                <input type="file" accept="image/*,video/*" id="galleryMediaInput" hidden>
              </div>
              <div id="galleryMediaPreview" style="margin-top: 1rem; display: none; text-align: center;">
                <!-- Preview injected via JS -->
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" data-close-modal>Cancel</button>
          <button class="btn btn-primary" onclick="handleGallerySubmit()">Save Media</button>
        </div>
      </div>
    </div>

    <script src="../js/admin_dashboard.js"></script>
     <script src="../js/session_timer.js"></script>
     <script src="../js/onboarding.js"></script>
  </body>
  </html>

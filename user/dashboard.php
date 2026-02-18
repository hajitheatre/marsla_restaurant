<?php
require_once __DIR__ . '/../core/includes/auth_functions.php';
require_once __DIR__ . '/../core/session_check.php';

// Ensure user is logged in (any role)
checkAuth();
preventCaching();

$user = $_SESSION['user'];
$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Marsla Restaurant</title>
    <meta name="description" content="Order delicious food from Marsla Restaurant. Fresh, authentic cuisine delivered to your doorstep.">
    <script>window.currentUser = <?php echo json_encode($_SESSION['user']); ?>;</script>
    <link rel="stylesheet" href="../css/user_dashboard.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="shortcut icon" href="../assets/favicon.svg" type="image/x-icon">
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
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="#" class="logo" onclick="showPage('home')">
                <img src="../assets/logo.svg" alt="Marsla Restaurant" height="40">
            </a>
            
            <nav class="nav-desktop">
                <a href="#" class="nav-link active" data-page="home" onclick="showPage('home')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Home
                </a>
                <a href="#" class="nav-link" data-page="orders" onclick="showPage('orders')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/></svg>
                    My Orders
                </a>
                <a href="#" class="nav-link" data-page="menu" onclick="showPage('menu')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                    View Menu
                </a>
                <a href="#" class="nav-link" data-page="settings" onclick="showPage('settings')">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings
                </a>
            </nav>

            <div class="header-actions">
                <button class="btn-icon cart-btn" onclick="toggleCart()">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>
                <button class="btn btn-outline logout-btn btn-logout-custom" onclick="handleLogout()">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Logout
                </button>
                <button class="btn-icon mobile-menu-btn" onclick="toggleMobileMenu()">
                    <svg class="icon" id="menuIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        <nav class="nav-mobile" id="mobileMenu">
            <a href="#" class="nav-link active" data-page="home" onclick="showPage('home')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Home
            </a>
            <a href="#" class="nav-link" data-page="orders" onclick="showPage('orders')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/></svg>
                My Orders
            </a>
            <a href="#" class="nav-link" data-page="menu" onclick="showPage('menu')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                View Menu
            </a>
            <a href="#" class="nav-link" data-page="settings" onclick="showPage('settings')">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </a>
            <button class="btn btn-outline logout-btn-mobile btn-logout-custom" onclick="handleLogout()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Logout
            </button>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">

            <div id="page-home" class="page active">
                <section class="welcome-section">
                    <h1 class="welcome-title">Welcome back, <span class="text-primary"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span></h1>
                    <p class="welcome-subtitle">What would you like to order today?</p>
                </section>

                <section class="stats-grid">
                    <div class="stat-card">
                        <p class="stat-value" id="totalOrdersValue">0</p>
                        <p class="stat-label">Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value" id="totalSpentValue">TZS 0</p>
                        <p class="stat-label">Total Spent</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-value" id="lastLoginValue">No orders yet</p>
                        <p class="stat-label">Last Order</p>
                    </div>
                </section>



                <section class="promo-section">
                    <h2 class="section-title">Special Offers for You!</h2>
                    <div class="slider">
                        <div class="slider-track" id="sliderTrack">
                            <!-- Dynamic slides will be loaded here -->
                        </div>
                        <button class="slider-btn slider-prev" onclick="prevSlide()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        <button class="slider-btn slider-next" onclick="nextSlide()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        <div class="slider-dots" id="sliderDots"></div>
                    </div>
                </section>

                <section class="info-section">
                    <h2 class="section-title">Important Information</h2>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div>
                                <p class="info-title">Opening Hours</p>
                                <p class="info-text">Mon-Sun: 10AM - 10PM</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <p class="info-title">Location</p>
                                <p class="info-text">Dar es Salaam, Tanzania</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <p class="info-title">Contact</p>
                                <p class="info-text">+255 123 456 789</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            </div>
                            <div>
                                <p class="info-title">Delivery</p>
                                <p class="info-text">Free over TZS 30,000</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div id="page-orders" class="page">
                <div class="page-header">
                    <h1 class="page-title">My Orders</h1>
                    <p class="page-subtitle">View your order history</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title" style="justify-content: center;">Order History</h2>
                    </div>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>

                                    <th>Order Date</th>
                                    <th>Order Items</th>
                                    <th class="text-right">Total  Amout Paid</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div id="page-menu" class="page">
                <div class="menu-filters">
                    <div class="search-container">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input type="text" id="dashboard-menu-search" class="search-input" placeholder="Search for dishes...">
                    </div>
                    <div class="category-filters" id="dashboardCategoryFilters">
                        <button class="category-btn active" data-category="">All</button>
                    </div>
                </div>

                <div class="menu-container">
                    <div id="dashboard-menu-grid" class="menu-grid">
                        <p style="text-align: center; padding: 2rem; color: var(--muted-foreground);">Loading menu...</p>
                    </div>
                </div>
            </div>

            <div id="page-settings" class="page">
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
                                <p class="card-description">Customize how the app looks</p>
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
                                            <input type="text" id="firstName" class="form-input" value="<?php echo htmlspecialchars($firstName); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="lastName">Last Name</label>
                                            <input type="text" id="lastName" class="form-input" value="<?php echo htmlspecialchars($lastName); ?>">
                                        </div>
                                    </div>
                                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                                        <div class="form-group">
                                            <label for="profileEmail">Email Address</label>
                                            <input type="email" id="profileEmail" class="form-input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="profilePhone">Phone Number</label>
                                            <input type="tel" id="profilePhone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
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
                                    <div class="form-grid">
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
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
    <aside class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2>Your Cart (<span id="cartCount">0</span> items)</h2>
            <button class="btn-icon" onclick="toggleCart()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="cart-items" id="cartItems">
            <p class="cart-empty">Your cart is empty</p>
        </div>
        <div class="cart-footer" id="cartFooter" style="display: none;">
            <div class="cart-total">
                <span>Total:</span>
                <span class="text-primary" id="cartTotal">TZS 0</span>
            </div>
            <button class="btn btn-primary btn-block checkout-btn" onclick="checkoutCart()">Complete Order</button>
        </div>
    </aside>

    <div class="toast" id="toast">
        <span id="toastMessage"></span>
    </div>

    <?php include '../core/includes/onboarding_modal.php'; ?>
    <script src="../js/user_dashboard.js"></script>

    <script src="../js/session_timer.js"></script>
    <script src="../js/onboarding.js"></script>
</body>
</html>

  <header class="navbar">
    <nav class="container-custom">
      <div class="navbar-container">
       
        <a href="index.php" class="navbar-logo">
          <img src="assets/logo.svg" alt="Marsla Restaurant">
        </a>

        <div class="navbar-links">
          <a href="index.php" class="nav-link">Home</a>
          <a href="about.php" class="nav-link">About Us</a>
          <a href="menu.php" class="nav-link">Our Menu</a>
          <a href="gallery.php" class="nav-link">Our Gallery</a>
          <a href="contact.php" class="nav-link">Contact Us</a>
        </div>

        <div class="navbar-actions">
          <button class="cart-button" aria-label="Open cart">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
              <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
            <span class="cart-badge" style="display: none;">0</span>
          </button>
          <a href="login.php" class="btn-primary login">Login</a>
        </div>

        <div class="mobile-actions">
          <button class="mobile-cart-button" aria-label="Open cart">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
              <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
            </svg>
            <span class="mobile-cart-badge" style="display: none;">0</span>
          </button>
          <button class="hamburger" aria-label="Toggle menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
          </button>
        </div>
      </div>

      <div class="mobile-menu">
        <div class="mobile-menu-links">
          <a href="index.php" class="mobile-menu-link">Home</a>
          <a href="about.php" class="mobile-menu-link">About Us</a>
          <a href="menu.php" class="mobile-menu-link">Our Menu</a>
          <a href="gallery.php" class="mobile-menu-link">Our Gallery</a>
          <a href="contact.php" class="mobile-menu-link">Contact Us</a>
        </div>
        <div class="mobile-menu-footer">
          <a href="login.php" class="btn-primary" style="width: 100%; text-align: center;">Login</a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Global Branded Loader -->
  <div id="globalLoader" class="loader-overlay">
    <div class="loader-container">
      <div class="loader-ring"></div>
      <img src="assets/favicon.png" alt="Loading..." class="loader-icon">
    </div>
  </div>

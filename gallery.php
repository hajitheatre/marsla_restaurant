<?php
$pageTitle = "Our Gallery | Marsla Restaurant";
$pageDescription = "Take a visual tour of Marsla Restaurant. View our beautiful interiors, delicious dishes, and memorable moments.";
$pageCSS = "../css/gallery.css";
?>

<?php include 'core/includes/head.php'; ?>
<?php include 'core/includes/navbar.php'; ?>
<body>


  <main>
    <section class="page-hero section-padding reveal">
      <div class="container-custom">
        <div class="page-hero-content">
          <span class="page-hero-tagline">Gallery</span>
          <h1 class="page-hero-title">Visual Journey</h1>
          <p class="page-hero-description">
            Take a visual tour through Marsla Restaurant. From our elegant interiors 
            to our beautifully plated dishes.
          </p>
        </div>
      </div>
    </section>

    <section class="gallery-filters">
      <div class="container-custom">
        <div class="gallery-filter-buttons">
          <button class="gallery-filter-btn active" data-filter="all">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
            </svg>
            All
          </button>
          <button class="gallery-filter-btn" data-filter="image">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
            </svg>
            Images
          </button>
          <button class="gallery-filter-btn" data-filter="video">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Videos
          </button>
        </div>
      </div>
    </section>

    <section class="section-padding">
      <div class="container-custom reveal">
        <div id="gallery-grid" class="gallery-grid">
          
        </div>
      </div>
    </section>
  </main>

  <div id="lightbox" class="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
    <button class="lightbox-nav lightbox-prev" onclick="navigateLightbox('prev')" aria-label="Previous">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <button class="lightbox-nav lightbox-next" onclick="navigateLightbox('next')" aria-label="Next">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>
    <div class="lightbox-content">
      <div id="lightbox-media"></div>
      <div class="lightbox-info">
        <span id="lightbox-badge" class="lightbox-badge image">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
          </svg>
          IMAGE
        </span>
        <h3 id="lightbox-title" class="lightbox-title"></h3>
        <p id="lightbox-description" class="lightbox-description"></p>
        <p id="lightbox-counter" class="lightbox-counter"></p>
      </div>
    </div>
  </div>

<?php include 'core/includes/cart.php'; ?>
<?php include 'core/includes/footer.php'; ?>

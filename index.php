<?php
$pageTitle = "Home | Marsla Restaurant";
$pageDescription = "Experience the finest Tanzanian flavors at Marsla Restaurant. Authentic flavors, warm hospitality, and unforgettable dining moments await you.";
?>

<?php include 'core/includes/head.php'; ?>
<?php include 'core/includes/navbar.php'; ?>

</head>
<body>

  <main>
    <section class="hero section-padding">
      <div class="container-custom">
        <div class="hero-grid">
        
          <div class="hero-content">
            <span class="hero-tagline">Welcome to Marsla Restaurant</span>
            <h1 class="hero-title">
              Experience the Taste of <span>Tanzania</span>
            </h1>
            <p class="hero-description">
              Discover authentic Tanzanian flavors in every dish. From traditional 
              Nyama Choma to aromatic Pilau, we bring the heart of Africa to your table.
            </p>
            <div class="hero-buttons">
              <a href="menu.php" class="btn-primary">
                View Our Menu
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </a>
              <a href="contact.php" class="btn-outline">Make a Reservation</a>
            </div>
          </div>

          <div class="hero-image-container">
            <div class="hero-carousel">
              <img src="assets/images/hero-1.jpg" alt="Marsla Restaurant dish 1" class="active">
              <img src="assets/images/hero-2.jpg" alt="Marsla Restaurant dish 2">
              <img src="assets/images/hero-3.jpg" alt="Marsla Restaurant dish 3">
              <div class="carousel-dots">
                <button class="carousel-dot active" aria-label="Go to slide 1"></button>
                <button class="carousel-dot" aria-label="Go to slide 2"></button>
                <button class="carousel-dot" aria-label="Go to slide 3"></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="features section-padding">
      <div class="container-custom reveal">
        <div class="features-header">
          <h2 class="features-title">Why Choose Us</h2>
          <p class="features-subtitle">
            At Marsla Restaurant, we combine tradition with excellence to deliver 
            an unforgettable dining experience.
          </p>
        </div>

        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
              </svg>
            </div>
            <h3 class="feature-title">Authentic Cuisine</h3>
            <p class="feature-description">Traditional Tanzanian recipes passed down through generations</p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <h3 class="feature-title">Fresh Daily</h3>
            <p class="feature-description">Ingredients sourced fresh every morning from local markets</p>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
              </svg>
            </div>
            <h3 class="feature-title">Award Winning</h3>
            <p class="feature-description">Recognized for excellence in taste and hospitality</p>
          </div>
        </div>
      </div>
    </section>

    <section class="cta section-padding">
      <div class="container-custom">
        <div class="cta-content">
          <h2 class="cta-title">Ready to Experience Marsla?</h2>
          <p class="cta-description">
            Join us for an unforgettable culinary journey through the flavors of Tanzania.
          </p>
          <div class="cta-buttons">
            <a href="menu.php" class="cta-btn-light">Explore Our Menu</a>
            <a href="contact.php" class="cta-btn-outline">Contact Us</a>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php include 'core/includes/cart.php'; ?>
<?php include 'core/includes/footer.php'; ?>

<?php
$pageTitle = "Our Menu | Marsla Restaurant";
$pageDescription = "Learn more about Marsla Restaurant and our culinary journey.";
$pageCSS = "../css/menu.css";
?>

<?php include 'core/includes/head.php'; ?>
<?php include 'core/includes/navbar.php'; ?>
<body>

  <main>
  
    <section class="page-hero section-padding reveal">
      <div class="container-custom">
        <div class="page-hero-content">
          <span class="page-hero-tagline">Our Menu</span>
          <h1 class="page-hero-title">Delicious Dishes</h1>
          <p class="page-hero-description">
            Explore our carefully crafted menu featuring authentic Tanzanian flavors 
            and international favorites.
          </p>
        </div>
      </div>
    </section>

    <section class="menu-filters">
      <div class="container-custom">
        <div class="search-container">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
          </svg>
          <input type="text" id="menu-search" class="search-input" placeholder="Search for dishes...">
        </div>

        <div class="category-filters">
          <button class="category-btn active" data-category="">All</button>
          <button class="category-btn" data-category="1">Main Dishes</button>
          <button class="category-btn" data-category="2">Grilled</button>
          <button class="category-btn" data-category="3">Seafood</button>
          <button class="category-btn" data-category="4">Rice & Biryani</button>
          <button class="category-btn" data-category="5">Beverages</button>
        </div>
      </div>
    </section>

    <section class="section-padding">
      <div class="container-custom reveal">

        <div id="menu-grid" class="menu-grid">
          
        </div>

      </div>
    </section>
  </main>

<?php include 'core/includes/cart.php'; ?>
<?php include 'core/includes/footer.php'; ?>

<?php
$pageTitle = "About Us | Marsla Restaurant";
$pageDescription = "Learn more about Marsla Restaurant and our culinary journey.";
$pageCSS = "../css/about.css";
?>

<?php include 'core/includes/head.php'; ?>
<?php include 'core/includes/navbar.php'; ?>

<body>

  <main>
    <section class="page-hero section-padding reveal">
      <div class="container-custom">
        <div class="page-hero-content">
          <span class="page-hero-tagline">About Us</span>
          <h1 class="page-hero-title">Our Story</h1>
          <p class="page-hero-description">
            Founded with a passion for authentic Tanzanian cuisine, Marsla Restaurant 
            has been serving the community since 2010, bringing families together 
            through the love of food.
          </p>
        </div>
      </div>
    </section>

    <section class="section-padding">
      <div class="container-custom">
        <div class="story-section reveal">
          <div class="story-image-container">
            <img src="assets/images/gallery-1.jpg" alt="Marsla Restaurant Interior" class="story-image">

          </div>
          <div class="story-content">
            <h2>A Legacy of Flavor</h2>
            <p>
              Marsla Restaurant was born from a simple dream: to share the rich culinary 
              heritage of Tanzania with the world. Our founders, inspired by the vibrant 
              food culture of the Swahili coast, set out to create a dining experience 
              that celebrates tradition while embracing innovation.
            </p>
            <p>
              Every dish we serve tells a story—of generations of cooks who perfected 
              these recipes, of the farmers who grow our ingredients, and of the 
              artisans who craft our spices. We believe that food is more than 
              sustenance; it's a celebration of culture, community, and connection.
            </p>
            <a href="menu.php" class="btn-primary">Explore Our Menu</a>
          </div>
        </div>
      </div>
    </section>

    <section class="values-section section-padding">
      <div class="container-custom reveal">
        <div class="values-header">
          <h2 class="values-title">Our Values</h2>
        </div>

        <div class="values-grid">
          <div class="value-card">
            <div class="value-icon primary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </div>
            <h3 class="value-title">Our Vision</h3>
            <p class="value-description">
              To be the premier destination for authentic Tanzanian cuisine, 
              recognized globally for our commitment to quality, tradition, 
              and exceptional hospitality.
            </p>
          </div>

          <div class="value-card">
            <div class="value-icon secondary">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
              </svg>
            </div>
            <h3 class="value-title">Our Mission</h3>
            <p class="value-description">
              To create memorable dining experiences by serving authentic, 
              high-quality Tanzanian dishes in a warm and welcoming environment 
              that feels like home.
            </p>
          </div>

          <div class="value-card">
            <div class="value-icon accent">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
              </svg>
            </div>
            <h3 class="value-title">Our Values</h3>
            <p class="value-description">
              Quality ingredients, authentic recipes, warm hospitality, 
              community connection, and a deep respect for our culinary heritage 
              guide everything we do.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding">
      <div class="container-custom reveal">
        <div class="chefs-header">
          <h2 class="chefs-title">Meet Our Chefs</h2>
          <p class="chefs-subtitle">
            The talented culinary artists behind every delicious dish at Marsla Restaurant.
          </p>
        </div>

        <div class="chefs-grid">
          <div class="chef-card">
            <div class="chef-image">
              <img src="assets/images/chef-1.jpg" alt="Chef Baraka Mwangi">
            </div>
            <div class="chef-info">
              <h3 class="chef-name">Chef Baraka Mwangi</h3>
              <p class="chef-title">Head Chef</p>
              <p class="chef-bio">With over 15 years of culinary experience, Chef Baraka brings authentic Tanzanian flavors to every dish. Trained in both traditional and contemporary techniques.</p>
            </div>
          </div>

          <div class="chef-card">
            <div class="chef-image">
              <img src="assets/images/chef-2.jpg" alt="Chef Amina Hassan">
            </div>
            <div class="chef-info">
              <h3 class="chef-name">Chef Amina Hassan</h3>
              <p class="chef-title">Sous Chef</p>
              <p class="chef-bio">Chef Amina specializes in fusion cuisine, blending African and international flavors. Her creative desserts are a customer favorite.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php include 'core/includes/cart.php'; ?>
<?php include 'core/includes/footer.php'; ?>

<?php
$pageTitle = "Contact Us | Marsla Restaurant";
$pageDescription = "Get in touch with Marsla Restaurant. Make a reservation, ask questions, or provide feedback. We'd love to hear from you.";
$pageCSS = "../css/contacts.css";
?>

<?php include 'core/includes/head.php'; ?>
<?php include 'core/includes/navbar.php'; ?>
<body>

  <main>
    <section class="page-hero section-padding reveal">
      <div class="container-custom">
        <div class="page-hero-content">
          <span class="page-hero-tagline">Contact Us</span>
          <h1 class="page-hero-title">Get In Touch</h1>
          <p class="page-hero-description">
            Have questions or want to make a reservation? We'd love to hear from you. 
            Reach out to us through any of the channels below.
          </p>
        </div>
      </div>
    </section>

    <section class="section-padding">
      <div class="container-custom">
        <div class="contact-grid">
          <div class="contact-form-section reveal-left">
            <h2>Send Us a Message</h2>
            <form id="contact-form" class="contact-form">
              <div class="form-row">
                <div class="form-group">
                  <label for="name">Full Name *</label>
                  <input type="text" id="name" name="name" required placeholder=" ">
                </div>
                <div class="form-group">
                  <label for="email">Email Address *</label>
                  <input type="email" id="email" name="email" required placeholder=" ">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="phone">Phone Number</label>
                  <input type="tel" id="phone" name="phone" placeholder=" ">
                </div>
                <div class="form-group">
                  <label for="subject">Subject *</label>
                  <select id="subject" name="subject" required>
                    <option value="">Select a subject</option>
                    <option value="reservation">Make a Reservation</option>
                    <option value="inquiry">General Inquiry</option>
                    <option value="feedback">Feedback</option>
                    <option value="catering">Catering Services</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="message">Message *</label>
                <textarea id="message" name="message" required placeholder=" "></textarea>
              </div>

              <button type="submit" class="btn-primary submit-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                Send Message
              </button>
            </form>
          </div>

          <div class="contact-info-section reveal-right">
            <h2>Contact Information</h2>

            <div class="contact-info-list">
              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="contact-info-content">
                  <h3>Our Location</h3>
                  <p>Bibi Titi Mohammed Road -<br>Dar es Salaam, Tanzania</p>
                </div>
              </div>

              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div class="contact-info-content">
                  <h3>Phone Number</h3>
                  <a href="tel:+255679952866">+255 679 952 866<br><a href="tel:+255614576364">+255 614 576 364</a></a>
                </div>
              </div>

              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                <div class="contact-info-content">
                  <h3>Email Address</h3>
                  <a href="mailto:marslarestaurant@gmail.com">marslarestaurant@gmail.com</a><br><a href="mailto:info@marsla.co.tz">info@marsla.co.tz</a>
                </div>
              </div>

              <div class="contact-info-item">
                <div class="contact-info-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="contact-info-content">
                  <h3>Opening Hours</h3>
                  <p>Mon - Fri: 10:00 AM - 10:00 PM<br>Sat: 9:00 AM - 11:00 PM<br>Sun: <span style="color: red;">Closed</span></p>
                </div>
              </div>
            </div>

            <div class="map-container">
              <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.6347992899327!2d39.27921027441839!3d-6.814196866648682!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x185c4b097f0d62f5%3A0x9f5833cefe49e437!2sCBE%20Cafeteria!5e0!3m2!1sen!2stz!4v1770140650459!5m2!1sen!2stz" width="600" height="450" style="border:0;" 
              allowfullscreen="" 
              loading="lazy" r
              eferrerpolicy="no-referrer-when-downgrade">
            </iframe>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  
<?php include 'core/includes/cart.php'; ?>
<?php include 'core/includes/footer.php'; ?>

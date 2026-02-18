<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create account | Marsla Restaurant</title>
  <meta name="description" content="Create your MARSLA Restaurant account">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/loader.css">
  <script src="js/loader.js"></script>
  <link rel="shortcut icon" sizes="32x32" href="assets/favicon.svg" type="image/xml + svg">
  <link rel="shortcut icon" sizes="48x48" href="assets/favicon.png" type="image/png">
  <link rel="shortcut icon" sizes="180x180" href="assets/favicon.svg" type="image/xml + svg">

</head>
<body>

  <div class="auth-container">
    <div class="auth-card">
      
      <div class="logo-section">
        <img src="assets/Logo_Column.svg" alt="MARSLA Restaurant" class="logo">
        <h1>Create Account</h1>
        <p>Join us today to start making orders!</p>
      </div>

      <div id="serverMessage" class="server-message hidden">
        <svg id="errorIcon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>

        <svg id="successIcon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        <span id="messageText"></span>
      </div>

      <form id="registerForm" class="auth-form" novalidate>
        <div class="input-group">
          <span class="input-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          </span>
          <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="Email Address" 
            autocomplete="email"
          >
          <p id="emailError" class="error-message" style="display: none;"></p>
        </div>

        <div class="input-group has-toggle">
          <span class="input-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Enter password" 
            autocomplete="new-password"
          >
          <button type="button" id="togglePassword" class="toggle-password">
            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>

            <svg class="icon-eye-off" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
          </button>
          <p id="passwordError" class="error-message" style="display: none;"></p>
        </div>

        <div class="input-group has-toggle">
          <span class="input-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
          </span>
          <input 
            type="password" 
            id="confirmPassword" 
            name="confirmPassword" 
            placeholder="Confirm password" 
            autocomplete="new-password"
          >
          <button type="button" id="toggleConfirmPassword" class="toggle-password">
            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>

            <svg class="icon-eye-off" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
          </button>
          <p id="confirmPasswordError" class="error-message" style="display: none;"></p>
        </div>

        <button type="submit" id="submitBtn" class="submit-btn secondary">
          Create Account
        </button>
      </form>

      <p class="auth-footer">
        Already have an account? <a href="login.php" class="secondary">Login</a>
      </p>
    </div>

    <center><a class="home-link" href="index.php">Return to Homepage</a></center>
  </div>

  <script src="js/script.js"></script>

  <!-- Global Branded Loader -->
  <div id="globalLoader" class="loader-overlay">
    <div class="loader-container">
      <div class="loader-ring"></div>
      <img src="assets/favicon.png" alt="Loading..." class="loader-icon">
    </div>
  </div>
</body>
</html>

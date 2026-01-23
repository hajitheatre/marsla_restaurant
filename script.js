const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function showServerMessage(message, type) {
  const serverMessage = document.getElementById('serverMessage');
  const errorIcon = document.getElementById('errorIcon');
  const successIcon = document.getElementById('successIcon');
  const messageText = document.getElementById('messageText');

  serverMessage.className = `server-message ${type}`;
  
  if (type === 'error') {
    errorIcon.style.display = 'block';
    successIcon.style.display = 'none';
  } else {
    errorIcon.style.display = 'none';
    successIcon.style.display = 'block';
  }
  
  messageText.textContent = message;

  setTimeout(() => {
    serverMessage.classList.add('leaving');
    setTimeout(() => {
      serverMessage.classList.add('hidden');
      serverMessage.classList.remove('leaving');
    }, 400);
  }, 6000);
}

function setupPasswordToggle(inputId, toggleId) {
  const input = document.getElementById(inputId);
  const toggle = document.getElementById(toggleId);

  if (!input || !toggle) return;

  const eyeIcon = toggle.querySelector('.icon-eye');
  const eyeOffIcon = toggle.querySelector('.icon-eye-off');

  toggle.addEventListener('click', () => {
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    if (isPassword) {
      eyeIcon.style.display = 'none';
      eyeOffIcon.style.display = 'block';
    } else {
      eyeIcon.style.display = 'block';
      eyeOffIcon.style.display = 'none';
    }
  });
}

function setupErrorClear(inputId, errorId) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(errorId);

  if (!input || !error) return;

  input.addEventListener('input', () => {
    input.classList.remove('error');
    error.textContent = '';
    error.style.display = 'none';
  });
}

function showFieldError(inputId, errorId, message) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(errorId);

  input.classList.add('error');
  error.textContent = message;
  error.style.display = 'block';
}

function validateLoginForm() {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  let isValid = true;

  if (!email) {
    showFieldError('email', 'emailError', 'Email is required');
    isValid = false;
  } else if (!emailRegex.test(email)) {
    showFieldError('email', 'emailError', 'Please enter a valid email address');
    isValid = false;
  }

  if (!password) {
    showFieldError('password', 'passwordError', 'Password is required');
    isValid = false;
  } else if (password.length < 6) {
    showFieldError('password', 'passwordError', 'Password must be at least 6 characters');
    isValid = false;
  }

  return isValid;
}

function validateRegisterForm() {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  let isValid = true;

  if (!email) {
    showFieldError('email', 'emailError', 'Email is required');
    isValid = false;
  } else if (!emailRegex.test(email)) {
    showFieldError('email', 'emailError', 'Please enter a valid email address');
    isValid = false;
  }

  if (!password) {
    showFieldError('password', 'passwordError', 'Password is required');
    isValid = false;
  } else if (password.length < 6) {
    showFieldError('password', 'passwordError', 'Password must be at least 6 characters');
    isValid = false;
  }

  if (!confirmPassword) {
    showFieldError('confirmPassword', 'confirmPasswordError', 'Please confirm your password');
    isValid = false;
  } else if (password !== confirmPassword) {
    showFieldError('confirmPassword', 'confirmPasswordError', 'Passwords do not match');
    isValid = false;
  }

  return isValid;
}

function handleLoginSubmit(e) {
  e.preventDefault();

  if (!validateLoginForm()) return;

  const submitBtn = document.getElementById('submitBtn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="animate-pulse-soft">Loging in...</span>';

  setTimeout(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Login';
    showServerMessage('Invalid email or password. Please try again.', 'error');
  }, 1500);
}

function handleRegisterSubmit(e) {
  e.preventDefault();

  if (!validateRegisterForm()) return;

  const submitBtn = document.getElementById('submitBtn');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="animate-pulse-soft">Creating account...</span>';


  setTimeout(() => {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Create Account';
    showServerMessage('Account created successfully! You can now login.', 'success');
    
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('confirmPassword').value = '';
  }, 1500);
}

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  setupPasswordToggle('password', 'togglePassword');
  setupPasswordToggle('confirmPassword', 'toggleConfirmPassword');

  setupErrorClear('email', 'emailError');
  setupErrorClear('password', 'passwordError');
  setupErrorClear('confirmPassword', 'confirmPasswordError');

  if (loginForm) {
    loginForm.addEventListener('submit', handleLoginSubmit);
  }

  if (registerForm) {
    registerForm.addEventListener('submit', handleRegisterSubmit);
  }
});

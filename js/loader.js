/**
 * Global Loader Controller
 */

function showLoader() {
  const loader = document.getElementById('globalLoader');
  if (loader) {
    loader.classList.add('active');
    // Prevent scrolling while loading
    document.body.style.overflow = 'hidden';
  }
}

function hideLoader() {
  const loader = document.getElementById('globalLoader');
  if (loader) {
    loader.classList.remove('active');
    // Restore scrolling
    document.body.style.overflow = '';
  }
}

// Automatically hide loader on window load as a fallback
window.addEventListener('load', () => {
    // Optional: add a small delay for better visual effect
    setTimeout(hideLoader, 300);
});

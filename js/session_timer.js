/**
 * js/session_timer.js
 * Handles client-side inactivity logout (15 minutes)
 */

(function () {
  const timeoutDuration = 900000; // 15 minutes in milliseconds
  let timeout;

  function logout() {
    window.location.href = '../logout.php?reason=timeout';
  }

  function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(logout, timeoutDuration);
  }

  // Events that reset the timer
  const events = [
    'mousemove',
    'mousedown',
    'keypress',
    'touchmove',
    'scroll',
    'click',
  ];

  events.forEach((event) => {
    document.addEventListener(event, resetTimer, true);
  });

  // Initialize timer
  resetTimer();

  console.log('Session timer initialized: 15 minutes');
})();

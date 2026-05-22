/**
 * Dashboard Theme & Interaction Manager
 * Handles dark mode toggle, menu interactions, and mobile responsiveness
 */

(function() {
  'use strict';

  /**
   * Toggle dark mode and save preference to localStorage
   */
  function toggleDarkMode() {
    const body = document.body;
    const themeToggle = document.querySelector('.theme-toggle svg');

    body.classList.toggle('dark-mode');

    if (body.classList.contains('dark-mode')) {
      themeToggle.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
      localStorage.setItem('theme', 'dark');
    } else {
      themeToggle.innerHTML = '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
      localStorage.setItem('theme', 'light');
    }
  }

  /**
   * Toggle user menu visibility
   */
  function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) {
      menu.classList.toggle('hidden');
    }
  }

  /**
   * Close user menu when clicking outside
   */
  function setupMenuClickHandler() {
    document.addEventListener('click', function(event) {
      const userMenu = document.getElementById('userMenu');
      const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');

      if (userMenu && !userButton && !userMenu.contains(event.target)) {
        userMenu.classList.add('hidden');
      }
    });
  }

  /**
   * Setup mobile menu toggle
   */
  function setupMobileMenu() {
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuButton) {
      mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.toggle('hidden');
      });
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      if (mobileMenu && mobileMenuButton &&
          !mobileButton.contains(event.target) &&
          !mobileMenu.contains(event.target)) {
        mobileMenu.classList.add('hidden');
      }
    });
  }

  /**
   * Restore theme preference on page load
   */
  function restoreThemePreference() {
    const savedTheme = localStorage.getItem('theme');
    const body = document.body;
    const themeToggle = document.querySelector('.theme-toggle svg');

    if (savedTheme === 'dark') {
      body.classList.add('dark-mode');
      if (themeToggle) {
        themeToggle.innerHTML = '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
      }
    }
  }

  /**
   * Initialize all event listeners
   */
  function init() {
    // Restore theme on page load
    restoreThemePreference();

    // Setup theme toggle
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(toggle => {
      toggle.addEventListener('click', toggleDarkMode);
    });

    // Setup menu handlers
    setupMenuClickHandler();
    setupMobileMenu();

    // Expose functions to global scope for inline onclick handlers
    window.toggleDarkMode = toggleDarkMode;
    window.toggleUserMenu = toggleUserMenu;
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

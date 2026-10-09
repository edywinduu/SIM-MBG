/**
 * ==========================================================================
 * MAIN JS - SIM-MBG
 * Global logic, navigation helpers, and shared UI interactions
 * Vanilla JS adhering to AGENTS.md rules
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initProfileDropdown();
});

/**
 * Initializes navigation bar scroll behavior and mobile menu toggle
 */
function initNavbar() {
  const header = document.querySelector('.main-header');
  const toggleBtn = document.querySelector('.mobile-nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  // Sticky header shadow on scroll
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  // Mobile menu toggle
  if (toggleBtn && navLinks) {
    toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      toggleBtn.setAttribute('aria-expanded', isOpen);
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!navLinks.contains(e.target) && !toggleBtn.contains(e.target)) {
        navLinks.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }
}

/**
 * Initializes the header profile/role-switch dropdown simulation
 */
function initProfileDropdown() {
  const profileTrigger = document.querySelector('.profile-trigger');
  const profileDropdown = document.querySelector('.profile-dropdown');

  if (profileTrigger && profileDropdown) {
    profileTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
        profileDropdown.classList.remove('show');
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && profileDropdown.classList.contains('show')) {
        profileDropdown.classList.remove('show');
      }
    });
  }
}

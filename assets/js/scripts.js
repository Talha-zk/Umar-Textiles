$(document).ready(function() {
  // Initialize mobile menu
  initMobileMenu();
  
  // Load includes if any
  var includes = $('[data-include]');
  if (includes.length > 0) {
    includes.each(function() {
      var file = 'include/' + $(this).data('include') + '.html';
      $(this).load(file, function() {
        initMobileMenu();
      });
    });
  }
  
  function initMobileMenu() {
    // Toggle mobile menu
    $('#mobileMenuToggle').off('click').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      toggleMobileMenu(true);
    });
    
    // Close menu when clicking the close button
    $('#closeMenu').off('click').on('click', function(e) {
      e.preventDefault();
      toggleMobileMenu(false);
    });
    
    // Close menu when clicking on a link
    $('.mobile-nav-links a').off('click').on('click', function() {
      toggleMobileMenu(false);
    });
    
    // Close menu when clicking outside
    $('.mobile-menu-overlay').off('click').on('click', function(e) {
      if ($(e.target).hasClass('mobile-menu-overlay')) {
        toggleMobileMenu(false);
      }
    });
    
    // Handle keyboard navigation
    $(document).off('keydown').on('keydown', function(e) {
      if (e.key === 'Escape' && $('.mobile-menu-overlay').hasClass('active')) {
        toggleMobileMenu(false);
      }
    });
    
    // Toggle mobile menu function
    function toggleMobileMenu(show) {
      const $overlay = $('.mobile-menu-overlay');
      const $menu = $('.mobile-menu-container');
      const $hamburger = $('#mobileMenuToggle');
      
      if (show) {
        // Show menu
        $overlay.addClass('active');
        $menu.addClass('active');
        $hamburger.addClass('active');
        $('body').css('overflow', 'hidden');
        
        // Focus trap for accessibility
        $menu.attr('tabindex', '0');
        $menu.focus();
      } else {
        // Hide menu
        $overlay.removeClass('active');
        $menu.removeClass('active');
        $hamburger.removeClass('active');
        $('body').css('overflow', '');
        
        // Return focus to hamburger button
        $hamburger.focus();
      }
    }
    
    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        // Close menu when resizing to desktop
        if ($(window).width() > 991.98) {
          $('.mobile-menu-overlay').removeClass('active');
          $('.mobile-menu-container').removeClass('active');
          $('#mobileMenuToggle').removeClass('active');
          $('body').css('overflow', '');
        }
      }, 250);
    });
  }
});
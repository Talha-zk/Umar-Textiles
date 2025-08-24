$(document).ready(function() {
  // Initialize mobile menu
  initMobileMenu();
  
  // Initialize form handling
  initFormHandling();
  
  // Load includes if any
  var includes = $('[data-include]');
  if (includes.length > 0) {
    includes.each(function() {
      var file = 'include/' + $(this).data('include') + '.html';
      $(this).load(file, function() {
        initMobileMenu();
        initFormHandling(); // Re-initialize forms after includes load
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
  
  // Form handling function
  function initFormHandling() {
    // Contact form handling
    $('#contactForm').off('submit').on('submit', function(e) {
      e.preventDefault();
      handleFormSubmission(this, 'contact');
    });
    
    // Footer form handling
    $('#footerForm').off('submit').on('submit', function(e) {
      e.preventDefault();
      handleFormSubmission(this, 'footer');
    });
  }
  
  // Generic form submission handler
  function handleFormSubmission(form, formType) {
    const $form = $(form);
    const $submitBtn = $form.find('button[type="submit"]');
    const $messageDiv = formType === 'footer' ? $('#footerMessage') : $('#formMessage');
    
    // Get form data
    const formData = {};
    $form.find('input, select, textarea').each(function() {
      const $field = $(this);
      const name = $field.attr('name');
      if (name) {
        formData[name] = $field.val();
      }
    });
    
    // Add form type
    formData.form_type = formType;
    
    // Show loading state
    const originalText = $submitBtn.text();
    $submitBtn.text('Sending...').prop('disabled', true);
    $messageDiv.hide();
    
    // Send form data
    fetch('process_form.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Success
        $messageDiv.removeClass('alert-danger').addClass('alert-success').html(data.message).show();
        $form[0].reset(); // Reset form
      } else {
        // Error
        $messageDiv.removeClass('alert-success').addClass('alert-danger').html(data.message).show();
      }
    })
    .catch(error => {
      // Network or other error
      $messageDiv.removeClass('alert-success').addClass('alert-danger').html('An error occurred. Please try again.').show();
      console.error('Error:', error);
    })
    .finally(() => {
      // Reset button state
      $submitBtn.text(originalText).prop('disabled', false);
      
      // Auto-hide message after 5 seconds
      setTimeout(() => {
        $messageDiv.fadeOut();
      }, 5000);
    });
  }
});
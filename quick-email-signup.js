jQuery(document).ready(function($) {

  /**
   * When user clicks on button...
   *
   */
  $('#btn-qes-submit').click( function(event) {

    /**
     * Prevent default action, so when user clicks button he doesn't navigate away from page
     *
     */
    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }

    var status = $('.qes-status');

    // Show 'Please wait' loader to user, so she/he knows something is going on
    status.html( qe_signup.processing_message ); // Add success message to results div
    status.addClass('processing'); // Add class success to results div
    status.show();

    // Collect data from inputs
    var reg_nonce = $('#qes_new_user_nonce').val();
    var reg_email  = $('#qes_email').val();

        // Do AJAX request
    $.ajax({
      url: qe_signup.ajax_url,
      type: 'post',
      data: {
        action: 'qes_add_user',
        nonce: reg_nonce,
        email: reg_email
      },
      success: function(response) {

        // If we have response
        if( response ) {

          // Hide 'Please wait' indicator
          status.hide();
          status.removeClass('processing').removeClass('success').removeClass('error');

          if( response === 'USER_CREATED' ) {
            // If user is created
            status.html( qe_signup.success_message ); // Add success message to results div
            status.addClass('success'); // Add class success to results div
            status.show(); // Show results div
            $('#qes_email').val('');

          } else {
            status.html( response ); // If there was an error, display it in results div
            status.addClass('error'); // Add class failed to results div
            status.show(); // Show results div
          }
        } else {
          status.html( qe_signup.error_message  ); // If there was an error, display it in results div
          status.addClass('error'); // Add class failed to results div
          status.show(); // Show results div
        }
      },
      error: function(response) {
        status.removeClass('processing').removeClass('success').removeClass('error');
        status.html( qe_signup.error_message  ); // If there was an error, display it in results div
        status.addClass('error'); // Add class failed to results div
        status.show(); // Show results div
      }
    });
  });
});

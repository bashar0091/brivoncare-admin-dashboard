jQuery(document).ready(function($) {
    $('.bvc-verify-carer-btn').on('click', function(e) {
        e.preventDefault();

        var $button = $(this);
        var carerId = $button.data('carer-id');
        var $row = $('#carer-row-' + carerId);
        var $spinner = $button.next('.spinner');
        var $message = $('#bvc-ajax-message');

        // Disable button and show spinner
        $button.prop('disabled', true).addClass('button-secondary');
        $spinner.css('visibility', 'visible');
        $message.hide().removeClass('notice-success notice-error');

        $.ajax({
            url: bvc_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'bvc_verify_carer',
                nonce: bvc_ajax_object.nonce,
                carer_id: carerId
            },
            success: function(response) {
                if (response.success) {
                    // Update table row with new status
                    var statusHtml = '<span class="carer-status-verified">' + response.data.new_status + '</span>';
                    $row.find('.carer-status-col').html(statusHtml);
                    
                    // Remove the button and spinner
                    $button.remove();
                    $spinner.remove();

                    // Display success message
                    $message.addClass('notice-success').html('<p>' + response.data.message + '</p>').show();
                } else {
                    // Display error message
                    $message.addClass('notice-error').html('<p>' + response.data.message + '</p>').show();
                }
            },
            error: function(xhr, status, error) {
                // Display generic error
                $message.addClass('notice-error').html('<p>An unexpected error occurred: ' + error + '</p>').show();
            },
            complete: function() {
                // Re-enable button on error, hide spinner
                if ($button.length) {
                    $button.prop('disabled', false).removeClass('button-secondary');
                }
                $spinner.css('visibility', 'hidden');
            }
        });
    });
});
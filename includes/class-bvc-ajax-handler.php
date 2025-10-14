<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class BVC_Ajax_Handler
{

    public function __construct()
    {
        // Register AJAX actions for both logged-in and non-logged-in users (though 'admin_ajax' is safer)
        add_action('wp_ajax_bvc_verify_carer', array($this, 'verify_carer_callback'));
    }

    /**
     * AJAX handler to verify a carer status via API.
     */
    public function verify_carer_callback()
    {

        // 1. Security Check: Nonce Verification
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_key($_POST['nonce']), 'bvc_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed. Nonce invalid.', 'bvc-dashboard')));
        }

        // 2. Security Check: Capability Verification
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'bvc-dashboard')));
        }

        // 3. Sanitization and Validation
        if (! isset($_POST['carer_id'])) {
            wp_send_json_error(array('message' => __('Missing Carer ID.', 'bvc-dashboard')));
        }

        $carer_id = intval($_POST['carer_id']);

        if ($carer_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid Carer ID provided.', 'bvc-dashboard')));
        }

        // 4. Perform the API Action
        $result = BVC_API_Helper::verify_carer($carer_id);

        // 5. Respond
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => sprintf(__('Failed to verify Carer ID %d. Reason: %s', 'bvc-dashboard'), $carer_id, esc_html($result->get_error_message()))
            ));
        }

        wp_send_json_success(array(
            'carer_id' => $carer_id,
            'new_status' => 'Verified',
            'message'  => sprintf(__('Carer ID %d successfully verified.', 'bvc-dashboard'), $carer_id)
        ));

        // Always exit after an AJAX call
        wp_die();
    }
}

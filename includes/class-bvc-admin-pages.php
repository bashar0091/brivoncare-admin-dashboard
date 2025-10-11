<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class BVC_Admin_Pages
{
    public function render_carers_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $redirect_url = admin_url('users.php?role=carer');
        wp_redirect($redirect_url);
        exit;
    }
    public function render_application_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $redirect_url = admin_url('admin.php?page=jet-cct-apply_for_a_job');
        wp_redirect($redirect_url);
        exit;
    }
    public function render_customers_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $redirect_url = admin_url('edit.php?post_type=elder-profile');
        wp_redirect($redirect_url);
        exit;
    }
}

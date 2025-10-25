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

?>
        <div class="wrap">
            <form method="get">
                <input type="hidden" name="page" value="job-applications" />
                <?php
                $table = new Job_Applications_Table();
                $table->prepare_items();
                $table->search_box('Search Applications', 'job-search');
                $table->display();
                ?>
            </form>
        </div>
<?php
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

    public function render_message_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        echo '<div style="padding: 20px 20px 20px 0;">';
        echo do_shortcode('[eldercare_messaging]');
        echo '</div>';
    }
}

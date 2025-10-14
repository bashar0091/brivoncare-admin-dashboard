<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}


class BVC_Admin_Helper
{

    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function user_auth()
    {
        if (current_user_can('site_admin') && !current_user_can('administrator')) {
            return true;
        }
        return false;
    }

    public function init()
    {
        add_action('admin_init', array($this, 'create_userrole'), 20);
        add_action('admin_head', array($this, 'remove_admin_menu'), 999);
        add_action('admin_head', array($this, 'redirect_site_admin'));
        add_action('admin_footer', array($this, 'conditionally_active_class_in_side_menu'));
        add_filter('login_redirect', array($this, 'login_redirect_site_admin'), 10, 3);
        add_filter('gettext', array($this, 'bvc_bulk_text_replace'), 20, 3);
        add_action('check_admin_referer', array($this, 'logout_without_confirm'), 10, 2);

        if ($this->user_auth()) {
            add_action('wp_head', array($this, 'hide_admin_toolbar'));
            add_filter('manage_users_columns', array($this, 'add_user_table_column'));
            add_filter('manage_users_columns', [$this, 'remove_table_columns'], 999);
            add_filter('manage_users_custom_column', array($this, 'show_user_table_column_content'), 10, 3);

            add_filter('manage_elder-profile_posts_columns', array($this, 'add_elder_profile_columns'));
            add_action('manage_elder-profile_posts_custom_column', array($this, 'populate_elder_profile_columns'), 10, 2);
        }
    }

    public function add_elder_profile_columns($columns)
    {
        $columns['phone'] = __('Phone', 'textdomain');
        $columns['email'] = __('Email', 'textdomain');
        return $columns;
    }

    public function populate_elder_profile_columns($column, $post_id)
    {
        if ('phone' === $column) {
            $custom_value = get_post_meta($post_id, 'phone', true);
            echo $custom_value ? esc_html($custom_value) : '—';
        }
        if ('email' === $column) {
            $custom_value = get_post_meta($post_id, 'email', true);
            echo $custom_value ? esc_html($custom_value) : '—';
        }
    }

    public function add_user_table_column($columns)
    {
        $columns['status'] = __('Status', 'bvc-dashboard');
        $columns['joined_date'] = __('Date', 'bvc-dashboard');
        return $columns;
    }

    public function show_user_table_column_content($value, $column_name, $user_id)
    {

        $user = get_userdata($user_id);

        if ($user && !empty($user->user_registered)) {
            if ($column_name === 'joined_date') {
                $joined_date = date_i18n(get_option('date_format'), strtotime($user->user_registered));
                return esc_html($joined_date);
            }
        }
        return $value;
    }

    public function remove_table_columns($columns)
    {
        $remove = ['role', 'posts', 'user_jetpack'];

        foreach ($remove as $col) {
            if (isset($columns[$col])) {
                unset($columns[$col]);
            }
        }

        return $columns;
    }

    public function redirect_site_admin()
    {
        if ($this->user_auth()) {
            $screen = get_current_screen();

            if (isset($screen->base)) {
                if ($screen->base == 'dashboard') {
                    wp_safe_redirect(admin_url('users.php?role=carer'));
                    exit;
                }
            }
        }
    }


    public function login_redirect_site_admin($redirect_to, $request, $user)
    {
        if (! is_wp_error($user) && isset($user->roles) && is_array($user->roles)) {
            if (in_array('site_admin', $user->roles)) {
                return admin_url('users.php?role=carer');
            }
        }

        return $redirect_to;
    }

    function logout_without_confirm($action, $result)
    {
        if ($action === 'log-out' && !isset($_GET['_wpnonce'])) {
            $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : site_url();
            $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
            wp_safe_redirect($location);
            exit;
        }
    }


    public function hide_admin_toolbar()
    {
        show_admin_bar(false);
    }

    public function conditionally_active_class_in_side_menu()
    {
        if (!$this->user_auth()) return;

        $screen = get_current_screen();
        $current_user_email = wp_get_current_user()->user_email;

        $active_id = '';

        switch ($screen->parent_file) {
            case 'users.php':
                $active_id = 'toplevel_page_bvc-carer';
                break;
            case 'jet-cct-apply_for_a_job':
                $active_id = 'toplevel_page_bvc-application';
                break;
            case 'edit.php?post_type=elder-profile':
                $active_id = 'toplevel_page_bvc-customer';
                break;
            default:
                $active_id = '';
        }

        if ($active_id) {
            $logout_url = wp_logout_url(site_url());
?>
            <script>
                jQuery(document).ready(function($) {
                    const active = $("#<?php echo esc_js($active_id); ?>");
                    if (active.length) {
                        active.addClass("active");
                    }

                    const main = $("#adminmenumain");
                    if (main.length) {
                        const logoutUrl = "<?php echo esc_url($logout_url); ?>";
                        main.append(`
                    <div id="bvc-footer-content">
                             <a href="<?php echo esc_url($logout_url); ?>" id="bvc-logout-btn">
                                 <span class="log-out-text">Logout</span> 
                                 <span class="dashicons dashicons-exit"></span> 
                            </a>
                        <span id="bvc-footer-email"><?php echo esc_js($current_user_email); ?></span>
                    </div>
                `);
                    }
                });
            </script>
        <?php
        }
    }

    function bvc_bulk_text_replace($translated_text, $untranslated_text, $domain)
    {

        $replacements = [
            'Search Users' => 'Search Customers'
        ];

        if (isset($replacements[$untranslated_text])) {
            $translated_text = $replacements[$untranslated_text];
        }

        return $translated_text;
    }


    public function remove_admin_menu()
    {
        $screen = get_current_screen();

        if ($this->user_auth()) {
        ?>
            <style>
                #message,
                #wp-admin-bar-root-default>li,
                #wpbody-content .pms-cross-promo,
                #wpbody-content .fs-notice,
                #wpbody-content .notice,
                #adminmenu>li {
                    display: none !important;
                }

                #wp-admin-bar-root-default #wp-admin-bar-site-name,
                #adminmenu li#toplevel_page_bvc-application,
                #adminmenu li#toplevel_page_my_brand_logo,
                #adminmenu li#toplevel_page_bvc-customer,
                #adminmenu li#toplevel_page_bvc-carer {
                    display: block !important;
                }

                #screen-meta-links {
                    display: none;
                }

                #wpadminbar {
                    display: none !important;
                }

                html.wp-toolbar {
                    padding-top: 0;
                }

                #custom-admin-logo a:focus {
                    box-shadow: none !important;
                }

                .cct-heading>h2 {
                    display: none;
                }

                #adminmenuback,
                #adminmenuwrap,
                #adminmenu {
                    background-color: #f6f6f6;
                    width: 250px;
                }

                #custom-admin-logo span {
                    margin-right: 25px;
                    margin-top: 6px;
                }

                #wpcontent,
                #wpfooter {
                    margin-left: 250px;
                }

                #adminmenu li a {
                    background: linear-gradient(135deg, #1b567f, #000175);
                    margin-bottom: 10px;
                    padding: 5px 10px !important;
                    font-family: "Poppins", Sans-serif !important;
                    font-size: 14px;
                    font-weight: 400;
                    border-radius: 8px;
                }

                #adminmenu li:hover a {
                    background: linear-gradient(135deg, #000175, #1b567f);
                }

                #adminmenu li {
                    padding: 0 20px;
                }

                #adminmenu a:focus,
                #adminmenu a:hover,
                .folded #adminmenu .wp-submenu-head:hover {
                    box-shadow: none;
                }

                #adminmenu a:focus,
                #adminmenu a:hover {
                    color: #fff;
                }

                #adminmenu div.wp-menu-image:before {
                    color: #fff;
                }

                #adminmenu a:hover div.wp-menu-image:before {
                    color: #fff;
                }

                #adminmenu li.menu-top:hover,
                #adminmenu li.opensub>a.menu-top,
                #adminmenu li>a.menu-top:focus {
                    background: transparent;
                }

                #adminmenu li.menu-top:hover a,
                #adminmenu li.opensub>a.menu-top a,
                #adminmenu li>a.menu-top:focus {
                    background: linear-gradient(135deg, #000175, #1b567f);
                    color: #fff;
                }

                #adminmenu li.menu-top:hover div.wp-menu-image:before,
                #adminmenu li.opensub>a.menu-top div.wp-menu-image:before,
                #adminmenu li>a.menu-top:focus div.wp-menu-image:before {
                    color: #fff;
                }

                #custom-admin-logo {
                    display: flex;
                    justify-content: space-between;
                    align-items: start;
                    height: 55px;
                }

                #custom-admin-logo img {
                    height: auto;
                    width: 150px;
                    display: inline;
                    margin-left: 12px;
                    transition: 0.3s each all;
                }

                #custom-admin-logo span {
                    cursor: pointer;
                }

                #bvc-footer-content {
                    position: fixed;
                    float: left;
                    z-index: 9999999999;
                    display: flex;
                    align-items: center;
                    justify-content: start;
                    gap: 20px;
                    bottom: 0;
                    width: 230px;
                    padding: 10px;
                    background: #12133a;
                    text-align: center;
                    color: #fff;
                }

                #bvc-footer-content img {
                    height: auto;
                    width: 70px;
                    display: inline;
                    margin-left: 12px;
                    border-radius: 4px;
                }

                #wpfooter {
                    display: none;
                }

                .bottom .tablenav-pages {
                    display: none;
                }

                #bvc-footer-email {
                    font-size: 12px;
                }

                #adminmenumain.bvc-collapse #custom-admin-logo {
                    justify-content: center !important;
                }

                #adminmenumain.bvc-collapse #custom-admin-logo img {
                    width: 0px !important;
                }

                #adminmenumain.bvc-collapse #bvc-footer-content img {
                    width: 50px !important;
                }

                #adminmenumain.bvc-collapse #adminmenu .wp-menu-name {
                    font-size: 0px !important;
                }

                #adminmenumain.bvc-collapse #adminmenu li a {
                    padding: 5px 3px !important;
                    padding-bottom: 23px !important;
                }

                #adminmenumain.bvc-collapse #bvc-footer-email {
                    font-size: 0px !important;
                }

                #adminmenumain.bvc-collapse #bvc-footer-content {
                    width: 60px !important;
                }

                #adminmenumain.bvc-collapse #bvc-footer-content a {
                    margin-left: 5px !important;
                    min-width: 30px !important;
                    padding-top: 10px;
                }

                #wpcontent p.search-box {
                    margin-bottom: -35px !important;
                }

                #adminmenumain.bvc-collapse #adminmenuback,
                #adminmenumain.bvc-collapse #adminmenuwrap,
                #adminmenumain.bvc-collapse #adminmenu {
                    width: 80px !important;
                }

                #adminmenumain.bvc-collapse #bvc-footer-content .log-out-text {
                    font-size: 0px !important;
                }

                #wpcontent.bvc-collapse {
                    margin-left: 100px !important;
                }

                #wpcontent,
                #adminmenumain,
                #adminmenuback,
                #adminmenuwrap,
                #adminmenu,
                #bvc-footer-content,
                #custom-admin-logo,
                #bvc-footer-content img {
                    transition: all 0.3s ease;
                }

                #adminmenumain.bvc-collapse .wp-menu-name,
                #adminmenumain.bvc-collapse #bvc-footer-email {
                    transition: all 0s ease;
                }

                #adminmenu .wp-menu-name,
                #adminmenumain #bvc-footer-email {
                    transition: all 0.3s ease;
                }

                #bvc-footer-content a#bvc-logout-btn {
                    background: #ffff;
                    padding: 6px 8px;
                    border-radius: 4px;
                    color: #12133a;
                    text-decoration: none;
                }

                #bvc-footer-content #bvc-logout-btn span {
                    transform: rotate(180deg);
                }

                .users #the-list .row-actions .view,
                .users #the-list .row-actions .view,
                .users #the-list .row-actions .resetpassword,
                .users #the-list .row-actions .capabilities {
                    display: none !important;
                }

                .type-elder-profile .row-actions .hide-if-no-js,
                .type-elder-profile .row-actions .view {
                    display: none !important;
                }

                #wpcontent.bvc-collapse {
                    margin-left: 100px !important;
                }

                #search-submit {
                    background: linear-gradient(135deg, #1b567f, #000175);
                    color: #fff;
                }

                .wp-core-ui .button-disabled,
                .wp-core-ui .button-secondary.disabled,
                .wp-core-ui .button-secondary:disabled,
                .wp-core-ui .button-secondary[disabled],
                .wp-core-ui .button.disabled,
                .wp-core-ui .button:disabled,
                .wp-core-ui .button[disabled] {
                    color: #a7aaad !important;
                    background: #f6f7f7 !important;
                }

                .wp-core-ui .button-secondary,
                .wp-core-ui .button-secondary,
                .wp-core-ui .button-secondary,
                .wp-core-ui .button,
                .wp-core-ui .button {
                    background: linear-gradient(135deg, #1b567f, #000175) !important;
                    color: #fff !important;
                }

                #the-list a {
                    color: #030b76 !important;
                }

                #the-list .delete a {
                    color: #b32d2e !important;
                }
            </style>

            <?php

            if ($screen && $screen->base === 'users') {
            ?>
                <style>
                    .search-box {
                        width: 100%;
                    }

                    .alignleft.actions,
                    .subsubsub,
                    .wp-heading-inline,
                    .page-title-action {
                        display: none !important;
                    }
                </style>
            <?php
            }

            if ($screen && $screen->base === 'user-edit') {
            ?>
                <style>
                    .page-title-action,
                    form#your-profile>* {
                        display: none !important;
                    }

                    form#your-profile .submit,
                    form#your-profile .jet-engine-user-meta-wrap {
                        display: block !important;
                    }

                    .cx-section__inner {
                        max-height: unset !important;
                    }
                </style>
            <?php
            }

            if ($screen && $screen->base === 'edit') {
            ?>
                <style>
                    .search-box {
                        width: 100%;
                    }

                    .alignleft.actions,
                    .subsubsub,
                    .wp-heading-inline,
                    .page-title-action {
                        display: none !important;
                    }
                </style>
            <?php
            }

            if ($screen && $screen->base === 'post') {
            ?>
                <style>
                    #members-cp,
                    #slugdiv,
                    #wpcode-metabox-snippets,
                    #postimagediv,
                    #litespeed_meta_boxes,
                    .cx-ui-kit__description {
                        display: none !important;
                    }
                </style>
            <?php
            }

            if ($screen && $screen->base === 'toplevel_page_jet-cct-apply_for_a_job') {
            ?>
                <style>
                    .jet-engine-cct-relations,
                    #jet_cct_export_form,
                    .page-title-action {
                        display: none !important;
                    }
                </style>
<?php
            }
        }
    }

    public function create_userrole()
    {
        // remove_role('site_manager');

        // $admin_role = get_role('administrator');
        // if ($admin_role) {
        //     add_role('site_admin', 'Site Admin', $admin_role->capabilities);
        // }
    }
}

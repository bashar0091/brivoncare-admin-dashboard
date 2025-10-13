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

        if ($this->user_auth()) {
            add_filter('manage_users_columns', array($this, 'add_user_table_column'));
            add_filter('manage_users_custom_column', array($this, 'show_user_table_column_content'), 10, 3);
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
                #adminmenu li#toplevel_page_bvc-customer,
                #adminmenu li#toplevel_page_bvc-carer {
                    display: block !important;
                }

                #screen-meta-links {
                    display: none;
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

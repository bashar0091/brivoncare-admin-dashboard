<?php

/**
 * Plugin Name: BrivonCare Admin Dashboard
 * Plugin URI: https://yourwebsite.com/
 * Description: Simplifies the BrivonCare admin dashboard with custom pages for Carers, Jobs, Customers, and Messages, fetching data from an external API.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL2
 * Text Domain: bvc-dashboard
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Define Plugin Constants
 */
define('BVC_DASHBOARD_PATH', plugin_dir_path(__FILE__));
define('BVC_DASHBOARD_URL', plugin_dir_url(__FILE__));

/**
 * Core Plugin Class Includes
 */
require_once BVC_DASHBOARD_PATH . 'includes/class-bvc-admin-pages.php';
require_once BVC_DASHBOARD_PATH . 'includes/class-bvc-ajax-handler.php';
require_once BVC_DASHBOARD_PATH . 'includes/class-bvc-admin-helper.php';

/**
 * Main Plugin Class
 */
class BrivonCare_Admin_Dashboard
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Initialize API and AJAX handlers
        new BVC_Ajax_Handler();
        new BVC_Admin_Helper();
    }

    /**
     * Register Admin Dashboard Pages
     */
    public function register_admin_pages()
    {
        $page_renderer = new BVC_Admin_Pages();
        $helper = new BVC_Admin_Helper();
        $user_auth = $helper->user_auth();

        if ($user_auth) {
            add_menu_page(
                __('Carers', 'bvc-dashboard'),
                __('Carer', 'bvc-dashboard'),
                'manage_options',
                'bvc-carer',
                array($page_renderer, 'render_carers_page'),
                'dashicons-admin-users',
                3
            );
            add_menu_page(
                __('Applications', 'bvc-dashboard'),
                __('Application', 'bvc-dashboard'),
                'manage_options',
                'bvc-application',
                array($page_renderer, 'render_application_page'),
                'dashicons-welcome-write-blog',
                4
            );
            add_menu_page(
                __('Customers', 'bvc-dashboard'),
                __('Customer', 'bvc-dashboard'),
                'manage_options',
                'bvc-customer',
                array($page_renderer, 'render_customers_page'),
                'dashicons-groups',
                5
            );
        }
    }

    /**
     * Enqueue Admin Scripts and Styles
     */
    public function enqueue_admin_assets($hook)
    {
        // Only load assets on our custom pages
        if (strpos($hook, 'bvc-') === false) {
            return;
        }

        // Enqueue Styles
        wp_enqueue_style(
            'bvc-admin-styles',
            BVC_DASHBOARD_URL . 'admin/css/bvc-admin-styles.css',
            array(),
            '1.0.0'
        );

        // Enqueue Scripts
        wp_enqueue_script(
            'bvc-admin-script',
            BVC_DASHBOARD_URL . 'admin/js/bvc-admin-script.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Pass AJAX URL and Nonce for security
        wp_localize_script(
            'bvc-admin-script',
            'bvc_ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('bvc_ajax_nonce') // Nonce generation
            )
        );
    }
}

// Initialize the plugin
new BrivonCare_Admin_Dashboard();

// Basic activation hook (optional, for DB setup, etc., not strictly needed here)
register_activation_hook(__FILE__, 'bvc_dashboard_activate');
function bvc_dashboard_activate()
{
    // Perform any necessary setup on activation
}

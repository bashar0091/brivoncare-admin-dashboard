<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Job_Applications_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'Application',
            'plural'   => 'Applications',
            'ajax'     => false
        ]);
    }

    // Default column rendering
    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'current_post_id':
                $post_id = intval($item['current_post_id']);
                if ($post_id && get_post_status($post_id)) {
                    $title = get_the_title($post_id);
                    $url   = get_edit_post_link($post_id);
                    return sprintf('<a href="%s" target="_blank">%s</a>', esc_url($url), esc_html($title));
                } else {
                    return '<em>Unknown Job</em>';
                }

            case 'cct_author_id':
                $user_id = intval($item['cct_author_id']);
                if ($user_id && get_userdata($user_id)) {
                    $user = get_userdata($user_id);
                    $display_name = $user->display_name;
                    $url = get_edit_user_link($user_id);
                    return sprintf('<a href="%s" target="_blank">%s</a>', esc_url($url), esc_html($display_name));
                } else {
                    return '<em>Unknown User</em>';
                }

            case 'cct_created':
                // Show only date
                $date = date('M d, Y', strtotime($item['cct_created']));
                return esc_html($date);

            case 'last_name':
            case 'email':
            case 'phone_number':
            case 'country':
            case 'application_status':
                return esc_html($item[$column_name]);

            default:
                return print_r($item, true);
        }
    }

    // Checkbox column
    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="ids[]" value="%s" />', $item['_ID']);
    }

    // Action column
    function column_action($item)
    {
        // Edit URL points to your edit page
        $edit_url = admin_url('admin.php?page=jet-cct-apply_for_a_job&cct_action=edit&item_id=' . $item['_ID']);

        // Delete URL with nonce for security
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=jet-cct-apply_for_a_job&cct_action=delete&item_id=' . $item['_ID']),
            'delete_application_' . $item['_ID']
        );

        return sprintf(
            '<a href="%s">Edit</a> | <a id="deletee" href="%s" onclick="return confirm(\'Are you sure you want to delete this application?\')">Delete</a>',
            esc_url($edit_url),
            esc_url($delete_url)
        );
    }


    // Columns
    function get_columns()
    {
        return [
            'cb'                 => '<input type="checkbox" />',
            'current_post_id'    => 'Job Name',
            'cct_author_id'      => 'Applicant Name',
            'email'              => 'Email',
            'phone_number'       => 'Phone',
            'country'            => 'Location',
            'application_status' => 'Status',
            'cct_created'        => 'Created',
            'action'             => 'Action',
        ];
    }

    // Bulk actions
    function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    // Prepare items
    function prepare_items()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'jet_cct_apply_for_a_job';

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $where = '';
        if ($search) {
            $where = $wpdb->prepare(
                "WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s",
                "%$search%",
                "%$search%",
                "%$search%"
            );
        }

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);

        $results = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY cct_created DESC LIMIT $per_page OFFSET $offset",
            ARRAY_A
        );

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = $results;
    }
}

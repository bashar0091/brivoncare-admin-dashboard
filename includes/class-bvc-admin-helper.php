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
        add_action('admin_init', array($this, 'switch_user_by_id'));
        add_action('jet-form-builder/custom-action/profile_compilation', array($this, 'profile_compilation'), 10, 2);


        if ($this->user_auth()) {
            add_action('wp_head', array($this, 'hide_admin_toolbar'));
            add_filter('manage_users_columns', array($this, 'add_user_table_column'));
            add_filter('manage_users_columns', [$this, 'remove_table_columns'], 999);
            add_filter('manage_elder-profile_posts_columns', [$this, 'remove_table_columns_elder'], 999);
            add_filter('manage_users_custom_column', array($this, 'show_user_table_column_content'), 10, 3);
            add_filter('gettext', array($this, 'bvc_bulk_text_replace'), 20, 3);
            add_filter('manage_elder-profile_posts_columns', array($this, 'add_elder_profile_columns'));
            add_action('manage_elder-profile_posts_custom_column', array($this, 'populate_elder_profile_columns'), 10, 2);
            add_filter('login_redirect', array($this, 'login_redirect_site_admin'), 10, 3);
            add_action('admin_footer', array($this, 'conditionally_active_class_in_side_menu'));
            add_action('admin_head', array($this, 'redirect_site_admin'));
            add_action('check_admin_referer', array($this, 'logout_without_confirm'), 10, 2);
            add_action('admin_head', array($this, 'remove_admin_menu'), 999);
            add_action('admin_head', array($this, 'get_payment_status'));
            add_action('admin_head', array($this, 'get_care_applied_data'));
            add_action('add_meta_boxes', array($this, 'render_applied_carers_list'));
            add_action('edit_user_profile', [$this, 'render_applied_elder_box_container']);
            add_action('admin_footer-user-edit.php', [$this, 'move_applied_elder_box_after_jetengine']);
            add_action('save_post', [$this, 'my_custom_post_save_percentage'], 20, 3);
        }
    }

    public function add_elder_profile_columns($columns)
    {
        $columns['custom_date'] = __('Register', 'textdomain');
        $columns['stripe_connection'] = __('Payment Method', 'textdomain');
        $columns['phone'] = __('Phone', 'textdomain');
        $columns['email'] = __('Email', 'textdomain');
        $columns['applied_carers'] = __('Applied Carers', 'textdomain');
        $columns['copy_customer_link'] = __('Copy Link', 'textdomain');
        $columns['switch_account'] = __('Switch Account', 'textdomain');
        $columns['account_completion'] = __('Completion', 'textdomain');
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
        if ('custom_date' === $column) {
            $custom_value = get_the_date('M d, Y', $post_id);
            echo $custom_value ? esc_html($custom_value) : '—';
        }

        if ('copy_customer_link' === $column) {
            $custom_value = get_permalink($post_id);
?>
            <span class="dashicons dashicons-admin-page link_copaier" data="<?php echo esc_attr($custom_value) ?>"></span>
            <div class="copy-alert-xx">Copied!</div>
            <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.link_copaier', function() {
                        var value = $(this).attr('data');
                        navigator.clipboard.writeText(value).then(function() {
                            var alertBox = $('.copy-alert-xx');
                            alertBox.addClass('show');
                            setTimeout(function() {
                                alertBox.removeClass('show');
                            }, 1500);
                        });
                    });
                });
            </script>
        <?php
        }

        if ('stripe_connection' === $column) {
            $auth_id = get_post_field('post_author', $post_id);
            $exists = $this->get_payment_status($auth_id);
            echo $exists
                ? '<span style="font-weight: bold; color: green;">Added</span>'
                : '<span style="color: red;">Not Added</span>';
        }

        if ('applied_carers' === $column) {
            $exists = $this->get_care_applied_data($post_id);
            $length = $exists ? count($exists) : 0;
        ?>
            <span><?php echo esc_html($length); ?></span>
        <?php
        }

        if ('switch_account' === $column) {
            $user_id = get_post_field('post_author', $post_id);
        ?>
            <a id="custom-switch-account" href="<?php echo esc_url(admin_url('/edit.php?post_type=elder-profile&switch_user_id=' . $user_id)); ?>">Login As Customer</a>
        <?php
        }

        if ('account_completion' === $column) {
            $user_id = get_post_field('post_author', $post_id);
            $account_completion = get_user_meta($user_id, 'profile_compilation_percent', true);
            $percentage = is_numeric($account_completion) ? intval($account_completion) : 0;
        ?>
            <div class="shakil-circle-wrap" data-percentage="<?php echo esc_attr($percentage); ?>">
                <div class="shakil-inner-circle">0%</div>
            </div>
            <?php
            ?>
            <style>
                .shakil-circle-wrap {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: conic-gradient(#ddd 0% 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
                    margin: 0 auto;
                    -webkit-mask-image: radial-gradient(circle, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 0) 100%);
                    mask-image: radial-gradient(circle, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 0) 100%);
                }

                .shakil-inner-circle {
                    width: 35px;
                    height: 35px;
                    background: #fff;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: bold;
                    color: #333;
                    position: absolute;
                    user-select: none;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('.shakil-circle-wrap').forEach(circleWrap => {
                        const percentage = parseInt(circleWrap.getAttribute('data-percentage')) || 0;
                        const innerCircle = circleWrap.querySelector('.shakil-inner-circle');

                        circleWrap.style.background = `conic-gradient(
            #000175 0% ${percentage}%, 
            #ddd ${percentage}% 100%
        )`;

                        innerCircle.textContent = `${percentage}%`;
                    });
                });
            </script>
        <?php
        }
    }

    public function get_payment_status($auth_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'svp_cards';

        $exists = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d",
            $auth_id
        ));

        return (bool) $exists;
    }

    public function get_elder_applied_data($cct_author_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'jet_cct_apply_for_a_job';

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT current_post_id FROM $table WHERE cct_author_id = %d", $cct_author_id),
            ARRAY_A
        );

        if (empty($rows)) return false;

        $meta_fields = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'city',
            'dropdown',
        ];

        $results = [];

        foreach ($rows as $row) {
            if (empty($row['current_post_id'])) continue;

            $post_id = (int) $row['current_post_id'];
            $meta_data = [];

            foreach ($meta_fields as $key) {
                $meta_data[$key] = get_post_meta($post_id, $key, true);
            }

            $results[] = ['meta' => $meta_data];
        }

        return !empty($results) ? $results : false;
    }


    public function render_applied_elder_box_container($user)
    {
        $user_id = $user->ID;
        $data = $this->get_elder_applied_data($user_id);
        ?>
        <div id="user-applied-elders-wrapper" class="user-applied-elders-wrapper" style="display:none;">
            <h2 class="applied-elder-title">Applied Customers</h2>
            <div id="applied_elder_box" class="applied-elder-box">
                <?php if (!empty($data)) : ?>
                    <table class="applied-elder-table">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>City</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $elder) :
                                $meta = isset($elder['meta']) ? $elder['meta'] : [];
                            ?>
                                <tr>
                                    <td><?php echo esc_html($meta['first_name'] ?? ''); ?></td>
                                    <td><?php echo esc_html($meta['last_name'] ?? ''); ?></td>
                                    <td><?php echo esc_html($meta['email'] ?? ''); ?></td>
                                    <td><?php echo esc_html($meta['phone'] ?? ''); ?></td>
                                    <td><?php echo esc_html($meta['dropdown'] ?? ''); ?></td>
                                    <td><?php echo esc_html($meta['city'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="no-applied-elder">No applied job found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php
    }


    public function move_applied_elder_box_after_jetengine()
    {
    ?>
        <script>
            jQuery(document).ready(function($) {
                const box = $('#user-applied-elders-wrapper');
                if (!box.length) return;

                box.css('display', 'none');

                const jetMetaWrap = $('.jet-engine-user-meta-wrap');

                if (jetMetaWrap.length) {
                    box.insertAfter(jetMetaWrap);
                } else {
                    $('form#your-profile').append(box);
                }
                box.fadeIn(300);
            });
        </script>
    <?php
    }



    public function get_care_applied_data($current_post_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'jet_cct_apply_for_a_job';
        $data = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE current_post_id = %d",
            $current_post_id
        ), ARRAY_A);

        return !empty($data) ? $data : null;
    }

    public function render_applied_carers_list()
    {
        add_meta_box(
            'applied_carers_box',
            'Applied Carers',
            [$this, 'render_applied_carers_box_callback'],
            'elder-profile',
            'normal',
            'default'
        );
    }

    public function render_applied_carers_box_callback($post)
    {

        $data = $this->get_care_applied_data($post->ID);
    ?>
        <?php
        $data = $this->get_care_applied_data($post->ID);
        ?>

        <div id="applied_carers_wrapper" class="applied-carers-wrapper">
            <?php if (!$data || count($data) === 0) : ?>
                <p class="no-jobs">No jobs</p>
            <?php else : ?>
                <table class="carers-table">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Availability</th>
                            <th>Previous Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $index => $carer) :
                            $availability = !empty($carer['availability']) ? maybe_unserialize($carer['availability']) : [];
                            $previous_job_role = !empty($carer['previous_job_role']) ? maybe_unserialize($carer['previous_job_role']) : [];
                            $row_class = $index % 2 === 0 ? 'even' : 'odd';
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <?php $photo = !empty($carer['profile_photo_for_job']) ? maybe_unserialize($carer['profile_photo_for_job']) : null;
                                ?>
                                <td>
                                    <?php if (!empty($photo['url'])) : ?>
                                        <img src="<?php echo esc_url($photo['url']); ?>" alt="Profile Photo" class="carer-photo" />
                                    <?php else : ?>
                                        <span class="no-photo">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                </td>
                                <td><?php echo esc_html($carer['first_name'] . ' ' . $carer['last_name']); ?></td>
                                <td><?php echo esc_html($carer['email']); ?></td>
                                <td><?php echo esc_html($carer['phone_number']); ?></td>
                                <td>
                                    <?php if (!empty($availability)) : ?>
                                        <ul class="list">
                                            <?php foreach ($availability as $av) : ?>
                                                <li><?php echo esc_html($av); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($previous_job_role)) : ?>
                                        <ul class="list">
                                            <?php foreach ($previous_job_role as $role) : ?>
                                                <li><?php echo esc_html($role); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php
    }


    public function add_user_table_column($columns)
    {
        $columns['status'] = __('Status', 'bvc-dashboard');
        $columns['applied_customers'] = __('Applied Customers', 'bvc-dashboard');
        $columns['joined_date'] = __('Date', 'bvc-dashboard');
        $columns['switch_account'] = __('Switch Account', 'bvc-dashboard');
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

            if ($column_name === 'applied_customers') {
                $data = $this->get_elder_applied_data($user->ID);
                $length = !empty($data) ? count($data) : 0;
                return esc_html($length);
            }

            if ($column_name === 'switch_account') {
                ob_start();
        ?>
                <a id="custom-switch-account" href="<?php echo esc_url(admin_url('/users.php?role=carer&switch_user_id=' . $user->ID)); ?>">Login As Carer</a>
            <?php
                return ob_get_clean();
            }
        }
        return $value;
    }

    public function remove_table_columns($columns)
    {
        $remove = ['role', 'posts', 'user_jetpack', 'docs'];

        foreach ($remove as $col) {
            if (isset($columns[$col])) {
                unset($columns[$col]);
            }
        }

        return $columns;
    }

    public function remove_table_columns_elder($columns)
    {
        $remove = ['date'];

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

    public function switch_user_by_id($user_id)
    {
        $user_id = isset($_GET['switch_user_id']) ? $_GET['switch_user_id'] : '';

        if (empty($user_id)) {
            return;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        if (!$this->user_auth()) {
            return false;
        }

        wp_logout();

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', $user->user_login, $user_id);

        // Redirect based on role
        if (in_array('carer', (array) $user->roles)) {
            wp_redirect(site_url('/carer-application'));
        } elseif (in_array('elder', (array) $user->roles)) {
            wp_redirect(site_url('/customer-application'));
        } else {
            wp_redirect(site_url('/'));
        }
        exit;
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

    public function profile_compilation($record, $handler)
    {
         $target_form_id = 3850;
        $user_id = get_current_user_id();
        if (! $user_id) {
            return;
        }
        
            $form_id = null;
        if (is_object($record) && method_exists($record, 'get_form_id')) {
            $form_id = $record->get_form_id();
        } elseif (is_array($record) && isset($record['form_id'])) {
            $form_id = $record['form_id'];
        }
    
        if ($form_id != $target_form_id) {
            return;
        }

        $allowed_fields = [
            'first_name',
            'last_name',
            'phone_mobile',
            'email',
            'tell_us_about_care',
            'how_many_individuals',
            'second_individual_dob',
            'second_individual_gender',
            'care_type_required',
            'care_recipient_dob',
            'gender',
            'mobility_level',
            'experienced_falls',
            'personal_care_help',
            'prescribed_specialist_medication',
            'experienced_seizure',
            'alcohol_substance_dependency',
            'ongoing_supervision_safety',
            'pet_in_home',
            'care_plan_type',
            'administer_medication_discreetly',
            'medication_support_level',
            'medical_conditions',
            'additional_medical_support',
            'healthcare_professional_support',
            'conditions_daily_life_impact',
            'medical_equipment_use',
            'risk_factors_present',
            'swallowing_difficulty',
            'nutrition_support_level',
            'dietary_preferences',
            'health_confirmations',
            'choking_aspiration_risk',
            'refusing_care_history',
            'serious_mental_health_conditions',
            'aggression_safety_concern',
            'safety_considerations',
            'hobbies_interests',
            'carer_gender_preference',
            'carer_driving_needed',
            'wifi_available',
            'care_recipient_smokes',
            'accept_smoker_carer',
            'carer_rest_breaks',
            'surveillance_privacy',
            'overnight_responsibilities',
            'family_involvement',
            'care_location_address_line_1',
            'care_location_city',
            'care_location_postal_code'
        ];

        $fields = [];
        if (is_object($record) && method_exists($record, 'get_fields')) {
            $fields = $record->get_fields();
        } elseif (is_array($record) && isset($record['fields'])) {
            $fields = $record['fields'];
        } elseif (is_array($record)) {
            $fields = $record;
        }

        if (empty($fields)) {
            return;
        }

        $filled_count = 0;
        foreach ($allowed_fields as $field_key) {
            if (! isset($fields[$field_key])) {
                continue;
            }

            $value = $fields[$field_key];
            if (is_array($value)) {
                $value = array_filter($value);
                if (! empty($value)) {
                    $filled_count++;
                }
            } elseif (! empty(trim($value))) {
                $filled_count++;
            }
        }

        $total_fields = count($allowed_fields);
        $percentage = $total_fields > 0 ? round(($filled_count / $total_fields) * 100) : 0;

        update_user_meta($user_id, 'profile_compilation_percent', $percentage);
    }


    public function my_custom_post_save_percentage($post_id, $post, $update)
    {

        if ('elder-profile' !== $post->post_type) return;

        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

        remove_action('save_post', 'my_custom_post_save_percentage', 20);

        $author_id = $post->post_author;

        $all_fields = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'tell_us_about_the_person_needing_care',
            'checkbox_1',
            'checkbox_2',
            'input_text',
            'date_of_birth_of_the_second_individual_',
            'gender_of_the_second_individual',
            'what_type_of_care_is_required',
            'does_the_care_recipient_need_help_with_personal_care_eg_washing_dressing_hygiene',
            'address_line_1',
            'city',
            'zip',
            'care_start_date_flexible',
            'datetime',
            'dropdown',
            'datetime_1',
            'dropdown_1',
            'does_the_care_recipient_have_a_pet_in_the_home',
            'is_this_an_ongoing_or_short-term_care_plan',
            'what_is_their_level_of_mobility',
            'has_the_individual_experienced_any_falls_in_the_last_year',
            'please_share_any_details_that_may_help_us_understand_why_these_falls_happened',
            'does_the_individual_need_assistance_with_personal_care_tasks',
            'is_the_care_recipient_prescribed_any_specialist_or_controlled_medication',
            'has_the_care_recipient_had_a_seizure_in_the_last_12_months',
            'does_the_care_recipient_have_a_history_of_dependency_on_alcohol_substances_or_medication',
            'does_the_care_recipient_need_ongoing_supervision_for_safety_is_there_a_risk_of_wandering',
            'will_the_carer_need_to_administer_medication_discreetly_eg_mixed_into_fooddrink',
            'does_the_care_recipient_have_any_diagnosed_medical_conditions_note_this_helps_us_match_you_with_carers_experienced_in_similar_conditions',
            'what_level_of_support_is_needed_with_medication',
            'additional_medical_support_for_this_condition',
            'if_the_care_recipient_is_receiving_treatment_or_support_from_a_healthcare_professional_please_select_all_that_apply',
            'how_do_these_conditions_affect_the_care_recipients_daily_life',
            'does_the_care_recipient_use_any_medical_equipment_eg_walking_aid_hoist_oxygen',
            'has_the_care_recipient_experienced_a_seizure_in_the_last_12_months',
            'are_any_of_the_following_risk_factors_present',
            'does_the_care_recipient_have_difficulty_swallowing_food',
            'what_level_of_support_is_needed_for_nutrition',
            'is_the_care_recipient_at_risk_of_choking_or_aspiration',
            'dietary_preferences_in_the_household_tick_all_that_apply',
            'does_the_individual_have_a_history_of_refusing_care_or_medication',
            'are_there_any_serious_mental_health_conditions_requiring_urgent_intervention_eghospitalization_self-harm_risk_psychosis_paranoia',
            'what_are_your_hobbies_and_interests',
            'does_the_individual_prefer_a_male_or_female_carer',
            'do_you_need_a_carer_who_can_drive',
            'is_there_wifi_available_at_the_property',
            'do_you_confirm_the_following',
            'does_the_care_recipient_smoke',
            'would_you_accept_a_carer_who_smokes_cigarettes_cigars_or_vaping',
            'the_carer_will_be_given_appropriate_rest_breaks',
            'there_are_no_surveillance_cameras_in_the_carers_sleeping_area_or_private_spaces',
            'overnight_responsibilities__should_the_carer_expect_to_be_woken_up_during_the_night',
            'family_involvement__will_family_members_be_living_in_the_home_with_the_care_recipient'
        ];

        $filled_count = 0;
        $total_fields = count($all_fields);

        foreach ($all_fields as $key) {

            $value = get_post_meta($post_id, $key, true);

            if (is_array($value)) {
                $true_values = array_filter($value, function ($v) {
                    return $v === true || $v === 'true' || $v === 1 || $v === '1';
                });
                if (!empty($true_values)) $filled_count++;
            } elseif (!empty(trim($value))) {
                $filled_count++;
            }
        }

        $percentage = $total_fields > 0 ? round(($filled_count / $total_fields) * 100) : 0;

        update_user_meta($author_id, 'profile_compilation_percent', $percentage);

        add_action('save_post', 'my_custom_post_save_percentage', 20, 3);
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
            case 'bvc-application':
                $active_id = 'toplevel_page_bvc-application';
                break;
            case 'edit.php?post_type=elder-profile':
                $active_id = 'toplevel_page_bvc-customer';
                break;
            case 'bvc-message':
                $active_id = 'toplevel_page_bvc-message';
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
            'Search Users' => 'Search Carers',
            'Search Posts' => 'Search Customers',
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
                #adminmenu li#toplevel_page_bvc-carer,
                #adminmenu li#toplevel_page_bvc-message {
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
                
                #adminmenumain #bvc-footer-email {
                    display: inline-block;
                    max-width: 120px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    vertical-align: middle;
                }
                
                #adminmenumain #bvc-footer-email:hover {
                        overflow: visible;
                        white-space: normal;
                        background: #fff;
                        position: relative;
                        z-index: 10;
                        background-color: transparent;
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
                    color: #030b76;
                }

                #the-list .delete a,
                #the-list a#deletee,
                .row-actions .trash a {
                    color: #b32d2e !important;
                }

                li.current a.menu-top {
                    background: linear-gradient(135deg, #1b567f, #000175) !important;
                }

                #adminmenu li a::after {
                    content: unset !important;
                }

                .applications #the-list tr td {
                    padding-top: 15px;
                    padding-bottom: 15px;
                }

                #wpbody-content a.page-title-action,
                #wpbody-content .wrap .wp-heading-inline,
                #wpbody-content .misc-pub-visibility a,
                #wpbody-content .misc-pub-curtime a,
                #wpbody-content #wp-content-media-buttons,
                #wpbody-content #edit-slug-buttons,
                #wpbody-content #minor-publishing-actions {
                    display: none !important;
                }

                #minor-publishing-actions .preview .button,
                #misc-publishing-actions .misc-pub-post-status a {
                    display: none !important;
                }

                .cx-checkbox-input[checked]+.cx-checkbox-item {
                    background-color: #030b76 !important;
                }

                #wpbody-content .search-box {
                    float: left !important;
                    width: 700px !important;
                }

                /* Wrapper */
                #applied_carers_wrapper {
                    font-family: Arial, sans-serif;
                    width: 100%;
                }

                /* No jobs */
                #applied_carers_wrapper .no-jobs {
                    color: #12133a;
                    font-weight: bold;
                }

                /* Table */
                #applied_carers_wrapper .carers-table {
                    width: 100% !important;
                    border-collapse: collapse;
                    text-align: left;
                }

                /* Table header */
                #applied_carers_wrapper .carers-table thead tr {
                    background-color: #12133a;
                    color: #fff;
                }

                #applied_carers_wrapper .carers-table th,
                #applied_carers_wrapper .carers-table td {
                    padding: 8px;
                    vertical-align: middle;
                }

                /* Striped rows */
                #applied_carers_wrapper .carers-table tbody tr {
                    background-color: #f5f5f5;
                }

                /* Lists inside cells */
                #applied_carers_wrapper .carers-table .list {
                    margin: 4px 0 0 0px;
                    padding: 0;
                    display: flex;
                    flex-direction: row;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                #applied_carers_wrapper .carers-table .list li {
                    background-color: #fff;
                    padding: 4px 8px;
                    border-radius: 30px;
                }

                #applied_carers_wrapper .carer-photo {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                }

                #applied_carers_box .inside {
                    padding: 0 !important;
                    margin: 0px 0 0 !important;
                }

                form#your-profile>#user-applied-elders-wrapper {
                    display: block !important;
                }

                #user-applied-elders-wrapper {
                    margin-top: 30px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin-right: 20px;
                }

                .applied-elder-title {
                    font-size: 18px;
                    font-weight: 600;
                    color: #12133a;
                    margin-bottom: 15px;
                }

                .applied-elder-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .applied-elder-table th,
                .applied-elder-table td {
                    border: 1px solid #e0e0e0;
                    padding: 10px;
                    text-align: center;
                    vertical-align: middle;
                }

                .applied-elder-table tr:nth-child(even) {
                    background-color: #f8f9fc;
                }

                .applied-elder-photo {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    object-fit: cover;
                }

                a#custom-switch-account {
                    background: linear-gradient(135deg, #1b567f, #000175) !important;
                    color: #fff !important;
                    padding: 8px 14px;
                    border-radius: 30px;
                    transition: all 0.3s ease;
                }

                a#custom-switch-account:hover {
                    background: linear-gradient(135deg, #000175, #1b567f);
                }

                [data-wp-lists="list:user"] tr td {
                    vertical-align: middle !important;
                }

                .type-elder-profile td {
                    vertical-align: middle !important;
                }

                .link_copaier:before {
                    background: linear-gradient(135deg, #1b567f, #000175);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    cursor: pointer;
                }

                .copy-alert-xx {
                    position: fixed;
                    top: 20px;
                    right: -300px;
                    background: linear-gradient(135deg, #1b567f, #000175);
                    color: #fff;
                    padding: 8px 35px;
                    border-radius: 4px;
                    z-index: 9999;
                    font-weight: 500;
                    transition: all 0.5s ease;
                }

                .copy-alert-xx.show {
                    right: 20px;
                }

                #account_completion {
                    text-align: center !important;
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

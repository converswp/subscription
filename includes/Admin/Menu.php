<?php

namespace SpringDevs\Subscription\Admin;

use function Avifinfo\read;

/**
 * Menu class
 *
 * @package SpringDevs\Subscription\Admin
 */
class Menu {

    /**
     * Template loader instance
     *
     * @var TemplateLoader
     */
    private $template_loader;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->template_loader = new TemplateLoader();
        add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_wp_subscription_bulk_action', array( $this, 'handle_bulk_action_ajax' ) );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'wp-subscription-admin',
            WP_SUBSCRIPTION_ASSETS . '/css/admin.css',
            array(),
            WP_SUBSCRIPTION_VERSION
        );
        
        // Enqueue admin JavaScript for subscription list functionality
        wp_enqueue_script(
            'sdevs_subscription_admin',
            WP_SUBSCRIPTION_ASSETS . '/js/admin.js',
            array( 'jquery' ),
            WP_SUBSCRIPTION_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script(
            'sdevs_subscription_admin',
            'wp_subscription_ajax',
            array(
                'nonce' => wp_create_nonce( 'wp_subscription_bulk_action_nonce' ),
                'ajaxurl' => admin_url( 'admin-ajax.php' )
            )
        );
    }

    /**
     * Create Subscriptions Menu.
     */
    public function create_admin_menu() {
        $parent_slug = 'wp-subscription';
        // Determine if the menu is active
        $is_active = isset($_GET['page']) && strpos($_GET['page'], 'wp-subscription') === 0;
        $icon_url = $is_active
            ? WP_SUBSCRIPTION_ASSETS . '/images/icons/subscription-20.png'
            : WP_SUBSCRIPTION_ASSETS . '/images/icons/subscription-20-gray.png';
        
        // Main menu
        // Important
        // Dont change these name here, it will break css,js selectors. 
        add_menu_page(
            __( 'WP Subscription', 'wp_subscription' ),
            __( 'WP Subscription', 'wp_subscription' ),
            'manage_woocommerce',
            $parent_slug,
            array( $this, 'render_subscriptions_page' ),
            $icon_url,
            40
        );

        // Subscriptions List
        add_submenu_page(
            $parent_slug,
            __( 'Subscriptions', 'wp_subscription' ),
            __( 'Subscriptions', 'wp_subscription' ),
            'manage_woocommerce',
            $parent_slug,
            array( $this, 'render_subscriptions_page' )
        );

        // Stats Overview
        add_submenu_page(
            $parent_slug,
            __( 'Stats', 'wp_subscription' ),
            __( 'Stats', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-stats',
            array( $this, 'render_stats_page' )
        );

        // Settings
        add_submenu_page(
            $parent_slug,
            __( 'Settings', 'wp_subscription' ),
            __( 'Settings', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-settings',
            array( $this, 'render_settings_page' )
        );

        // Support
        add_submenu_page(
            $parent_slug,
            __( 'Support', 'wp_subscription' ),
            __( 'Support', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription-support',
            array( $this, 'render_support_page' )
        );

        // Add WPSubscription link under WooCommerce menu
        add_submenu_page(
            'woocommerce',
            __( 'WP Subscription', 'wp_subscription' ),
            __( 'WP Subscription', 'wp_subscription' ),
            'manage_woocommerce',
            'wp-subscription',
            array( $this, 'render_subscriptions_page' )
        );
    }

    /**
     * Render the admin header
     */
    public function render_admin_header() {
        $this->template_loader->load_admin_template( 'header' );
    }

    /**
     * Render the admin footer
     */
    public function render_admin_footer() {
        $this->template_loader->load_admin_template( 'footer' );
    }

    /**
     * Render Subscriptions page
     */
    public function render_subscriptions_page() {
        $this->render_admin_header();

        // Handle filters
        $status = isset($_GET['subscrpt_status']) ? sanitize_text_field($_GET['subscrpt_status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '';
        $per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

        // Handle form submissions (both filters and bulk actions)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle bulk actions
            if (isset($_POST['bulk_action']) || isset($_POST['bulk_action2'])) {
                $bulk_action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : sanitize_text_field($_POST['bulk_action2']);
                $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : sanitize_text_field($_POST['action2']);
                
                if ($bulk_action && $action && $action !== '-1' && isset($_POST['subscription_ids']) && is_array($_POST['subscription_ids'])) {
                    $subscription_ids = array_map('intval', $_POST['subscription_ids']);
                    
                    if ($action === 'trash') {
                        foreach ($subscription_ids as $sub_id) {
                            wp_trash_post($sub_id);
                        }
                    } elseif ($action === 'restore') {
                        foreach ($subscription_ids as $sub_id) {
                            wp_untrash_post($sub_id);
                        }
                    } elseif ($action === 'delete') {
                        foreach ($subscription_ids as $sub_id) {
                            wp_delete_post($sub_id, true);
                        }
                    }
                    
                    wp_safe_redirect(admin_url('admin.php?page=wp-subscription'));
                    exit;
                }
            }
            
            // Handle filter form submission
            if (isset($_POST['filter_action'])) {
                $filter_params = array();
                
                if (!empty($_POST['subscrpt_status'])) {
                    $filter_params['subscrpt_status'] = sanitize_text_field($_POST['subscrpt_status']);
                }
                if (!empty($_POST['date_filter'])) {
                    $filter_params['date_filter'] = sanitize_text_field($_POST['date_filter']);
                }
                if (!empty($_POST['s'])) {
                    $filter_params['s'] = sanitize_text_field($_POST['s']);
                }
                if (!empty($_POST['per_page'])) {
                    $filter_params['per_page'] = intval($_POST['per_page']);
                }
                
                $redirect_url = add_query_arg($filter_params, admin_url('admin.php?page=wp-subscription'));
                wp_safe_redirect($redirect_url);
                exit;
            }
        }

        // Handle individual actions
        if (isset($_GET['action']) && !empty($_GET['sub_id'])) {
            $sub_id = intval($_GET['sub_id']);
            $action = sanitize_text_field($_GET['action']);
            
            if ($action === 'duplicate') {
                $post = get_post($sub_id);
                if ($post && $post->post_type === 'subscrpt_order') {
                    $new_post = [
                        'post_title'   => $post->post_title . ' (Copy)',
                        'post_content' => $post->post_content,
                        'post_status'  => 'draft',
                        'post_type'    => 'subscrpt_order',
                    ];
                    $new_id = wp_insert_post($new_post);
                    if ($new_id) {
                        $meta = get_post_meta($sub_id);
                        foreach ($meta as $key => $values) {
                            foreach ($values as $value) {
                                add_post_meta($new_id, $key, maybe_unserialize($value));
                            }
                        }
                    }
                }
                wp_safe_redirect(admin_url('admin.php?page=wp-subscription'));
                exit;
            } elseif ($action === 'trash') {
                // Move to trash
                wp_trash_post($sub_id);
                wp_safe_redirect(admin_url('admin.php?page=wp-subscription'));
                exit;
            } elseif ($action === 'restore') {
                // Restore from trash
                wp_untrash_post($sub_id);
                wp_safe_redirect(admin_url('admin.php?page=wp-subscription'));
                exit;
            } elseif ($action === 'delete') {
                // Permanent delete
                wp_delete_post($sub_id, true);
                wp_safe_redirect(admin_url('admin.php?page=wp-subscription'));
                exit;
            } elseif ($action === 'clean_trash') {
                // Clean all trash items
                $trash_posts = get_posts([
                    'post_type' => 'subscrpt_order',
                    'post_status' => 'trash',
                    'numberposts' => -1,
                    'fields' => 'ids'
                ]);
                
                foreach ($trash_posts as $trash_id) {
                    wp_delete_post($trash_id, true);
                }
                
                wp_safe_redirect(admin_url('admin.php?page=wp-subscription&subscrpt_status=trash'));
                exit;
            }
        }

        $args = [
            'post_type'      => 'subscrpt_order',
            'post_status'    => 'any',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ($status) {
            $args['post_status'] = $status;
        }
        // Search only by subscription ID
        if ($search !== '') {
            if (is_numeric($search)) {
                $args['p'] = intval($search);
            } else {
                // If not numeric, return no results
                $args['post__in'] = array(0);
            }
        }
        // Dynamic date filter (YYYY-MM)
        if ($date_filter && preg_match('/^\d{4}-\d{2}$/', $date_filter)) {
            $year = substr($date_filter, 0, 4);
            $month = substr($date_filter, 5, 2);
            $args['date_query'][] = [
                'year'  => intval($year),
                'month' => intval($month),
            ];
        }

        $query = new \WP_Query($args);
        $subscriptions = $query->posts;
        $total = $query->found_posts;
        $max_num_pages = $query->max_num_pages;

        // Get all possible statuses for filter dropdown
        $all_statuses = get_post_stati(['show_in_admin_all_list' => true], 'objects');

        include dirname(__FILE__) . '/views/subscription-list.php';
        
        $this->render_admin_footer();
    }

    /**
     * Render Stats page
     */
    public function render_stats_page() {
        $this->render_admin_header();

        if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) {
            $this->template_loader->load_admin_template( 'stats' );
        } else {
            // Allow pro plugin to override the entire stats page content
            do_action('wp_subscription_render_stats_page');
        }

        $this->render_admin_footer();
    }

    /**
     * Render Settings page
     */
    public function render_settings_page() {
        $this->render_admin_header();
        $this->template_loader->load_admin_template( 'settings' );
        $this->render_admin_footer();
    }

    /**
     * Render Support page
     */
    public function render_support_page() {
        $this->render_admin_header();
        $this->template_loader->load_admin_template( 'support' );
        $this->render_admin_footer();
    }

    /**
     * Render the legacy subscriptions list page (WP_List_Table based)
     */
    public function render_legacy_subscriptions_page() {
        // No longer needed, as the menu now links directly to the post type list.
    }

    /**
     * Handle bulk action AJAX
     */
    public function handle_bulk_action_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp_subscription_bulk_action_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wp_subscription' ) ) );
        }
        
        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'wp_subscription' ) ) );
        }
        
        // Get action and subscription IDs
        $bulk_action = sanitize_text_field( $_POST['bulk_action'] );
        $subscription_ids = isset( $_POST['subscription_ids'] ) ? array_map( 'intval', $_POST['subscription_ids'] ) : array();
        
        if ( empty( $subscription_ids ) ) {
            wp_send_json_error( array( 'message' => __( 'No subscriptions selected.', 'wp_subscription' ) ) );
        }
        
        $processed_count = 0;
        $errors = array();
        
        foreach ( $subscription_ids as $subscription_id ) {
            $post = get_post( $subscription_id );
            
            if ( ! $post || $post->post_type !== 'subscrpt_order' ) {
                $errors[] = sprintf( __( 'Subscription #%d not found.', 'wp_subscription' ), $subscription_id );
                continue;
            }
            
            try {
                switch ( $bulk_action ) {
                    case 'trash':
                        if ( wp_trash_post( $subscription_id ) ) {
                            $processed_count++;
                        } else {
                            $errors[] = sprintf( __( 'Failed to move subscription #%d to trash.', 'wp_subscription' ), $subscription_id );
                        }
                        break;
                        
                    case 'restore':
                        if ( wp_untrash_post( $subscription_id ) ) {
                            $processed_count++;
                        } else {
                            $errors[] = sprintf( __( 'Failed to restore subscription #%d.', 'wp_subscription' ), $subscription_id );
                        }
                        break;
                        
                    case 'delete':
                        if ( wp_delete_post( $subscription_id, true ) ) {
                            $processed_count++;
                        } else {
                            $errors[] = sprintf( __( 'Failed to delete subscription #%d.', 'wp_subscription' ), $subscription_id );
                        }
                        break;
                        
                    default:
                        $errors[] = sprintf( __( 'Unknown action: %s', 'wp_subscription' ), $bulk_action );
                        break;
                }
            } catch ( Exception $e ) {
                $errors[] = sprintf( __( 'Error processing subscription #%d: %s', 'wp_subscription' ), $subscription_id, $e->getMessage() );
            }
        }
        
        // Prepare response message
        $message = '';
        if ( $processed_count > 0 ) {
            switch ( $bulk_action ) {
                case 'trash':
                    $message = sprintf( _n( '%d subscription moved to trash.', '%d subscriptions moved to trash.', $processed_count, 'wp_subscription' ), $processed_count );
                    break;
                case 'restore':
                    $message = sprintf( _n( '%d subscription restored.', '%d subscriptions restored.', $processed_count, 'wp_subscription' ), $processed_count );
                    break;
                case 'delete':
                    $message = sprintf( _n( '%d subscription permanently deleted.', '%d subscriptions permanently deleted.', $processed_count, 'wp_subscription' ), $processed_count );
                    break;
            }
        }
        
        if ( ! empty( $errors ) ) {
            $message .= ' ' . __( 'Some errors occurred:', 'wp_subscription' ) . ' ' . implode( ', ', $errors );
        }
        
        if ( $processed_count > 0 ) {
            wp_send_json_success( array( 'message' => $message ) );
        } else {
            wp_send_json_error( array( 'message' => $message ?: __( 'No subscriptions were processed.', 'wp_subscription' ) ) );
        }
    }
}

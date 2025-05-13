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
     * Initialize the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
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
            __( 'Reports', 'wp_subscription' ),
            __( 'Reports', 'wp_subscription' ),
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

        // Add WP Subscription link under WooCommerce menu
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
    private function render_admin_footer() {
        ?>
        <div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
            Made with <span style="color:#e25555;font-size:1.1em;">♥</span> by the WP Subscription Team
            <div style="margin-top:6px;">
                <a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;">Support</a>
                &nbsp;/&nbsp;
                <a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;">Docs</a>
            </div>
        </div>
        <?php
    }
    /**
     * Render the admin header
     */
    private function render_admin_header() {
        // Get current page slug
        $current = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'wp-subscription';
        $menu_items = [
            [
                'slug' => 'wp-subscription',
                'label' => __('Subscriptions', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription'),
            ],
            [
                'slug' => 'wp-subscription-stats',
                'label' => __('Reports', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-stats'),
            ],
            [
                'slug' => 'wp-subscription-settings',
                'label' => __('Settings', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-settings'),
            ],
            [
                'slug' => 'wp-subscription-support',
                'label' => __('Support', 'wp_subscription'),
                'url'  => admin_url('admin.php?page=wp-subscription-support'),
            ],
        ];
        // Allow pro plugin to inject menu items
        $menu_items = apply_filters('wp_subscription_admin_header_menu_items', $menu_items, $current);
        ?>
        <div class="wp-subscription-admin-header">
            <div style="width:1240px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
                <div class="wp-subscription-admin-header-left" style="display:flex;align-items:center;gap:14px;">
                    <img style="height:30px;" src="<?php echo esc_url( WP_SUBSCRIPTION_ASSETS . '/images/logo-title.svg' ); ?>" alt="WP Subscription" class="wp-subscription-logo">
                    <nav class="wp-subscription-admin-header-menu">
                        <?php foreach ($menu_items as $item): ?>
                            <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo ($current === $item['slug']) ? 'current' : ''; ?>">
                                <?php echo esc_html($item['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <div class="wp-subscription-admin-header-right">
                    <?php if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) : ?>
                    <a target="_blank" href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro" class="wp-subscription-upgrade-btn"><?php _e( 'Upgrade to Pro', 'wp_subscription' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
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

        // Handle duplicate action
        if (isset($_GET['action']) && $_GET['action'] === 'duplicate' && !empty($_GET['sub_id'])) {
            $sub_id = intval($_GET['sub_id']);
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
        ?>
        <div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
            Made with <span style="color:#e25555;font-size:1.1em;">♥</span> by the WP Subscription Team
            <div style="margin-top:6px;">
                <a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;">Support</a>
                &nbsp;/&nbsp;
                <a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;">Docs</a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Stats page
     */
    public function render_stats_page() {
        $this->render_admin_header();   
        // if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ){
        //     $this->render_admin_footer();
        //     return;
        // }

        if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) { ?>
            <div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto">
                <div class="wp-subscription-hero-upgrade" style="margin-bottom:18px;">
                    <div class="wp-subscription-hero-content">
                        <span class="wp-subscription-hero-icon">✨</span>
                        <span class="wp-subscription-hero-title">
                            Unlock advanced features, priority support,<br>
                            and more subscription control and reporting.
                        </span>
                    </div>
                    <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro"
                    target="_blank"
                    class="wp-subscription-hero-btn">
                        UPGRADE TO PRO
                    </a>
                </div>
            </div>
        <?php 
            $this->render_admin_footer();
            return;
        }

        global $wpdb;
        $months = [];
        $now = current_time('timestamp');
        for ($i = 11; $i >= 0; $i--) {
            $month = strtotime("-{$i} month", $now);
            $key = date('Y-m', $month);
            $months[$key] = [
                'label' => date('M Y', $month),
                'total' => 0,
                'active' => 0,
                'cancelled' => 0,
                'revenue' => 0.0,
            ];
        }
        $start = strtotime('-11 months', $now);
        $start = strtotime(date('Y-m-01 00:00:00', $start));
        $end = strtotime(date('Y-m-t 23:59:59', $now));
        $subs = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_status, post_date_gmt FROM {$wpdb->posts} WHERE post_type='subscrpt_order' AND post_status != 'trash' AND post_date_gmt >= %s AND post_date_gmt <= %s",
            gmdate('Y-m-d H:i:s', $start),
            gmdate('Y-m-d H:i:s', $end)
        ));
        // dd($subs);
        foreach ($subs as $sub) {
            $month = date('Y-m', strtotime($sub->post_date_gmt));
            if (!isset($months[$month])) continue;
            $months[$month]['total']++;
            if ($sub->post_status === 'active') $months[$month]['active']++;
            if ($sub->post_status === 'cancelled') $months[$month]['cancelled']++;
            // Revenue: get price meta
            $price = get_post_meta($sub->ID, '_subscrpt_price', true);
            $months[$month]['revenue'] += floatval($price);
        }
        // Next month projection: current active * avg revenue per active
        $active_now = $months[array_key_last($months)]['active'];
        $revenue_now = $months[array_key_last($months)]['revenue'];
        $avg_per_active = $active_now ? ($revenue_now / $active_now) : 0;
        $projection = round($active_now * $avg_per_active, 2);
        // Previous month
        $month_keys = array_keys($months);
        $prev_month_key = $month_keys[count($month_keys)-2] ?? null;
        $prev_month_label = $prev_month_key ? $months[$prev_month_key]['label'] : '';
        $prev_month_revenue = $prev_month_key ? $months[$prev_month_key]['revenue'] : 0;
        // Next month name
        $next_month_label = date('M Y', strtotime('+1 month', $now));
        ?>
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto;">
            <div class="wp-subscription-admin-box" style="margin-bottom:32px;">
                <h1 class="wp-subscription-admin-title" style="margin-bottom:18px;">Monthly Subscription Report</h1>
                <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:50px;">
                    <div style="flex:1;min-width:180px;background:#f7fafd;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:2em;font-weight:700;color:#2563eb;"><?php echo intval($months[array_key_last($months)]['total']); ?></div>
                        <div style="color:#888;font-size:13px;">Subscriptions This Month</div>
                    </div>
                    <div style="flex:1;min-width:180px;background:#f7fafd;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:2em;font-weight:700;color:#27c775;"><?php echo intval($months[array_key_last($months)]['active']); ?></div>
                        <div style="color:#888;font-size:13px;">Active Subscriptions</div>
                    </div>
                    <div style="flex:1;min-width:180px;background:#f7fafd;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:2em;font-weight:700;color:#d93025;"><?php echo intval($months[array_key_last($months)]['cancelled']); ?></div>
                        <div style="color:#888;font-size:13px;">Cancelled This Month</div>
                    </div>
                    <div style="flex:1;min-width:180px;background:#f7fafd;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:2em;font-weight:700;color:#7f54b3;">$<?php echo number_format($months[array_key_last($months)]['revenue'], 2); ?></div>
                        <div style="color:#888;font-size:13px;">Revenue This Month</div>
                    </div>
                    <div style="flex:1;min-width:180px;background:#f4f7fa;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:1.5em;font-weight:700;color:#7f54b3;">$<?php echo number_format($prev_month_revenue, 2); ?></div>
                        <div style="font-size:1.1em;font-weight:600;color:#888;margin-bottom:2px;">Previous Month (<?php echo esc_html($prev_month_label); ?>)</div>
                    </div>
                    <div style="flex:1;min-width:180px;background:#e6f0fa;border-radius:8px;padding:18px 22px;text-align:center;">
                        <div style="margin-bottom:10px;font-size:1.5em;font-weight:700;color:#2196f3;">$<?php echo number_format($projection, 2); ?></div>
                        <div style="font-size:1.1em;font-weight:600;color:#888;margin-bottom:2px;">Next Month Projection (<?php echo esc_html($next_month_label); ?>)</div>
                    </div>
                </div>
                <canvas id="wpsubscription-report-chart" height="110"></canvas>
            </div>
        </div>
        
        <?php $this->render_admin_footer(); ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        const ctx = document.getElementById('wpsubscription-report-chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($months, 'label')); ?>,
                datasets: [
                    {
                        label: 'Total',
                        data: <?php echo json_encode(array_column($months, 'total')); ?>,
                        backgroundColor: '#2563eb',
                    },
                    {
                        label: 'Active',
                        data: <?php echo json_encode(array_column($months, 'active')); ?>,
                        backgroundColor: '#27c775',
                    },
                    {
                        label: 'Cancelled',
                        data: <?php echo json_encode(array_column($months, 'cancelled')); ?>,
                        backgroundColor: '#d93025',
                    },
                    {
                        label: 'Revenue',
                        data: <?php echo json_encode(array_map('floatval', array_column($months, 'revenue'))); ?>,
                        backgroundColor: '#7f54b3',
                        type: 'line',
                        yAxisID: 'y1',
                        borderColor: '#7f54b3',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: '#7f54b3',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Count' } },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Revenue ($)' }
                    }
                }
            }
        });
        </script>
        <?php
    }

    /**
     * Render Settings page
     */
    public function render_settings_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto">
            <div class="wp-subscription-admin-box">
                <?php include_once dirname(__FILE__) . '/views/settings.php'; ?>
            </div>
        </div>
        <div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
            Made with <span style="color:#e25555;font-size:1.1em;">♥</span> by the WP Subscription Team
            <div style="margin-top:6px;">
                <a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;">Support</a>
                &nbsp;/&nbsp;
                <a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;">Docs</a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Support page
     */
    public function render_support_page() {
        $this->render_admin_header();
        ?>
        <div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto">
            <?php if ( ! class_exists('Sdevs_Wc_Subscription_Pro') ) : ?>
            <!-- HERO VARIANT 1: Emoji -->
            <div class="wp-subscription-hero-upgrade" style="margin-bottom:18px;">
                <div class="wp-subscription-hero-content">
                    <span class="wp-subscription-hero-icon">✨</span>
                    <span class="wp-subscription-hero-title">
                        Unlock advanced features, priority support,<br>
                        and more subscription control and reporting.
                    </span>
                </div>
                <a href="https://wpsubscription.co/?utm_source=plugin&utm_medium=admin&utm_campaign=upgrade_pro"
                   target="_blank"
                   class="wp-subscription-hero-btn">
                    UPGRADE TO PRO
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Product Overview & Video -->
            <div class="wp-subscription-admin-box" style="margin-bottom:24px;display:flex;gap:32px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:2;min-width:260px;">
                    <h3>Product Overview</h3>
                    <p style="font-size:14px;line-height:1.7;margin:0 0 10px 0;">WP Subscription helps you manage WooCommerce subscriptions with ease. Enjoy a modern admin UI, powerful filters, detailed history, and seamless customer management. Designed for speed, clarity, and growth.</p>
                    <ul style="font-size:14px;line-height:1.6;margin:0 0 0 18px;padding:0;list-style:disc;">
                        <li>Modern, compact admin interface</li>
                        <li>Advanced filtering & search</li>
                        <li>Subscription history & activities</li>
                        <!-- <li>Easy migration from other plugins</li> -->
                        <!-- <li>Pro: Analytics, automation, integrations, and more!</li> -->
                    </ul>
                </div>
                <div style="flex:1;min-width:220px;max-width:320px;">
                    <div style="background:#f4f7fa;border-radius:8px;padding:10px 10px 6px 10px;box-shadow:0 2px 8px #e0e7ef;">
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:6px;">
                            <iframe src="https://www.youtube.com/embed/XXXXXXXX" title="WP Subscription Overview" frameborder="0" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:6px;"></iframe>
                        </div>
                        <div style="text-align:center;font-size:13px;color:#888;margin-top:4px;">Watch: Quick Product Tour</div>
                    </div>
                </div>
            </div>

            <!-- PRO Features List -->
            <div class="wp-subscription-admin-box wp-subscription-pro-features" style="margin-bottom:32px;">
                <div style="font-size:1.3em;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 28 28"><circle cx="14" cy="14" r="14" fill="#2196f3"/><path d="M9 14.5l3 3 7-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>WP Subscription PRO Features</span>
                </div>
                <ul class="wp-subscription-pro-feature-list" style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;">
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">🔀</span>
                        <span><b>Variable Product</b><br><span style="color:#555;font-size:0.98em;">Offer flexible subscription options for variable products.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">🚚</span>
                        <span><b>Delivery Schedule</b><br><span style="color:#555;font-size:0.98em;">Set custom delivery intervals for each subscription.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">📜</span>
                        <span><b>Subscription History</b><br><span style="color:#555;font-size:0.98em;">Track all changes and events for every subscription.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">⏳</span>
                        <span><b>More Subscription Durations</b><br><span style="color:#555;font-size:0.98em;">Offer more flexible and custom subscription periods.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">💶</span>
                        <span><b>Sign Up Fee</b><br><span style="color:#555;font-size:0.98em;">Charge a one-time sign up fee for new subscribers.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">⏩</span>
                        <span><b>Early Renewal</b><br><span style="color:#555;font-size:0.98em;">Allow customers to renew their subscription before expiry.</span></span>
                    </li>
                    <li style="background:#f4f7fa;border-radius:8px;padding:18px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px #e0e7ef;">
                        <span style="font-size:1.5em;color:#2196f3;">💳</span>
                        <span><b>Renewal Price</b><br><span style="color:#555;font-size:0.98em;">Set a different price for subscription renewals.</span></span>
                    </li>
                </ul>
            </div>

            <!-- Support Resources: 2 rows, 2 columns, each in its own box -->
            <div class="wp-subscription-support-resources" style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-bottom:24px;">
                <div class="wp-subscription-admin-box">
                    <h3>Documentation</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Read our <a href="https://converslabs.thrivedeskdocs.com/en\" target=\"_blank\" style=\"color:#2271b1;\">comprehensive docs</a> for setup, migration, and advanced usage.</p>
                    <a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">View Docs</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Facing An Issue?</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">If you have a problem, <a href="https://wpsubscription.co/contact\" target=\"_blank\" style=\"color:#d93025;\">open a support ticket</a> or check our FAQ.</p>
                    <a href="https://wpsubscription.co/contact" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Get Support</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Request a Feature</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Have an idea? <a href="https://wpsubscription.co/contact\" target=\"_blank\" style=\"color:#2271b1;\">Request a feature</a> or vote on others.</p>
                    <a href="https://wpsubscription.co/contact" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Request Feature</a>
                </div>
                <div class="wp-subscription-admin-box">
                    <h3>Show Your Love</h3>
                    <p style="font-size:14px;margin:0 0 8px 0;">Enjoying WP Subscription? <a href="https://wordpress.org/support/plugin/subscription/reviews/\" target=\"_blank\" style=\"color:#f59e42;\">Leave us a review</a> or share your experience!</p>
                    <a href="https://wordpress.org/support/plugin/subscription/reviews/" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">Leave a Review</a>
                </div>
            </div>
        </div>
        <div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
            Made with <span style="color:#e25555;font-size:1.1em;">♥</span> by the WP Subscription Team
            <div style="margin-top:6px;">
                <a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;">Support</a>
                &nbsp;/&nbsp;
                <a href="https://converslabs.thrivedeskdocs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;">Docs</a>
            </div>
        </div>
        <?php
    }

    /**
     * Render the legacy subscriptions list page (WP_List_Table based)
     */
    public function render_legacy_subscriptions_page() {
        // No longer needed, as the menu now links directly to the post type list.
    }
}

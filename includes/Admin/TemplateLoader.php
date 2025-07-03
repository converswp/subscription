<?php

namespace SpringDevs\Subscription\Admin;

/**
 * Template Loader Class
 *
 * @package SpringDevs\Subscription\Admin
 */
class TemplateLoader {

    /**
     * Plugin template directory
     *
     * @var string
     */
    private $template_dir;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->template_dir = WP_SUBSCRIPTION_PATH . '/templates/';
    }

    /**
     * Load admin template
     *
     * @param string $template_name Template name.
     * @param array  $args          Template arguments.
     */
    public function load_admin_template( $template_name, $args = array() ) {
        $template_path = $this->template_dir . 'admin/' . $template_name . '.php';
        
        if ( file_exists( $template_path ) ) {
            // Extract args to make them available in template
            if ( ! empty( $args ) && is_array( $args ) ) {
                extract( $args );
            }
            
            include $template_path;
        } else {
            // Fallback error message
            echo '<div class="notice notice-error"><p>' . 
                 sprintf( __( 'Template file not found: %s', 'wp_subscription' ), esc_html( $template_name ) ) . 
                 '</p></div>';
        }
    }

    /**
     * Get template path
     *
     * @param string $template_name Template name.
     * @return string
     */
    public function get_template_path( $template_name ) {
        return $this->template_dir . 'admin/' . $template_name . '.php';
    }

    /**
     * Check if template exists
     *
     * @param string $template_name Template name.
     * @return bool
     */
    public function template_exists( $template_name ) {
        return file_exists( $this->get_template_path( $template_name ) );
    }
} 
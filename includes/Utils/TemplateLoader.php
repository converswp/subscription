<?php

namespace SpringDevs\Subscription\Utils;

/**
 * Template Loader Utility Class
 *
 * @package SpringDevs\Subscription\Utils
 */
class TemplateLoader {

	/**
	 * Load a template file
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Template arguments.
	 * @param string $template_path Template path (default: 'subscription').
	 * @param string $default_path  Default path (default: WP_SUBSCRIPTION_TEMPLATES).
	 *
	 * @return void
	 */
	public static function load_template( $template_name, $args = array(), $template_path = 'subscription', $default_path = null ) {
		if ( null === $default_path ) {
			$default_path = WP_SUBSCRIPTION_TEMPLATES;
		}

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}

	/**
	 * Get template HTML
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Template arguments.
	 * @param string $template_path Template path (default: 'subscription').
	 * @param string $default_path  Default path (default: WP_SUBSCRIPTION_TEMPLATES).
	 *
	 * @return string
	 */
	public static function get_template_html( $template_name, $args = array(), $template_path = 'subscription', $default_path = null ) {
		if ( null === $default_path ) {
			$default_path = WP_SUBSCRIPTION_TEMPLATES;
		}

		return wc_get_template_html( $template_name, $args, $template_path, $default_path );
	}

	/**
	 * Check if template exists
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path (default: 'subscription').
	 * @param string $default_path  Default path (default: WP_SUBSCRIPTION_TEMPLATES).
	 *
	 * @return bool
	 */
	public static function template_exists( $template_name, $template_path = 'subscription', $default_path = null ) {
		if ( null === $default_path ) {
			$default_path = WP_SUBSCRIPTION_TEMPLATES;
		}

		return wc_get_template_part( $template_name, '', $args, $template_path, $default_path ) !== false;
	}
} 
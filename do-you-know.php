<?php
/**
 * Plugin Name: Do You Know
 * Plugin URI: http://lab.konnektiv.de/giz/wp-plugins/do-you-know-widget
 * Description: Provides the do you know widget.
 * Version: 0.0.1
 * Author: Konnektiv
 * Author URI: http://konnektiv.de/
 * License: GPLv2 (license.txt)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DoYouKnowPlugin {

	/**
	 * @var DoYouKnowPlugin
	 */
	private static $instance;

	/**
	 * Main DoYouKnowPlugin Instance
	 *
	 * Insures that only one instance of DoYouKnowPlugin exists in memory at
	 * any one time. Also prevents needing to define globals all over the place.
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 *
	 * @staticvar array $instance
	 *
	 * @return DoYouKnowPlugin
	 */
	public static function instance( ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new DoYouKnowPlugin;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_filters();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent loading more than one instance
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 */
	private function __construct() { /* Do nothing here */
	}


	/**
	 * Component global variables
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 * @access private
	 *
	 */
	private function setup_globals() {

	}

	/**
	 * Includes
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 * @access private
	 */
	private function includes() {
		require_once( 'do-you-know-widget-class.php' );
		require_once( 'bp-member-with-avatar.php' );
	}

	/**
	 * Setup the filters
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 * @access private
	 *
	 * @uses remove_filter() To remove various filters
	 * @uses add_filter() To add various filters
	 */
	private function setup_filters() {

	}

	/**
	 * Setup the actions
	 *
	 * @since DoYouKnowPlugin (0.0.1)
	 * @access private
	 *
	 * @uses remove_action() To remove various actions
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {
		add_action('widgets_init', create_function('', 'return register_widget("DoYouKnow_widget");'));
	}
}

DoYouKnowPlugin::instance();
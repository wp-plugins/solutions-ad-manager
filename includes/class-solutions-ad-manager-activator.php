<?php

/**
 * Fired during plugin activation
 *
 * @link       http://solutionsbysteve.com
 * @since      0.1.0
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/includes
 * @author     Steven Maloney <steve@solutionsbysteve.com>
 */
class Solutions_Ad_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {
		
		if ( ! wp_next_scheduled( 'solutions_scheduled_update' ) ) {
			wp_schedule_event( time(), 'hourly', 'solutions_scheduled_update');
		}
		
	}

}

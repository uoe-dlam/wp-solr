<?php

/**
 * Provides integration for Apache Solr with WordPress.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://blogs.ed.ac.uk
 * @since             1.0.0
 * @package           EdSolr
 *
 * @wordpress-plugin
 * Plugin Name:       EdSolr
 * Plugin URI:        https://gitlab.is.ed.ac.uk/is-dlam/academic-blogging-project/ed-solr
 * Description:       Integrates Apache Solr with a WordPress Multisite Installation.
 * Version:           1.0.0
 * Author:            DLAM Applications Development Team, University of Edinburgh
 * Author URI:        http://www.ed.ac.uk/
 * License:           MIT
 * License URI:       http://mit-license.org/
 * Text Domain:       EdSolr
 * Domain Path:       /languages
 */

require __DIR__ . '/vendor/autoload.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ED_SOLR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ed-solr-activator.php
 */
function activate_ed_solr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ed-solr-activator.php';
	Ed_Solr_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ed-solr-deactivator.php
 */
function deactivate_ed_solr() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ed-solr-deactivator.php';
	Ed_Solr_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ed_solr' );
register_deactivation_hook( __FILE__, 'deactivate_ed_solr' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ed-solr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ed_solr() {

	$plugin = new Ed_Solr();
	$plugin->run();

}

run_ed_solr();


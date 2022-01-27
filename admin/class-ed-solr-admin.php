<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/admin
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ed_solr    The ID of this plugin.
	 */
	private $ed_solr;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $ed_solr    The name of this plugin.
	 * @param    string $version    The version of this plugin.
	 */
	public function __construct( $ed_solr, $version ) {
		$this->ed_solr = $ed_solr;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ed_Solr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ed_Solr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->ed_solr, plugin_dir_url( __FILE__ ) . 'css/ed-solr-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ed_Solr_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ed_Solr_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->ed_solr, plugin_dir_url( __FILE__ ) . 'js/ed-solr-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add the admin menu for EdSolr.
	 *
	 * @since   1.0.0
	 */
	public function add_main_menu() {
		add_menu_page(
			'Solr Search',
			'Solr Search',
			'manage_options',
			'solr-search',
			array( $this, 'display_admin_page' ),
			'dashicons-search',
			150
		);

		add_submenu_page(
			'solr-search',
			'Solr Server Config',
			'Solr Server Config',
			'manage_options',
			'solr-search-config',
			array( $this, 'display_config_page' )
		);
	}

	/**
	 * Index all blogs in the external Solr server and redirect for status message.
	 *
	 * @since   1.0.0
	 */
	public function index_blogs() {

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'admin_response' => 'The indexing process has been initiated. You should get an email once it is done.',
					),
					network_admin_url( 'admin.php?page=solr-search' )
				)
			)
		);

		exit;
	}

	/**
	 * Register the default Solr server settings with WordPress.
	 *
	 * @since   1.0.0
	 */
	public function register_solr_settings() {
		add_site_option( 'solr-host', '' );
		add_site_option( 'solr-port', '' );
		add_site_option( 'solr-path', '' );
		add_site_option( 'solr-core', '' );
		add_site_option( 'solr-email', '' );
		add_site_option( 'solr-username', '' );
		add_site_option( 'solr-password', '' );
	}

	/**
	 * Display the main EdSolr admin page.
	 *
	 * @since   1.0.0
	 */
	public function display_admin_page() {
		include_once 'partials/ed-solr-admin-display.php';
	}

	/**
	 * Display the EdSolr config page.
	 *
	 * @since   1.0.0
	 */
	public function display_config_page() {
		include_once 'partials/ed-solr-config-display.php';
	}

	/**
	 * Update the external Solr server config settings.
	 *
	 * @since   1.0.0
	 */
	public function update_solr_settings() {
		if ( isset( $_POST['solr_settings_nonce'] ) && wp_verify_nonce( $_POST['solr_settings_nonce'], 'solr_settings_nonce' ) ) {
			// update the solr settings
			update_site_option( 'solr-host', sanitize_text_field( $_POST['solr-host'] ) );
			update_site_option( 'solr-port', sanitize_text_field( $_POST['solr-port'] ) );
			update_site_option( 'solr-path', sanitize_text_field( $_POST['solr-path'] ) );
			update_site_option( 'solr-core', sanitize_text_field( $_POST['solr-core'] ) );
			update_site_option( 'solr-email', sanitize_email( $_POST['solr-email'] ) );
			update_site_option( 'solr-username', sanitize_text_field( $_POST['solr-username'] ) );
			update_site_option( 'solr-password', sanitize_text_field( $_POST['solr-password'] ) );

			wp_redirect(
				esc_url_raw(
					add_query_arg(
						array(
							'admin_response' => 'Solr settings updated.',
						),
						network_admin_url( 'admin.php?page=solr-search-config' )
					)
				)
			);

			exit;
		}

		wp_die(
			__( 'Invalid nonce specified', $this->ed_solr ),
			__( 'Error', $this->ed_solr ),
			array(
				'response'  => 403,
				'back_link' => 'admin.php?page=' . $this->ed_solr,
			)
		);
	}

	/**
	 * Handles the printing of notifications on the admin pages.
	 *
	 * @since   1.0.0
	 */
	public function print_plugin_admin_notices() {
		if ( isset( $_REQUEST['admin_response'] ) ) {
			$html  = '<div class="notice notice-success is-dismissible"><p><strong>The request was successful.</strong></p><br>';
			$html .= $_REQUEST['admin_response'];
			$html .= '</div>';

			echo $html;
		} elseif ( isset( $_REQUEST['admin_error'] ) ) {
			$html  = '<div class="notice notice-error is-dismissible"><p><strong>The request was not successful.</strong></p><br>';
			$html .= $_REQUEST['admin_error'];
			$html .= '</div>';

			echo $html;
		} else {
			return;
		}
	}
}

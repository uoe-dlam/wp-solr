<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/public
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Public {
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
	 * @param      string    $ed_solr       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $ed_solr, $version ) {
		$this->ed_solr = $ed_solr;
		$this->version = $version;
	}
	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( $this->ed_solr, plugin_dir_url( __FILE__ ) . 'css/ed-solr-public.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		wp_enqueue_script( $this->ed_solr, plugin_dir_url( __FILE__ ) . 'js/ed-solr-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Store or update a post in Solr.
	 *
	 * This is called whenever a post or page is updated/created
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function store_post( $post_id ) {
	    $solr_client = $this->get_solr_client();

		$update = $solr_client->createUpdate();

		$blog_id = get_current_blog_id();
		$post    = get_post( $post_id );

		$document = $update->createDocument();

		$document->id          = $blog_id . '_' . $post->ID;
		$document->blogId      = $blog_id;
		$document->postId      = $post->ID;
		$document->postAuthor  = $post->post_author;
		$document->postDate    = $post->post_date;
		$document->postTitle   = $post->post_title;
		$document->postContent = $post->post_content;
		$document->postExcerpt = $post->post_excerpt;

		$update->addDocument( $document );
		$update->addCommit();

		$result = $solr_client->update( $update );

		if ( $result->getStatus() !== 0 ) {
			wp_mail( get_site_option( 'solr-email' ), 'Solr Index Error', "Error saving post ID: $post->ID" );
		}
	}

	public function delete_post( $post_id ) {
	    $solr_client = $this->get_solr_client();

	    $update = $solrClient->createUpdate();

	    $update->addDeleteById( get_current_blog_id() . '_' . $post_id );
	    $update->addCommit();

	    $solr_client->update($update);
    }

    private function get_solr_client() {
	    return new Solarium\Client(
			[
				'endpoint' => [
					'localhost' => [
						'host' => get_site_option( 'solr-host' ),
						'port' => get_site_option( 'solr-port' ),
						'path' => get_site_option( 'solr-path' ),
						'core' => get_site_option( 'solr-core' ),
					],
				],
			]
		);
    }
}

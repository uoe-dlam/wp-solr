<?php
/**
 * Searches solr for entries.
 *
 * @since      1.0.0
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Search {

	public $posts      = [];
	private $blog_ids  = [];
	private $keywords  = '';
	private $show_ease = false;
	private $solr_client;
	private $current_page   = 1;
	private $posts_per_page = 10;
	private $total_pages    = 0;

	public function __construct( array $args ) {
		foreach ( $args as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->{$property} = $value;
			}
		}

		$this->solr_client = $this->get_solr_client();
		$this->calculate_total_pages();
		$this->do_search();

	}

	/**
	 * Get solarium client
	 *
	 * @return Solarium\Client
	 */
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

	/**
	 * Get total no pages associated with search
	 *
	 * @return int
	 */
	private function calculate_total_pages() {
		if ( '' === $this->keywords || empty( $this->blog_ids ) ) {
			return;
		}

		$query = $this->solr_client->createSelect();
		$query->setQuery( $this->get_query_string() );
		$query->setRows( 0 );
		$result_set        = $this->solr_client->select( $query );
		$this->total_pages = ceil( $result_set->getNumFound() / $this->posts_per_page );
	}

	/**
	 * Load matching posts from DB.
	 *
	 * @return void
	 */
	private function do_search() {
		if ( '' === $this->keywords || empty( $this->blog_ids ) ) {
			return;
		}

		$query = $this->solr_client->createSelect();
		$query->setQuery( $this->get_query_string() );
		$query->setStart( $this->get_start_record() );
		$query->setRows( $this->posts_per_page );
		$result_set = $this->solr_client->select( $query );

		foreach ( $result_set as $document ) {
			$mapper        = new Ed_Solr_Post_Mapper( $document );
			$this->posts[] = $mapper->get_post_from_document();
		}

	}

	/**
	 * Build up solr search query from search values
	 *
	 * @return string
	 */
	private function get_query_string() {
        $query_string  = 'blogId:(' . implode( ' OR ', $this->blog_ids ) . ')';
        $query_string .= ' AND postTitle:"' . $this->keywords . '"~10000';
        $query_string .= ' OR postContent:"' . $this->keywords . '"~10000';

		if ( ! $this->show_ease ) {
			$query_string .= ' AND easeOnly:0';
		}

		return $query_string;
	}

	/**
	 * Get start record depending on page
	 *
	 * @return int
	 */
	private function get_start_record() {
		return ( $this->current_page - 1 ) * $this->posts_per_page;
	}

	/**
	 * Get current page
	 *
	 * @return int
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Get total pages for search
	 *
	 * @return int
	 */
	public function get_total_pages() {
		return $this->total_pages;
	}



}


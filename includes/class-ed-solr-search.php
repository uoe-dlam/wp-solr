<?php

use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Searches solr for entries.
 *
 * @since      1.0.0
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Search {

	public $posts = [];
	private $blog_ids = [];
	private $keywords = '';
	private $show_sso = false;
	private $solr_client;
	private $current_page = 1;
	private $posts_per_page = 10;
	private $total_pages = 0;

	public function __construct( array $args ) {
		foreach ( $args as $property => $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->{$property} = $value;
			}
		}

		$this->solr_client = $this->get_solr_client();
		$this->do_search();
	}

	/**
	 * Get solarium client
	 *
	 * @return Solarium\Client
	 */
	private function get_solr_client() {
		$client = new Solarium\Client(
			new Curl(),
			new EventDispatcher(),
			[
				'endpoint' => [
					'localhost' => [
						'host'     => get_site_option( 'solr-host' ),
						'port'     => get_site_option( 'solr-port' ),
						'path'     => get_site_option( 'solr-path' ),
						'core'     => get_site_option( 'solr-core' ),
						'username' => get_site_option( 'solr-username' ),
						'password' => get_site_option( 'solr-password' ),
					],
				],
			]
		);

		$client->getPlugin( 'postbigrequest' );

		return $client;
	}

	/**
	 * Load matching posts from Solr and record the total page count.
	 *
	 * @return void
	 */
	private function do_search() {
		if ( '' === $this->keywords || empty( $this->blog_ids ) ) {
			return;
		}

		$query = $this->solr_client->createSelect();

		$dismax = $query->getEDisMax();
		$dismax->setQueryFields( 'postTitle^2 postContent' );
		$query->setQuery( $this->keywords );

		// Non-scoring filters, cached and reused across every search.
		$this->add_filter_queries( $query );

		$query->setStart( $this->get_start_record() );
		$query->setRows( $this->posts_per_page );

		$result_set = $this->solr_client->select( $query );

		$this->total_pages = (int) ceil(
			$result_set->getNumFound() / $this->posts_per_page
		);

		foreach ( $result_set as $document ) {
			$mapper        = new Ed_Solr_Post_Mapper( $document );
			$this->posts[] = $mapper->get_post_from_document();
		}
	}

	/**
	 * Attach the blog and visibility restrictions as cached filter queries.
	 *
	 * blogId uses the terms query parser so the whole list counts as a
	 * single clause, regardless of how many blogs are in the network.
	 *
	 * @param Solarium\QueryType\Select\Query\Query $query Query to modify.
	 *
	 * @return void
	 */
	private function add_filter_queries( $query ) {
		$blog_ids = array_filter( array_map( 'intval', $this->blog_ids ) );

		if ( ! empty( $blog_ids ) ) {
			$query->createFilterQuery( 'blogs' )
				->setQuery( '{!terms f=blogId}' . implode( ',', $blog_ids ) )
				->setCache( true );
		}

		if ( ! $this->show_sso ) {
			$query->createFilterQuery( 'ease' )
				->setQuery( 'easeOnly:0' )
				->setCache( true );
		}
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


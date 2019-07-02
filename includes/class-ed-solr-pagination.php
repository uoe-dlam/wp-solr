<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 */

/**
 * Utility functions.
 *
 * @since      1.0.0
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Pagination {

	/**
	 * Used for search pagination.
	 *
	 * @param Ed_Solr_Search $search a multisite search query object.
	 *
	 * @return string
	 */
	public static function paginate( $search ) {
		$args = array(
			'total'     => $search->get_total_pages(), // total amount of pages.
			'current'   => $search->get_current_page(), // current page number.
			'show_all'  => false, // set to true if you want to show all pages at once.
			'mid_size'  => 2, // how much page numbers to show on the each side of the current page.
			'end_size'  => 2, // how much page numbers to show at the beginning and at the end of the list.
			'prev_next' => true, // if you set this to false, the previous and the next post links will be removed.
			'prev_text' => '&laquo;', // «.
			'next_text' => '&raquo;', // ».
			'format'    => '',
			'base'      => @add_query_arg( 'page', '%#%' ),
		);

		if ( $args['total'] <= 1 ) { // do not return anything if there are not enough posts.
			return '';
		}

		return '<div class="navigation">
		<span class="pages">Page ' . $args['current'] . ' of ' . $args['total'] . '</span>&nbsp;&nbsp; - '
			. paginate_links( $args ) .
			'</div>';
	}




}


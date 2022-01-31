<?php

use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class Ed_Solr_Index_Blogs_CLI extends WP_CLI_Command {

		public const NO_OF_BLOGS_PER_BATCH = 100;
		public const ALL_POSTS = - 1;
		public const MAX_NUMBER_OF_BLOGS = '200000';

		/**
		 * Index all blogs in a WordPress instance into Apache Solr through WP CLI command.
		 *
		 * @return void
		 */
		public function index() {
			$solr_client = new Solarium\Client(
				new Curl(),
				new EventDispatcher(),
				array(
					'endpoint' => array(
						'localhost' => array(
							'host'     => get_site_option( 'solr-host' ),
							'port'     => get_site_option( 'solr-port' ),
							'path'     => get_site_option( 'solr-path' ),
							'core'     => get_site_option( 'solr-core' ),
							'username' => get_site_option( 'solr-username' ),
							'password' => get_site_option( 'solr-password' ),
						),
					),
				)
			);

			$buffer = $solr_client->getPlugin( 'bufferedadd' );
			$buffer->setBufferSize( self::NO_OF_BLOGS_PER_BATCH );

			$blogs = get_sites( 'number', self::MAX_NUMBER_OF_BLOGS );

			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog->blog_id );

				$posts = get_posts( self::ALL_POSTS );

				foreach ( $posts as $post ) {
					if ( 'publish' === $post->post_status ) {
						$buffer->createDocument( Ed_Solr_Post_Mapper::get_data_from_post( $post, $blog->blog_id ) );
					}
				}
			}

			$result = $buffer->commit( true, true, true, true );
			$status = $result->getStatus();

			$to      = get_site_option( 'solr-email' );
			$subject = 'ED BLOGS: background indexing process ended.';
			$message = '<p>The background indexing process has finished.</p><p>Status message: ' . ( $status === 0 ? 'All good' : 'Something was not right  (' . $status . ')' ) . '<p><p>Thank you.</p>';
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			if ( ! wp_mail( $to, $subject, $message, $headers ) ) {
				error_log( 'Background process indexing end - email NOT sent - status code: ' . $status );
			}

			exit;
		}
	}

	// Add command to the new class
	WP_CLI::add_command( 'solr', 'ED_Solr_Index_Blogs_CLI' );

}

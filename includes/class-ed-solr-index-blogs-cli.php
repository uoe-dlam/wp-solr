<?php

use Solarium\Plugin\BufferedAdd\Event\Events;
use Solarium\Plugin\BufferedAdd\Event\PreFlush as PreFlushEvent;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class Ed_Solr_Index_Blogs_CLI extends WP_CLI_Command {

    	/**
		 * Index all blogs in a WordPress instance into Apache Solr through WP CLI command.
		 *
		 * @return void
		 */
        public function index() {

			$solr_client = new Solarium\Client(
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

            $buffer = $solr_client->getPlugin('bufferedadd');
            $buffer->setBufferSize(10);

            // also register an event hook to display what is happening
            $solr_client->getEventDispatcher()->addListener(
                Events::PRE_FLUSH,
                function (PreFlushEvent $event) {
                    echo 'Flushing buffer (' . count($event->getBuffer()) . 'docs)<br/>';
                }
            );

            $solr_client = $solr_client->getPlugin('bufferedadd');

			$update = $solr_client->createUpdate();

			$blogs = get_sites( 'number', '200000' );

			$documents = array();

			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog->blog_id );

				$posts = get_posts( -1 );

				foreach ( $posts as $post ) {
					if ( 'publish' === $post->post_status ) {
						$mapper = new Ed_Solr_Post_Mapper( $update->createDocument() );
                        $buffer->addDocument( $mapper->get_document_from_post( $post, $blog->blog_id ) );
						$documents[] = $mapper->get_document_from_post( $post, $blog->blog_id );
					}
				}
			}

            $buffer->flush();

			$update->addDocuments( $documents );
			$update->addCommit();

			$result = $solr_client->update( $update );

			$status = $result->getStatus();

			$to = get_site_option( 'solr-email' );
			$subject = 'ED BLOGS: background indexing process ended.';
			$message = '<p>The background indexing process has finished.</p><p>Status message: ' . ($status === 0 ? 'All good (0)' : 'Something was not right  (' . $status . ')' ) . '<p><p>Thank you.</p>';
			$headers = array('Content-Type: text/html; charset=UTF-8');

			if ( wp_mail( $to, $subject, $message, $headers ) ) {
				error_log('Background process indexing end - email sent.' );
			}
			else{
				error_log('Background process indexing end - email NOT sent' );
			}

			exit;
	  }
    }
    // Add command to the new class
    WP_CLI::add_command( 'solr', 'ED_Solr_Index_Blogs_CLI' );

} 

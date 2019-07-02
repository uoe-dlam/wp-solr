<?php
/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 */
/**
 * Maps posts to documents.
 *
 * @since      1.0.0
 * @package    Ed_Solr
 * @subpackage Ed_Solr/includes
 * @author     DLAM Applications Development Team <ltw-apps@ed.ac.uk>
 */
class Ed_Solr_Post_Mapper {

	private $document;

	public function __construct( $document ) {
		$this->document = $document;
	}

	/**
	 * Build solr doc for post
	 *
	 * @param WP_Post $post
	 * @param int     $blog_id
	 * @return Solarium\QueryType\Update\Query\Document $document
	 */
	public function get_document_from_post( $post, $blog_id ) {
		$this->document->id          = $blog_id . '_' . $post->ID;
		$this->document->blogId      = $blog_id;
		$this->document->postId      = $post->ID;
		$this->document->postAuthor  = $post->post_author;
		$this->document->postDate    = $post->post_date;
		$this->document->postTitle   = $post->post_title;
		$this->document->postContent = wp_strip_all_tags( $post->post_content );
		$this->document->postExcerpt = $post->post_excerpt;

		// Grab ease_only value from post if this is a form update since meta values only get set after publish_post/publish_page post is run; i.e. we can't just grab the post_meta value from the db.
		if ( isset( $_POST['action'] ) && 'editpost' === $_POST['action'] ) {
			$this->document->easeOnly = (int) $_POST['chk_ease_only'] ?? 0;
		} else {
			$this->document->easeOnly = (int) get_post_meta( $post->ID, 'ease_only', true );
		}

		return $this->document;
	}

	/**
	 * Build post from solr doc
	 *
	 * @return stdClass
	 */
	public function get_post_from_document() {
		$post               = new stdClass();
		$post->BLOG_ID      = $this->document['blogId'][0];
		$post->ID           = $this->document['postId'][0];
		$post->post_title   = $this->document['postTitle'][0];
		$post->post_content = $this->document['postContent'][0];
		$post->post_date    = $this->document['postDate'][0];
		return $post;
	}
}

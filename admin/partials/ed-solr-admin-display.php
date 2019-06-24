<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ed_Solr
 * @subpackage Ed_Solr/admin/partials
 */
?>

<div class="wrap">
	<h1>Index Blogs</h1>

	<p>
		This function is used to index all of the blogs in this multisite instance. In most instances, it would only be
		used if this plugin has been newly installed on an existing multisite installation that has pre-existing blog
		content.
	</p>
	<p>
		Alternatively, you should use this function if you believe updates to a blog have not been indexed correctly in
		Apache Solr.
	</p>

	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="index_blogs">
		<?php echo submit_button( 'Index Blogs', 'primary', 'index_blogs' ); ?>
	</form>
</div>

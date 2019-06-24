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
	<h1>Solr Server Configuration</h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="solr-host">Host</label>
					</th>
					<td>
						<input id="solr-host" name="solr-host" value="<?php echo get_site_option( 'solr-host' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="solr-port">Port:</label>
					</th>
					<td>
						<input id="solr-port" name="solr-port" value="<?php echo get_site_option( 'solr-port' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="solr-path">Path:</label>
					</th>
					<td>
						<input id="solr-path" name="solr-path" value="<?php echo get_site_option( 'solr-path' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="solr-core">Core:</label>
					</th>
					<td>
						<input id="solr-core" name="solr-core" value="<?php echo get_site_option( 'solr-core' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="solr-email">Error Report Email:</label>
					</th>
					<td>
						<input id="solr-email" name="solr-email" value="<?php echo get_site_option( 'solr-email' ); ?>" required>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="solr_settings">
		<?php
		wp_nonce_field( 'solr_settings_nonce', 'solr_settings_nonce' );
		submit_button();
		?>
	</form>
</div>


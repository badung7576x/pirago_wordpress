<?php
/**
 * Astra Demo View.
 *
 * @package Astra Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Ok.
}
?>

<div class="wrap">

	<form id="astra-portfolio-settings" enctype="multipart/form-data" method="post">

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show Portfolio On', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<select name="show-portfolio-on">
								<option value="scroll" <?php selected( $data['show-portfolio-on'], 'scroll' ); ?> /><?php esc_html_e( 'Scroll', 'astra-portfolio' ); ?></option>
								<option value="click" <?php selected( $data['show-portfolio-on'], 'click' ); ?> /><?php esc_html_e( 'Click', 'astra-portfolio' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Select the action to display more portfolio items.', 'astra-portfolio' ); ?></p>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Thumbnail Hover Style', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<select name="grid-style">
								<option value="default" <?php selected( $data['grid-style'], 'default' ); ?> /><?php esc_html_e( 'Default', 'astra-portfolio' ); ?></option>
								<option value="style-1" <?php selected( $data['grid-style'], 'style-1' ); ?> /><?php esc_html_e( 'Image Scroll', 'astra-portfolio' ); ?></option>
							</select>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Preview Bar', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<select name="preview-bar-loc">
								<option value="top" <?php selected( $data['preview-bar-loc'], 'top' ); ?> /><?php esc_html_e( 'Top', 'astra-portfolio' ); ?></option>
								<option value="bottom" <?php selected( $data['preview-bar-loc'], 'bottom' ); ?> /><?php esc_html_e( 'Bottom', 'astra-portfolio' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Set portfolio preview bar location.', 'astra-portfolio' ); ?></p>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Call to Action', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<textarea rows="8" cols="80" class="large-text code" name="no-more-sites-message"><?php echo wp_kses_post( $data['no-more-sites-message'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'CTA will display at the end of the portfolio list. Shortcode / HTML allowed.', 'astra-portfolio' ); ?></p>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Layout', 'astra-portfolio' ); ?></h2>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Masonry', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="checkbox" name="enable-masonry" value="1" <?php checked( $data['enable-masonry'], 1 ); ?> /> <?php esc_html_e( 'Enable Masonry Layout.', 'astra-portfolio' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Columns', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="number" name="no-of-columns" min="1" max="4" value="<?php echo esc_attr( $data['no-of-columns'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Number of items per row. Supports maximum 4 items.', 'astra-portfolio' ); ?></p>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Items Per Page', 'astra-portfolio' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="number" name="par-page" min="1" max="100" value="<?php echo esc_attr( $data['par-page'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Select the number of items that should load per request.', 'astra-portfolio' ); ?></p>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<input type="hidden" name="message" value="saved" />
		<input type="hidden" name="tab_slug" value="style" />
		<?php wp_nonce_field( 'astra-portfolio-importing', 'astra-portfolio-import' ); ?>

		<?php submit_button(); ?>
	</form>
</div>

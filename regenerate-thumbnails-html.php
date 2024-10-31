<?php
/*
Plugin Name: Regenerate Thumbnails HTML
Plugin URI: http://meowapps.com
Description: This plugin updates the HTML for your images. Useful when switching between image sizes and themes.
Version: 0.0.2
Author: TigrouMeow
License: GPLv2 or later
*/

// Check admin area
if ( !is_admin() )
	return;

include "common/meow_admin.php";

class Meow_Regenerate_Thumbnails_HTML extends Meow_Admin {

	public function __construct() {
		parent::__construct( 'mrth', 'regenerate-thumbnails-html' );
		add_action('admin_menu', array( &$this, 'admin_menu' ) );
	}

	public function admin_menu() {
		add_submenu_page( 'meowapps-main-menu', 'Regenerate Thumbnails HTML', 'Regenerate Thumbnails HTML', 'manage_options',
			'regenerate-thumbnails-html', array( &$this, 'the_ui' ) );
	}

	function common_url( $file ) {
		return plugin_dir_url( __FILE__ ) . ( '\/common\/' . $file );
	}

	function get_image_sizes() {
		$sizes = array();
		global $_wp_additional_image_sizes;
		foreach ( get_intermediate_image_sizes() as $s ) {
			$crop = false;
			if ( isset( $_wp_additional_image_sizes[$s] ) ) {
				$width = intval($_wp_additional_image_sizes[$s]['width']);
				$height = intval($_wp_additional_image_sizes[$s]['height']);
				$crop = $_wp_additional_image_sizes[$s]['crop'];
			} else {
				$width = get_option( $s . '_size_w' );
				$height = get_option( $s . '_size_h' );
				$crop = get_option( $s . '_crop' );
			}
			$sizes[$s] = array( 'width' => $width, 'height' => $height, 'crop' => $crop );
		}

		return $sizes;
	}

	public function the_ui() {

		// Track errors
		$form_errors = array();

		$image_sizes = $this->get_image_sizes();
		$posttypes = get_post_types( '', 'names' );
		$posttypes = array_diff( $posttypes, array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ) );
		array_unshift( $posttypes, "" );

		// Check step
		$step = 0;
		if ( isset( $_POST['hd-submit'] ) ) {
			$step = intval( $_POST['hd-submit'] );
			if ($step < 0 || $step > 2)
				$step = 0;
		}

		// Validate form
		if ( $step > 0 ) {

			// Check period width
			$width_usr = $_POST['tx-width'];
			$widths = explode('-', $width_usr);
			if ( !( count( $widths ) == 1 || count( $widths ) == 2 ) ) {
				$width = '';
				$step = 0;
				$form_errors['width'] = __( 'Incorrect Width format', 'regenerate-thumbnails-html' );

			// Period or single
			} else {

				// Check width
				$width = intval( trim( $widths[0] ) );
				if (!$width > 0) {
					$width = '';
					$step = 0;
					$form_errors['width'] = __('Incorrect Width value', 'regenerate-thumbnails-html');
				}

				// Period widths
				if ( count( $widths ) == 2 ) {
					$width2 = intval(trim($widths[1]));
					if (!$width2 > 0 || !($width2 > $width)) {
						$width = '';
						$step = 0;
						$form_errors['width'] = __('Incorrect second Width value', 'regenerate-thumbnails-html');
					} elseif ($width2 - $width > 100) {
						$width2 = $width + 100;
					}
				}
			}

			// Check size
			$size = trim($_POST['tx-size']);
			if (empty($size)) {
				$step = 0;
				$form_errors['size'] = __('Please, enter a format size', 'regenerate-thumbnails-html');
			}
		}

		// Check default post type
		$post_type = isset( $_POST['tx-post-type'] ) ? trim( $_POST['tx-post-type'] ) : 'post';
		$image_size = isset( $size ) ? esc_attr( $size ) : 'large';
		?>

		<div class="wrap">

			<?php echo $this->display_title( "Regenerate Thumbnails HTML" );  ?>

			<div class="meow-row">
				<div class="meow-box meow-col meow-span_2_of_2">
					<h3><?php _e( 'Settings', 'regenerate-thumbnails-html' ); ?></h3>

					<div class="inside">

						<p><?php _e( 'How does this work? The first important field is the <b>Width in HTML</b>. You can find this the posts in your HTML, it is basically the width used in the filename of your photos (for example: mount-daigahara-<b>1024</b>x683.jpg) or the value associated with the width of your img tag (for example: width="<b>840</b>"). Pick the target Image Size you need, and in which kind of post (probably <i>post</i>). <b>Analyze</b> will show you the changes before applying them.', 'regenerate-thumbnails-html' ); ?></p>

						<form method="post">
							<input type="hidden" name="hd-submit" value="1" />
							<div class="meow-row">
								<div class="meow-col meow-span_1_of_3">
									<label for="tx-width"><?php _e('Width in HTML', 'regenerate-thumbnails-html'); ?></label><br />
									<input type="text" name="tx-width" id="tx-width" value="<?php echo isset( $width_usr ) ? esc_attr( $width_usr ) : '300'; ?>" class="regular-text" />
								</div>
								<div class="meow-col meow-span_1_of_3">
									<label for="tx-size"><?php _e('New Image Size', 'regenerate-thumbnails-html'); ?></label><br />
									<select id="tx-size" name="tx-size" style="width: 100%;">
									<?php
								    foreach ( $image_sizes as $arg => $value ) {
								      echo '<option value="' . $arg . '"' . selected( $arg, $image_size, false ) . ' > '  .
								        ( empty( $arg ) ? 'none' : $arg ) . '</option><br />';
										}
									?>
									</select>
								</div>
								<div class="meow-col meow-span_1_of_3">
									<label for="tx-post-type"><?php _e( 'Post type', 'regenerate-thumbnails-html' ); ?></label><br />
									<select id="tx-post-type" name="tx-post-type" style="width: 100%;">
									<?php
								    foreach ( $posttypes as $arg => $value )
								      echo '<option value="' . $arg . '"' . selected( $arg, $post_type, false ) . ' > '  .
								        ( empty( $arg ) ? 'none' : $arg ) . '</option><br />';
									?>
									</select>
								</div>
							</div>
							<?php submit_button( "Analyze" ); ?>
						</form>

					</div>
				</div>
			</div>

				<?php if ( $step == 1 ) : ?>
					<!-- ANALYSIS RESULTS -->
					<form method="post">
					<div class="meow-row">
						<div class="meow-box meow-col meow-span_2_of_2">
							<h3><?php _e( 'To Update', 'regenerate-thumbnails-html' ); ?></h3>
							<input type="hidden" name="tx-width" id="tx-width" value="<?php echo esc_attr( $width_usr ); ?>" />
							<input type="hidden" name="tx-size" id="tx-size" value="<?php echo esc_attr( $image_size ); ?>" />
							<input type="hidden" name="tx-post-type" id="tx-post-type" value="<?php echo esc_attr( $post_type ); ?>" />
							<input type="hidden" name="hd-submit" value="2" />

				<?php elseif ( $step == 2 ) : ?>
					<!-- UPDATE RESULTS -->
					<form method="post">
						<div class="meow-box meow-col meow-span_2_of_2">
							<h3><?php _e( 'Result', 'regenerate-thumbnails-html' ); ?></h3>

				<?php endif; ?>

				<!-- ACTION! -->
				<div style="margin: 5px;">
				<?php

					if ( $step > 0 ) :
						global $wpdb;
						$mod_sum = false;
						if ( !isset( $width2 ) )
							$width2 = $width;
						$ids = array();
						$posts = array();

						for ( $w = $width; $w <= $width2; $w++ ) {
							$entries = $wpdb->get_results( '
								SELECT ID, post_title, post_content
								FROM ' . $wpdb->posts . '
								WHERE post_content LIKE "%<img %" AND (post_content LIKE "%-' . esc_sql( $w ) . 'x%"
								OR post_content LIKE '."'" . '%width="' . $w . '"%' . "'" . ')
									AND post_type = "' . esc_sql( $post_type ) . '"
									AND post_status IN ( "publish", "future" )' . ( ( count( $ids ) > 0 ) ? '
									AND ID NOT IN (' . implode( ',', $ids ) . ')' : '' ) . '
									ORDER BY ID DESC'
							);
							if ( $entries && count( $entries ) > 0 ) {
								foreach ( $entries as $entry ) {
									$posts[] = $entry;
									$ids[] = $entry->ID;
								}
							}
						}

						echo '<table class="widefat fixed striped" cellspacing="0"><thead><tr><th class="column-cb check-column"></th><th>Before</th><th>After</th><th>Image</th></tr></thead>';

						if ( $posts && count( $posts ) > 0) {

							foreach ( $posts as $post ) {

								$i = 0;
								$mod = false;
								$id_displayed = false;
								$content = '';
								$chunks = explode( "\n", $post->post_content );
								$slug_checked = false;
								foreach ( $chunks as $chunk ) {
									$i++;
									$url = "";
									$pos1 = stripos( $chunk, 'src="' );
									if ( $pos1 > 0 ) {
										$pos1 += 5;
										$pos2 = stripos( $chunk, '"', $pos1 + 1 );
										if ( $pos2 > 0 ) {
											// Clean URL
											$url = urldecode( substr( $chunk, $pos1, $pos2 - $pos1 ) );
											if ( preg_match('/^(.*)-[\d]+x[\d]+\.(.*)$/', $url, $matches ) == 1 )
												$url = $matches[1].'.'.$matches[2];
										}
									}
									if ( stripos($chunk, '<img ') !== false ) {

										// Enum sizes
										for ( $w = $width; $w <= $width2; $w++ ) {

											// Check size in chunk interval
											if ( stripos($chunk, '-' . $w . 'x' ) > 0 || stripos( $chunk, 'width="' . $w . '"' ) > 0 ) {
												$attachment = null;

												/* Find by class (wp-image-xxxx) */
												$pos1 = stripos( $chunk, 'class="' );
												if ( $pos1 > 0 ) {
													$this->post_info( $post, $step, $id_displayed );
													if ( preg_match('/wp-image-(\d*)/', $chunk, $matches ) == 1)
														$id = $matches[1];

													// Search attachments
													$attachments = get_posts( array( 'p' => $id, 'post_type' => 'attachment' ) );
													if ( $attachments && is_array( $attachments ) && count( $attachments ) > 0 )
														$attachment = $attachments[0];

													// Check issues
													if ( $step == 1 ) {
														$mediafound = empty( $id ) ? 'No media found' :
															( 'Media <a href="' . get_edit_post_link( $id )
															. '" target="post_edit">[ID #' . $id . ']</a>' );
														if (!isset( $attachment ) ) {
															echo '<tr><td colspan="4" style="color: #d63737;">Couldn\'t find info: Media [ID #' . $id . ']</td></tr>';
														}
														else {
															$image = wp_get_attachment_image_src( $attachment->ID, $size );
															if (!$image) {
																$attachment = null;
																echo '<tr><td colspan="4" style="color: #d63737;">Seems it is not an image: ' .
																	$mediafound . '</td></tr>';
															}
															else {
																$file = get_attached_file( $attachment->ID );
																if ( !file_exists( $file ) ) {
																	$attachment = null;
																	echo '<tr><td colspan="4" style="color: #d63737;">File issues (does not exist/match): ' .
																		$mediafound . '</td></tr>';
																}
															}
														}
													}

												}

												// Check object
												if ( isset( $attachment ) ) {

													// Get image info
													$image = wp_get_attachment_image_src( $attachment->ID, $size );
													if ($image) {

														// Copy mod
														$mod_old = $mod;

														// Chunk edit
														$mod = true;
														$mod_sum = true;

														// Copy chunk
														$chunk_old = $chunk;

														// Replace data
														$chunk = preg_replace('/src=".*"/Ui', 'src="'.$image[0].'"', $chunk);
														$chunk = preg_replace('/width=".*"/Ui', 'width="'.$image[1].'"', $chunk);
														$chunk = preg_replace('/height=".*"/Ui', 'height="'.$image[2].'"', $chunk);
														$chunk = preg_replace('/class=\"(.*)size-[_a-zA-Z0-9-]*?([^\"]*)\"/Ui', 'class="'.'$1'.'size-'.$size.'$2'.'"', $chunk);

														// Set checkbox name
														$checkbox_name = 'ck-resize-' . $post->ID . '-' . $i;

														// Check step
														if ( $step == 1 ) {

															$image_thumb = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );

															// Display Info
															echo '<tr>
																<th class="check-column">
																	<input type="checkbox" checked name="' . $checkbox_name . '" id="' . $checkbox_name . '" value="1" />
																</td>
																<td style="font-size: 11px;">
																	' . htmlspecialchars( $chunk_old, ENT_QUOTES ) . '
																</td>
																<td style="font-size: 11px;">
																	' . htmlspecialchars( $chunk, ENT_QUOTES ) . '
																</td>
																<td>
																	Media [<a href="' . get_edit_post_link( $id ) . '" target="post_edit">ID #' . $id  . '</a>]<br />
																	<img style="max-width: 64px; height: auto !important;" src="' . str_replace( '.dev', '.es', $image_thumb[0] ).'" />
																</td>
															</tr>';
														}

														// Check chunk modified
														elseif ( ! ( isset( $_POST[$checkbox_name] ) && $_POST[$checkbox_name] == '1' ) ) {
															$mod = $mod_old;
															$chunk = $chunk_old;
														}
													}
													break;
												}
											}
										}
									}

									// Re-compose content
									$content .= ( ( $content !== '' ) ? "\n" : '' ) . $chunk;
								}

								// Check for post updates
								if ( $mod && $step == 2 ) {
									$wpdb->query( 'UPDATE ' . $wpdb->posts . ' SET post_content = "' . esc_sql( $content ) .'" WHERE ID = ' . $post->ID );
									clean_post_cache( $post->ID );
								}
							}
						}

						echo '</table>';

						if ( $step == 1 ) {
							if ( !$mod_sum )
								echo '<p style="background: #4e80bf; padding: 5px; color: #ffffff; text-align: center; margin: 10px 5px; border-radius: 3px;"> ' .
									__( 'There is nothing to do. Pick a difference width maybe?', 'regenerate-thumbnails-html' ) . '</p>';
							else {
								echo '<p style="background: #bf5e4e;; padding: 5px; color: #ffffff; text-align: center; margin: 10px 5px; border-radius: 3px;"> ' .
									sprintf( __( 'Now, careful! Backup your database! (in particularly, the <b>%s</b> table).', 'regenerate-thumbnails-html' ), $wpdb->posts ) . '</p>';
								submit_button( "Update HTML" );
							}
						}
						else if ( $step == 2 )
							echo '<p style="background: #4e80bf; padding: 5px; color: #ffffff; text-align: center; margin: 10px 5px; border-radius: 3px;"> ' .
								__( 'The HTML was updated.', 'regenerate-thumbnails-html' ) . '</p>';

					echo "</div></div>";

					endif;

				?>
				</div>
				</div>
				</form>
		<?php
	}


	/*
	 * Show posts info: Id and links to single post and edit form
	 */
	private function post_info($post, $step, &$id_displayed) {
		if ($step == 1 && !$id_displayed) {
			echo '<tr><td colspan="4" style="background: #0073aa; font-size: 100%; color: white; padding: 4px 10px;">'
				. '<a style="color: white;" href="'. get_permalink( $post->ID ) . '" target="post_display">'
				. $post->post_title . '</a> [<a style="color: white;" href="' . get_edit_post_link( $post->ID ) . '" target="post_edit">'
				. 'ID #' . $post->ID
				. '</a>]</td></tr>';
			$id_displayed = true;
		}
	}

}

	new Meow_Regenerate_Thumbnails_HTML;

<?php

/**
 * Class registering the core metaboxes for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Core Metaboxes
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnMetabox {

	/**
	 * The core metabox options array.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * Initiate the core metaboxes and fields.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $metabox Instance of the cmMetaboxAPI.
	 *
	 * @return void
	 */
	public static function init( $metabox ) {

		// Build the array that defines the core metaboxes.
		self::register();

		// Register the core metaboxes the Metabox API.
		$metabox::add( self::$metaboxes );
	}

	/**
	 * Register the core metabox and fields.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return void
	 */
	private static function register() {

		self::$metaboxes[] = array(
			'id'       => 'meta',
			'title'    => __( 'Custom Fields', 'connection' ),
			'name'     => 'Meta',
			'desc'     => __( 'Custom fields can be used to add extra metadata to an entry that you can use in your template.', 'connections' ),
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'meta' ),
		);

	}

	/**
	 * Call back to render the "Custom Fields" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function meta( $entry, $metabox ) {

		// Only need the data from $metabox['args'].
		$value   = $entry->getMeta( 'meta', TRUE );
		$results = $entry->getMeta();
		$metabox = $metabox['args'];
		$keys    = cnMeta::key( 'entry' );

		// Build the meta key select drop down options.
		array_walk( $keys, create_function( '&$key', '$key = "<option value=\"$key\">$key</option>";' ) );
		array_unshift( $keys, '<option value="-1">&mdash; ' . __( 'Select', 'connections' ) . ' &mdash;</option>');
		$options = implode( $keys, PHP_EOL );

		// echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';

		echo '<div class="cn-metabox-section" id="meta-fields">';

		?>

		<table id="list-table" style="<?php echo ( empty( $results ) ? 'display: none;' : 'display: table;' ) ?>">
			<thead>
				<tr>
					<th class="left"><?php _e( 'Name', 'connections' ); ?></th>
					<th><?php _e( 'Value', 'connections' ); ?></th>
				</tr>
			</thead>

			<tbody id="the-list">

			<?php

			if ( ! empty( $results ) ) {

				foreach ( $results as $metaID => $meta ) {

					// Class added to alternate tr rows for CSS styling.
					$alternate = ! isset( $alternate ) || $alternate == '' ? 'alternate' : '';

					?>

					<tr id="meta-<?php echo $metaID; ?>" class="<?php echo $alternate; ?>">

						<td class="left">
							<label class="screen-reader-text" for='meta[<?php echo $metaID; ?>][key]'><?php _e( 'Key', 'connections' ); ?></label>
							<input name='meta[<?php echo $metaID; ?>][key]' id='meta[<?php echo $metaID; ?>][key]' type="text" size="20" value="<?php echo esc_textarea( $meta['meta_key'] ) ?>" />
							<div class="submit">
								<input type="submit" name="deletemeta[<?php echo $metaID; ?>]" id="deletemeta[<?php echo $metaID; ?>]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
								<!-- <input type="submit" name="meta-<?php echo $metaID; ?>-submit" id="meta-<?php echo $metaID; ?>-submit" class="button updatemeta button-small" value="Update" /> -->
							</div>
							<!-- <input type="hidden" id="_ajax_nonce" name="_ajax_nonce" value="0db0125bba" /> -->
						</td>
						<td>
							<label class="screen-reader-text" for='meta[<?php echo $metaID; ?>][value]'><?php _e( 'Value', 'connections' ); ?></label>
							<textarea name='meta[<?php echo $metaID; ?>][value]' id='meta[<?php echo $metaID; ?>][value]' rows="2" cols="30"><?php echo esc_textarea( $meta['meta_value'] ) ?></textarea>
						</td>

					</tr>

					<?php
				}

				?>

			<?php

			}

			?>

			<!-- This is the row that will be cloned via JS when adding a new Custom Field. -->
			<tr style="display: none;">

				<td class="left">
					<label class="screen-reader-text" for='newmeta[0][key]'><?php _e( 'Key', 'connections' ); ?></label>
					<input name='newmeta[0][key]' id='newmeta[0][key]' type="text" size="20" value=""/>
					<div class="submit">
						<input type="submit" name="deletemeta[0]" id="deletemeta[0]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
						<!-- <input type="submit" name="newmeta-0-submit" id="newmeta-0-submit" class="button updatemeta button-small" value="Update" /> -->
					</div>
					<!-- <input type="hidden" id="_ajax_nonce" name="_ajax_nonce" value="0db0025bba" /> -->
				</td>
				<td>
					<label class="screen-reader-text" for='newmeta[0][value]'><?php _e( 'Value', 'connections' ); ?></label>
					<textarea name='newmeta[0][value]' id='newmeta[0][value]' rows="2" cols="30"></textarea>
				</td>

			</tr>

			</tbody>
		</table>

		<p><strong><?php _e( 'Add New Custom Field:', 'connections' ); ?></strong></p>

		<table id="newmeta">
			<thead>
				<tr>
					<th class="left"><label for="metakeyselect"><?php _e( 'Name', 'connections' ); ?></label></th>
					<th><label for="metavalue"><?php _e( 'Value', 'connections' ); ?></label></th>
				</tr>
			</thead>
			<tbody>

				<tr>

					<td id="newmetaleft" class="left">
						<select id="metakeyselect" name="metakeyselect">
							<?php echo $options; ?>
						</select>
						<input class="hide-if-js" type=text id="metakeyinput" name="newmeta[0][key]" value=""/>
						<a href="#postcustomstuff" class="postcustomstuff hide-if-no-js"> <span id="enternew"><?php _e( 'Enter New', 'connections' ); ?></span> <span id="cancelnew" class="hidden"><?php _e( 'Cancel', 'connections' ); ?></span></a>
					</td>

					<td>
						<textarea id="metavalue" name="newmeta[0][value]" rows="2" cols="25"></textarea>
					</td>

				</tr>



			</tbody>
			<tfoot>
				<td colspan="2">
					<div class="submit">
						<input type="submit" name="addmeta" id="newmeta-submit" class="button" value="<?php _e( 'Add Custom Field', 'connections' ); ?>" />
					</div>
					<!-- <input type="hidden" id="_ajax_nonce-add-meta" name="_ajax_nonce-add-meta" value="a7f70d2878" /> -->
				</td>
			</tfoot>
		</table>

		<?php

		if ( isset( $metabox['desc'] ) && ! empty( $metabox['desc'] ) ) {

			printf( '<p>%1$s</p>',
				esc_html( $metabox['desc'] )
			);
		}

		echo '</div>';

	}

}

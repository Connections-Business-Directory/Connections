<?php

/**
 * Term Meta UI Class
 *
 * This class is base helper to be extended by other plugins that may want to
 * provide a UI for term meta values. It hooks into several different WordPress
 * core actions & filters to add columns to list tables, add fields to forms,
 * and handle the sanitization & saving of values.
 *
 * @package    Connections
 * @subpackage Term Meta UI API
 * @since      8.5.2
 * @credit     Stuttter
 * @link       https://github.com/stuttter
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Term Meta UI class.
 *
 * @since 8.5.2
 */
class cnTerm_Meta_UI {

	/**
	 * @since 8.5.2
	 * @var string Metadata key
	 */
	protected $meta_key = '';

	/**
	 * @since 8.5.2
	 * @var array Array of labels
	 */
	protected $labels = array(
		'singular'    => '',
		'plural'      => '',
		'description' => '',
	);

	/**
	 * @since 8.5.18
	 * @var string File for plugin
	 */
	public $file = '';

	/**
	 * @since 8.5.18
	 * @var string URL to plugin
	 */
	public $url = '';

	/**
	 * @since 8.5.18
	 * @var string Path to plugin
	 */
	public $path = '';

	/**
	 * @since 8.5.18
	 * @var string Basename for plugin
	 */
	public $basename = '';

	/**
	 * Hook into queries, admin screens, and more.
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param string $file
	 */
	public function __construct( $file = '' ) {

		$this->file       = $file;
		$this->url        = plugin_dir_url( $this->file );
		$this->path       = plugin_dir_path( $this->file );
		$this->basename   = plugin_basename( $this->file );

		// Register the column header.
		add_filter( 'cn_category_table_columns', array( $this, 'columnHeader' ) );

		// Register the column value.
		add_action( 'cn_manage_category_custom_column', array( $this, 'columnValue' ), 10, 3 );

		// Render the form fields for adding the category color.
		add_action( 'cn_category_add_form_fields', array( $this, 'formFieldAdd' ) );
		add_action( 'cn_category_edit_form_fields', array( $this, 'formFieldEdit' ) );

		// Save the category order.
		add_action( 'cn_created_category', array( $this, 'save' ), 10, 2 );
		add_action( 'cn_edited_category', array( $this, 'save' ), 10, 2 );

		add_action( 'load-connections_page_connections_categories', array( $this, 'init' ) );
	}

	/**
	 * Administration area hooks.
	 *
	 * @access private
	 * @since  8.5.2
	 */
	public function init() {

		// Enqueue scripts and help tab.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'help_tabs' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @access public
	 * @since  8.5.2
	 */
	public function enqueue_scripts() {}

	/**
	 * Add help tabs for metadata.
	 *
	 * @access public
	 * @since 8.5.2
	 */
	public function help_tabs() {}

	/**
	 * Quick edit ajax updating.
	 *
	 * @access public
	 * @since 8.5.2
	 */
	public function ajax_update() {}

	/**
	 * Add the "meta_key" column to taxonomy terms list-tables.
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function columnHeader( $columns = array() ) {

		$columns[ $this->meta_key ] = $this->labels['singular'];

		return $columns;
	}

	/**
	 * Output the value for the custom column
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param object $term
	 * @param string $column_name
	 * @param int    $term_id
	 *
	 * @return mixed
	 */
	public function columnValue( $term, $column_name, $term_id ) {

		// Bail if no taxonomy passed or not on the `meta_key` column
		if ( $this->meta_key !== $column_name ) {

			return $term;
		}

		// Get the metadata
		$meta = $this->get( $term_id );

		// Output HTML element if not empty
		if ( ! empty( $meta ) ) {

			$html = $this->renderColumnValue( $meta );

		} else {

			$html = $this->renderColumnValue();
		}

		echo $html;

		return $term;
	}

	/**
	 * Return the formatted output for the column row
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function renderColumnValue( $value = '' ) {

		return esc_attr( $value );
	}

	/**
	 * Add `meta_key` to term when updating
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param  int $term_id Term ID.
	 * @param  int $tt_id   Taxonomy Term ID.
	 */
	public function save( $term_id, $tt_id ) {

		// Get the term being posted
		$term_key = 'term-' . $this->meta_key;

		// Bail if not updating meta_key
		$value = ! empty( $_POST[ $term_key ] ) ? $_POST[ $term_key ] : '';

		if ( empty( $value ) ) {

			cnMeta::delete( 'term', $term_id, $this->meta_key );

			// Update meta_key value
		} else {

			cnMeta::update( 'term', $term_id, $this->meta_key, $value );
		}
	}

	/**
	 * Return the `meta_key` of a term
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param int $term_id
	 *
	 * @return array|bool|string
	 */
	public function get( $term_id = 0 ) {

		return cnMeta::get( 'term', $term_id, $this->meta_key, TRUE );
	}

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @access public
	 * @since  8.5.2
	 */
	public function formFieldAdd() {

		?>

		<div class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
				<?php echo esc_html( $this->labels['singular'] ); ?>
			</label>

			<?php $this->formField( new stdClass() ); ?>

			<?php if ( ! empty( $this->labels['description'] ) ) : ?>

				<p class="description">
					<?php echo esc_html( $this->labels['description'] ); ?>
				</p>

			<?php endif; ?>

		</div>

		<?php
	}

	/**
	 * Output the form field when editing an existing term
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param object $term
	 */
	public function formFieldEdit( $term ) {

		?>

		<tr class="form-field term-<?php echo esc_attr( $this->meta_key ); ?>-wrap">
			<th scope="row" valign="top">
				<label for="term-<?php echo esc_attr( $this->meta_key ); ?>">
					<?php echo esc_html( $this->labels['singular'] ); ?>
				</label>
			</th>
			<td>
				<?php $this->formField( $term ); ?>

				<?php if ( ! empty( $this->labels['description'] ) ) : ?>

					<p class="description">
						<?php echo esc_html( $this->labels['description'] ); ?>
					</p>

				<?php endif; ?>

			</td>
		</tr>

		<?php
	}

	/**
	 * Output the form field
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param object $term
	 */
	protected function formField( $term ) {

		// Get the meta value
		$value = isset( $term->term_id ) ? $this->get( $term->term_id ) : ''; ?>

		<input type="text" name="term-<?php echo esc_attr( $this->meta_key ); ?>"
		       id="term-<?php echo esc_attr( $this->meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>">

		<?php
	}
}

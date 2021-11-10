<?php
/**
 * Term Meta UI Class
 *
 * This class is base helper to be extended by other plugins that may want to
 * provide a UI for term meta values. It hooks into several WordPress
 * core actions & filters to add columns to list tables, add fields to form,
 * and handle the sanitization & saving of values.
 *
 * @package    Connections
 * @subpackage Term Meta UI API
 * @since      8.5.2
 * @credit     Stuttter
 * @link       https://github.com/stuttter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Term Meta UI class.
 *
 * @since 8.5.2
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */
class cnTerm_Meta_UI {

	/**
	 * Metadata key.
	 *
	 * @since 8.5.2
	 * @var string
	 */
	protected $meta_key = '';

	/**
	 * Array of labels.
	 *
	 * @since 8.5.2
	 * @var array
	 */
	protected $labels = array(
		'singular'    => '',
		'plural'      => '',
		'description' => '',
	);

	/**
	 * File for plugin.
	 *
	 * @since 8.5.18
	 * @var string
	 */
	public $file = '';

	/**
	 * URL to plugin.
	 *
	 * @since 8.5.18
	 * @var string
	 */
	public $url = '';

	/**
	 * Path to plugin.
	 *
	 * @since 8.5.18
	 * @var string
	 */
	public $path = '';

	/**
	 * Basename for plugin.
	 *
	 * @since 8.5.18
	 * @var string
	 */
	public $basename = '';

	/**
	 * Hook into queries, admin screens, and more.
	 *
	 * @access public
	 * @since  8.5.2
	 *
	 * @param string $file Path to plugin.
	 */
	public function __construct( $file = '' ) {

		$this->file     = $file;
		$this->url      = plugin_dir_url( $this->file );
		$this->path     = plugin_dir_path( $this->file );
		$this->basename = plugin_basename( $this->file );

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

		// add_action( 'load-connections_page_connections_categories', array( $this, 'init' ) );
		add_action( 'load-connections_page_connections_manage_category_terms', array( $this, 'init' ) );
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
	 * @since 8.5.2
	 */
	public function enqueue_scripts() {}

	/**
	 * Add help tabs for metadata.
	 *
	 * @since 8.5.2
	 */
	public function help_tabs() {}

	/**
	 * Quick edit ajax updating.
	 *
	 * @since 8.5.2
	 */
	public function ajax_update() {}

	/**
	 * Add the "meta_key" column to taxonomy terms list-tables.
	 *
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
	 * @since 8.5.2
	 *
	 * @param object $term
	 * @param string $column_name
	 * @param int    $term_id
	 *
	 * @return object
	 */
	public function columnValue( $term, $column_name, $term_id ) {

		// Bail if no taxonomy passed or not on the `meta_key` column.
		if ( $this->meta_key !== $column_name ) {

			return $term;
		}

		// Get the metadata.
		$meta = $this->get( $term_id );

		// Output HTML element if not empty.
		if ( ! empty( $meta ) ) {

			$html = $this->renderColumnValue( $meta );

		} else {

			$html = $this->renderColumnValue();
		}

		// HTML is escaped in overriding classes.
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return $term;
	}

	/**
	 * Return the formatted output for the column row
	 *
	 * @since 8.5.2
	 *
	 * @param string $value The meta value.
	 *
	 * @return string
	 */
	protected function renderColumnValue( $value = '' ) {

		return esc_attr( $value );
	}

	/**
	 * Add `meta_key` to term when updating
	 *
	 * @since 8.5.2
	 *
	 * @param int $term_id Term ID.
	 * @param int $tt_id   Taxonomy Term ID.
	 */
	public function save( $term_id, $tt_id ) {

		// Get the term being posted.
		$term_key = 'term-' . $this->meta_key;

		// Bail if not updating meta_key.
		$value = ! empty( $_POST[ $term_key ] ) ? $_POST[ $term_key ] : '';

		if ( empty( $value ) ) {

			cnMeta::delete( 'term', $term_id, $this->meta_key );

			// Update meta_key value.
		} else {

			cnMeta::update( 'term', $term_id, $this->meta_key, $value );
		}
	}

	/**
	 * Return the `meta_key` of a term
	 *
	 * @since 8.5.2
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return array|bool|string
	 */
	public function get( $term_id = 0 ) {

		return cnMeta::get( 'term', $term_id, $this->meta_key, true );
	}

	/**
	 * Output the form field for this metadata when adding a new term
	 *
	 * @since 8.5.2
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
	 * @since 8.5.2
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
	 * @since 8.5.2
	 *
	 * @param object $term Instance of the term object.
	 */
	protected function formField( $term ) {

		// Get the meta value.
		$value = isset( $term->term_id ) ? $this->get( $term->term_id ) : '';
		?>
		<input type="text" name="term-<?php echo esc_attr( $this->meta_key ); ?>" id="term-<?php echo esc_attr( $this->meta_key ); ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php
	}
}

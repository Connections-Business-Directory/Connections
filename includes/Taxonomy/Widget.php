<?php

namespace Connections_Directory\Taxonomy;

use cnEntry;
use cnQuery;
use cnShortcode;
use Connections_Directory\Content_Block;
use Connections_Directory\Content_Blocks;
use Connections_Directory\Taxonomy;
use Connections_Directory\Utility\_format;
use WP_Widget;

/**
 * Class Widget
 *
 * @package Connections_Directory\Taxonomy
 */
final class Widget extends WP_Widget {

	/**
	 * @since 10.2
	 * @var Taxonomy
	 */
	private $taxonomy;

	/**
	 * Widget constructor.
	 *
	 * @since 10.2
	 *
	 * @param string   $id_base
	 * @param string   $name
	 * @param Taxonomy $taxonomy
	 * @param array    $widget_options
	 * @param array    $control_options
	 */
	public function __construct( $id_base, $name, $taxonomy, $widget_options = array(), $control_options = array() ) {

		$this->taxonomy = $taxonomy;

		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}

	/**
	 * @since 10.2
	 *
	 * @return array
	 */
	private function defaults() {

		$defaults = array(
			'force_home' => false,
			'home_id'    => cnShortcode::getHomeID(),
			'title'      => $this->taxonomy->getLabels()->widget_name,
		);

		$defaults = apply_filters( 'Connections_Directory/Taxonomy/Widget/Default_Options', $defaults, $this );

		_format::toBoolean( $defaults['force_home'] );

		return $defaults;
	}

	/**
	 * Renders the settings update form.
	 *
	 * @since 10.2
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {

		// Defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$instance = apply_filters( 'Connections_Directory/Taxonomy/Widget/Form/Instance', $instance, $this );

		$title = esc_attr( $instance['title'] );

		do_action( 'Connections_Directory/Taxonomy/Widget/Form/Before', $instance, $this );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _ex( 'Title:', 'widget title', 'connections' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php

		do_action( 'Connections_Directory/Taxonomy/Widget/Form/After', $instance, $this );
	}

	/**
	 * Updates widget instance settings.
	 *
	 * @since 10.2
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return apply_filters( 'Connections_Directory/Taxonomy/Widget/Update', $instance, $old_instance, $this );
	}

	/**
	 * Render the widget.
	 *
	 * @since 10.2
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the widget instance.
	 */
	public function widget( $args, $instance ) {

		// Display the widget when displaying a single entry.
		if ( ! $slug = cnQuery::getVar( 'cn-entry-slug' ) ) {
			return;
		}

		// Defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$instance = apply_filters( 'Connections_Directory/Taxonomy/Widget/Instance', $instance, $this );

		// Query the entry.
		$result = Connections_Directory()->retrieve->entry( urldecode( $slug ) );

		if ( false === $result ) {

			return;
		}

		/*
		 * Define the block ID instance to retrieve.
		 * The "legacy" categories taxonomy already has a registered Content Block, use its ID instead.
		 */
		$taxonomySlug = $this->taxonomy->getSlug();
		$blockID      = 'category' === $taxonomySlug ? 'entry-categories' : "taxonomy-{$taxonomySlug}";

		/**
		 * This filter is documented in includes/Taxonomy.php
		 * @see Taxonomy::registerContentBlock()
		 */
		$blockID = apply_filters( 'Connections_Directory/Taxonomy/Register/Content_Block/ID', $blockID );

		// Get an instance of the taxonomy block.
		$block = Content_Blocks::instance()->get( $blockID );

		if ( ! $block instanceof Content_Block ) {

			return;
		}

		// Setup the entry object
		$entry = new cnEntry( $result );

		// Configure the page where the entry link to.
		$entry->directoryHome(
			array(
				'page_id'    => $instance['home_id'],
				'force_home' => $instance['force_home'],
			)
		);

		// Setup the taxonomy block.
		$block->useObject( $entry );

		$block->set( 'widget', $this );
		$block->set( 'widget_options', $instance );

		$block->set( 'render_container', false );
		$block->set( 'type', 'list' );
		$block->set( 'label', '' );

		$block = apply_filters( 'Connections_Directory/Taxonomy/Widget/Block', $block, $instance, $this );

		$blockHTML = $block->asHTML();

		// If the block has no content, do not render the widget.
		if ( 0 === strlen( $blockHTML ) ) {

			return;
		}

		/**
		 * Filter the widget title.
		 *
		 * Use the `widget_title` hook name to match core WP widgets.
		 *
		 * @since 10.2
		 *
		 * @param string $title    The widget title. Default 'Pages'.
		 * @param array  $instance Array of settings for the current widget.
		 * @param mixed  $id_base  The widget ID.
		 */
		$title = apply_filters(
			'widget_title',
			$instance['title'],
			$instance,
			$this->id_base
		);

		echo $args['before_widget'];

		do_action( 'Connections_Directory/Taxonomy/Widget/Before', $args, $instance, $this );

		if ( 0 < strlen( $title ) ) {

			echo $args['before_title'] . $title . $args['after_title'] . PHP_EOL;
		}

		echo $blockHTML;

		do_action( 'Connections_Directory/Taxonomy/Widget/After', $args, $instance, $this );

		echo $args['after_widget'];
	}
}

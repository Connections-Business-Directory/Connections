<?php
class cnTemplate {
	/**
	 * Template Name.
	 * @var string
	 */
	public $name;

	/**
	 * Template slug [template directory]
	 * @var string
	 */
	public $slug;

	/**
	 * Template URL
	 * @var string
	 */
	public $url;

	/**
	 * The template author's uri.
	 * @var
	 */
	public $uri;

	/**
	 * Template version.
	 * @var string
	 */
	public $version;

	/**
	 * Tamplate's author's name.
	 * @var string
	 */
	public $author;

	/**
	 * Template description.
	 * @var string
	 */
	public $description;

	/**
	 * Set TRUE if the template should use the legacy output from cnOutput.
	 * @var bool
	 */
	public $legacy;

	/**
	 * TRUE if the template is in the custom template directory.
	 * @var
	 */
	public $custom;

	/**
	 * Path to the template.php file.
	 * @var string
	 */
	public $file;

	/**
	 * The path to the template's CSS file.
	 * @var string
	 */
	public $cssPath;

	public $printCSS = array();
	public $printJS = array();

	/**
	 * The path to the template's Javascript file.
	 * @var string
	 */
	public $jsPath;

	/**
	 * The template base path.
	 * @var string
	 */
	public $path;

	/**
	 * Stores the catalog of available templates when cnTemplate::buildCtalog() is called.
	 *
	 * @var object
	 */
	private $catalog;


	/**
	 * Builds a catalog of all the available templates from
	 * the supplied and the custom template directories.
	 *
	 * @return array
	 */
	public function buildCatalog() {
		/**
		 * --> START <-- Find the available templates
		 */
		$templatePaths = array( CN_TEMPLATE_PATH , CN_CUSTOM_TEMPLATE_PATH );
		$templates = new stdClass();

		foreach ( $templatePaths as $templatePath ) {
			if ( ! is_dir( $templatePath ) && ! is_readable( $templatePath ) ) continue;

			if ( ! $templateDirectories = @opendir( $templatePath ) ) continue;
			//var_dump($templatePath);

			//$templateDirectories = opendir($templatePath);

			while ( ( $templateDirectory = readdir($templateDirectories) ) !== FALSE ) {

				if ( is_dir ( $templatePath . $templateDirectory ) && is_readable( $templatePath . $templateDirectory ) ) {

					if ( file_exists( $templatePath . $templateDirectory . '/meta.php' ) && file_exists( $templatePath . $templateDirectory . '/template.php' ) ) {

						$template = new stdClass();
						include( $templatePath . $templateDirectory . '/meta.php');
						$template->slug = $templateDirectory;

						// Load the template metadate from the meta.php file

						if ( ! isset( $template->type ) ) $template->type = 'all';

						// PHP 5.4 warning fix.
						if ( ! isset( $templates->{$template->type} ) ) $templates->{$template->type} = new stdClass();
						if ( ! isset( $templates->{$template->type}->{$template->slug} ) ) $templates->{$template->type}->{$template->slug} = new stdClass();

						$templates->{$template->type}->{$template->slug}->name = $template->name;
						$templates->{$template->type}->{$template->slug}->version = $template->version;
						$templates->{$template->type}->{$template->slug}->uri = 'http://' . $template->uri;
						$templates->{$template->type}->{$template->slug}->author = $template->author;
						( isset( $template->description ) ) ? $templates->{$template->type}->{$template->slug}->description = $template->description : $templates->{$template->type}->{$template->slug}->description = '';

						( ! isset($template->legacy) ) ? $templates->{$template->type}->{$template->slug}->legacy = TRUE : $templates->{$template->type}->{$template->slug}->legacy = $template->legacy;
						$templates->{$template->type}->{$template->slug}->slug = $template->slug;
						$templates->{$template->type}->{$template->slug}->custom = ( $templatePath === CN_TEMPLATE_PATH ) ? FALSE : TRUE;
						$templates->{$template->type}->{$template->slug}->path = $templatePath . $templateDirectory;
						$templates->{$template->type}->{$template->slug}->url = ( $templatePath === CN_TEMPLATE_PATH ) ? CN_TEMPLATE_URL . $template->slug : CN_CUSTOM_TEMPLATE_URL . $template->slug;
						$templates->{$template->type}->{$template->slug}->file = $templatePath . $templateDirectory . '/template.php';

						if ( file_exists( $templates->{$template->type}->{$template->slug}->path . '/' . 'styles.css' ) ) {
							$templates->{$template->type}->{$template->slug}->cssPath = $templates->{$template->type}->{$template->slug}->path . '/' . 'styles.css';
						}

						if ( file_exists( $templates->{$template->type}->{$template->slug}->path . '/' . 'template.js' ) ) {
							$templates->{$template->type}->{$template->slug}->jsPath = $templates->{$template->type}->{$template->slug}->path . '/' . 'template.js';
						}

						if ( file_exists( $templates->{$template->type}->{$template->slug}->path . '/' . 'functions.php' ) ) {
							$templates->{$template->type}->{$template->slug}->phpPath = $templates->{$template->type}->{$template->slug}->path . '/' . 'functions.php';
						}

						if ( file_exists( $templates->{$template->type}->{$template->slug}->path . '/' . 'thumbnail.png' ) ) {
							$templates->{$template->type}->{$template->slug}->thumbnailPath = $templates->{$template->type}->{$template->slug}->path . '/' . 'thumbnail.png';
							$templates->{$template->type}->{$template->slug}->thumbnailURL = $templates->{$template->type}->{$template->slug}->url . '/' . 'thumbnail.png';
						}
					}
				}
			}

			//var_dump($templateDirectories);
			@closedir($templateDirectories);
		}
		/**
		 * --> END <-- Find the available templates
		 */
		$this->catalog = $templates;

		return $templates;
	}

	/**
	 * Returns the catalog of templates by the supplied type.
	 *
	 * @param string $type
	 * @return object
	 */
	public function getCatalog($type)
	{
		if ($type !== 'all') {
			return (object) array_merge( (array) $this->catalog->all, (array) $this->catalog->$type);
		} else {
			return $this->catalog->$type;
		}
	}

	/**
	 * Loads the template based on the supplied directory name [$slug].
	 * This will search the both the default templates directory and
	 * the connections_templates directory in wp_content.
	 *
	 * @param string $slug
	 */
	public function load($slug)
	{
		$templatePaths = array( CN_CUSTOM_TEMPLATE_PATH, CN_TEMPLATE_PATH );
		$template = new stdClass();

		foreach ( $templatePaths as $templatePath ) {

			if ( is_dir( $templatePath . $slug ) && is_readable( $templatePath . $slug ) ) {

				if ( file_exists( $templatePath . $slug . '/meta.php' ) &&  file_exists( $templatePath . $slug . '/template.php' ) ) {

					$this->slug = $slug;
					$this->path = $templatePath . $slug;
					$this->url = ( $templatePath === CN_TEMPLATE_PATH ) ? CN_TEMPLATE_URL . $this->slug : CN_CUSTOM_TEMPLATE_URL . $this->slug;
					$this->file = $this->path . '/template.php';

					include( $this->path . '/meta.php' );

					$this->name = $template->name;
					$this->uri = $template->uri;
					$this->version = $template->version;
					$this->author = $template->author;
					$this->description = $template->description;
					$this->legacy = $template->legacy;

					$this->custom = ( $templatePath === CN_TEMPLATE_PATH ) ? FALSE : TRUE;
					if ( file_exists( $this->path . '/' . 'styles.css') ) $this->cssPath = $this->path . '/' . 'styles.css';
					if ( file_exists( $this->path . '/' . 'template.js') ) $this->jsPath = $this->path . '/' . 'template.js';
					if ( file_exists( $this->path . '/' . 'functions.php') ) $this->phpPath = $this->path . '/' . 'functions.php';

					$this->includeFunctions();
					$this->printJS();

					break;
				}
			} elseif ( is_file( $templatePath . $slug . '.php' ) ) {
				$this->slug = $slug;
				$this->path = $templatePath;
				$this->url = ( $templatePath === CN_TEMPLATE_PATH ) ? CN_TEMPLATE_URL : CN_CUSTOM_TEMPLATE_URL;
				$this->file = $this->path . '/' . $slug . '.php';
			}
		}

	}

	/**
	 * Initialize the class with a template object.
	 *
	 * @TODO If any of the required properties are not supplied this should return FALSE.
	 * @param object $template
	 */
	public function init($template)
	{
		$this->name = $template->name; // REQUIRED
		$this->slug = $template->slug; // REQUIRED
		$this->url = $template->url; // REQUIRED
		$this->uri = $template->uri; // REQUIRED
		if ( isset($template->version) ) $this->version = $template->version;
		if ( isset($template->author) ) $this->author = $template->author;
		if ( isset($template->description) ) $this->description = $template->description;
		if ( isset($template->legacy) ) $this->legacy = $template->legacy;
		if ( isset($template->custom) ) $this->custom = $template->custom;
		$this->file = $template->file; // REQUIRED
		if ( isset($template->cssPath) ) $this->cssPath = $template->cssPath;
		if ( isset($template->jsPath) ) $this->jsPath = $template->jsPath;
		if ( isset($template->phpPath) ) $this->phpPath = $template->phpPath;
		if ( isset($template->path) ) $this->path = $template->path;

		$this->includeFunctions();
		$this->printJS();
	}

	public function reset()
	{
		$this->name = ''; // REQUIRED
		$this->slug = ''; // REQUIRED
		$this->url = ''; // REQUIRED
		$this->uri = ''; // REQUIRED
		$this->version = '';
		$this->author = '';
		$this->description = '';
		$this->legacy = '';
		$this->custom = '';
		$this->file = ''; // REQUIRED
		$this->cssPath = '';
		$this->jsPath = '';
		$this->phpPath = '';
		$this->path = '';
	}

	/**
	 * Loads the CSS file while replacing %%PATH%% with the URL
	 * to the template.
	 *
	 * @return string
	 */
	public function printCSS()
	{
		if ( empty($this->cssPath) ) return '';

		/*
		 * The intent was to keep a log as the CSS was output so if a template was called multiple time on on the same page,
		 * the CSS would not output multiple times. The issue is that some plugins pre-process the page content. When this hppens
		 * the CSS is not output when needed.
		 *
		 * @TODO Create a page pre-process function so the CSS outputs only once in the page head.
		 */

		//if ( ! in_array( $this->slug , $this->printCSS ) )
		//{

			$this->printCSS[] = $this->slug;

			// Loads the CSS style in the body, valid HTML5 when set with the 'scoped' attribute.
			$out = "\n" . '<style type="text/css" scoped>' . "\n";
			$out .= str_replace( '%%PATH%%' , $this->url , file_get_contents( $this->cssPath ) );
			$out .= "\n" . '</style>' . "\n";
		//}

		return $out;
	}

	/**
	 * Prints the template's JS in the theme's footer.
	 *
	 * NOTE: As of WP3.3 simply using wp_enqueue_script() should work to print the script in the footer.
	 */
	public function printJS()
	{
		// Prints the javascript tag in the footer if $template->js path is set
		if ( isset($this->jsPath) && ! empty($this->jsPath) )
		{
			if ( ! in_array( $this->slug , $this->printJS ) )
			{
				$this->printJS[] = $this->slug;

				wp_register_script("cn_{$this->slug}_js", $this->url . '/template.js', array(), $this->version, TRUE);

				$printJS = create_function
				(
					'',
					'global $connections;

					if ( isset($connections->template->printJS) && ! empty($connections->template->printJS) ) {
						foreach ( $connections->template->printJS as $slug) {
							wp_print_scripts("cn_{$slug}_js");
						}
					}'
				);

				add_action( 'wp_footer', $printJS );
			}
		}
	}

	/**
	 * Include the template functions.php file if present.
	 */
	private function includeFunctions()
	{
		if ( ! empty( $this->phpPath ) ) {
			include_once( $this->phpPath );
		}
	}

	/**
	 * The return to top anchor.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function returnToTop( $atts = array() )
	{
		$defaults = array(
			'return' => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		$out = '<a href="#cn-top" title="' . __('Return to top.', 'connections') . '"><img src="' . CN_URL . 'images/uparrow.gif" alt="' . __('Return to top.', 'connections') . '"/></a>';

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * Create the search input.
	 *
	 * Accepted option for the $atts property are:
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @uses get_query_var()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function search( $atts = array() )
	{
		$out = '';

		$defaults = array(
			'show_label' => TRUE,
			'return' => FALSE
		);

		$atts = wp_parse_args($atts, $defaults);

		$searchValue = ( get_query_var('cn-s') ) ? get_query_var('cn-s') : '';

		$out .= '<span class="cn-search">';
			if ( $atts['show_label'] ) $out .= '<label for="cn-s">Search Directory</label>';
			$out .= '<input type="text" id="cn-search-input" name="cn-s" value="' . esc_attr($searchValue) . '" placeholder="' . __('Search', 'connections') . '"/>';
			$out .= '<input type="submit" name="" id="cn-search-submit" class="cn-search-button" value="" tabindex="-1" />';
		$out .= '</span>';

		// Output the the search input.
		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * Outputs a submit button.
	 *
	 * Accepted option for the $atts property are:
	 * 	name (string) The input name attribute.
	 * 	value (string) The input value attribute.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function submit( $atts = array() )
	{
		$out = '';

		$defaults = array(
			'name' => '',
			'value' => __('Submit', 'connections'),
			'return' => FALSE
		);

		$atts = wp_parse_args($atts, $defaults);

		$out .= '<input type="submit" name="' . $atts['name'] . '" id="cn-submit" class="button" value="' . $atts['value'] . '" tabindex="-1" />';

		// Output a submit button.
		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * Creates the pagination controls.
	 *
	 * Accepted option for the $atts property are:
	 * 	limit (int) The pagination page limit.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access public
	 * @since 0.7.3
	 * @version 1.0
	 * @uses wp_parse_args()
	 * @uses get_permalink()
	 * @uses get_query_var()
	 * @uses add_query_arg()
	 * @uses absint()
	 * @uses trailingslashit()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function pagination( $atts = array() )
	{
		global $wp_rewrite, $post,  $connections;

		$out = '';

		$defaults = array(
			'limit' => 20,
			'return' => FALSE
		);

		$atts = wp_parse_args($atts, $defaults);

		$pageCount = ceil( $connections->retrieve->resultCountNoLimit / $atts['limit'] );

		if ( $pageCount > 1 )
		{
			$current = 1;
			$disabled = array();
			$url = array();
			$page = array();
			$queryVars = array();

			// Get page/post permalink.
			// Only slash it when using pretty permalinks.
			$permalink = $wp_rewrite->using_permalinks() ? trailingslashit( get_permalink() ) : get_permalink();

			// Get the settings for the base of each data type to be used in the URL.
			$base = get_option('connections_permalink');

			// Store the query vars
			if ( get_query_var('cn-s') ) $queryVars['cn-s'] = get_query_var('cn-s');
			if ( get_query_var('cn-cat') ) $queryVars['cn-cat'] = get_query_var('cn-cat');
			if ( get_query_var('cn-near-coord') ) $queryVars['cn-near-coord'] = get_query_var('cn-near-coord');
			if ( get_query_var('cn-radius') ) $queryVars['cn-radius'] = get_query_var('cn-radius');
			if ( get_query_var('cn-unit') ) $queryVars['cn-unit'] = get_query_var('cn-unit');

			// Current page
			if ( get_query_var('cn-pg') ) $current = absint( get_query_var('cn-pg') );

			$page['first'] = 1;
			$page['previous'] = ( $current - 1 >= 1 ) ? $current - 1 : 1;
			$page['next'] = ( $current + 1 <= $pageCount ) ? $current + 1 : $pageCount;
			$page['last'] = $pageCount;

			// The class to apply to the disabled links.
			( $current > 1 ) ? $disabled['first'] = '' : $disabled['first'] = ' disabled';
			( $current - 1 >= 1 ) ? $disabled['previous'] = '' : $disabled['previous'] = ' disabled';
			( $current + 1 <= $pageCount ) ? $disabled['next'] = '' : $disabled['next'] = ' disabled';
			( $current < $pageCount ) ? $disabled['last'] = '' : $disabled['last'] = ' disabled';

			/*
			 * Create the page permalinks. If on a post or custom post type, use query vars.
			 */
			if ( is_page() && $wp_rewrite->using_permalinks() )
			{
				// Add the category base and path if paging thru a category.
				if ( get_query_var('cn-cat-slug') ) $permalink = trailingslashit( $permalink . $base['category_base'] . '/' . get_query_var('cn-cat-slug') );

				$url['first'] = add_query_arg( $queryVars , $permalink . 'pg/' . $page['first'] );
				$url['previous'] = add_query_arg( $queryVars , $permalink . 'pg/' . $page['previous'] );
				$url['next'] = add_query_arg( $queryVars , $permalink . 'pg/' . $page['next'] );
				$url['last'] = add_query_arg( $queryVars , $permalink . 'pg/' . $page['last'] );
			}
			else
			{
				// If on the front page, add the query var for the page ID.
				if ( is_front_page() ) $permalink = add_query_arg( 'page_id' , $post->ID );

				// Add back on the URL any other Connections query vars.
				$permalink = add_query_arg( $queryVars , $permalink );

				$url['first'] = add_query_arg( array( 'cn-pg' => $page['first'] ) , $permalink );
				$url['previous'] = add_query_arg( array( 'cn-pg' => $page['previous'] ) , $permalink );
				$url['next'] = add_query_arg( array( 'cn-pg' => $page['next'] ) , $permalink );
				$url['last'] = add_query_arg( array( 'cn-pg' => $page['last'] ) , $permalink );
			}

			// Build the html page nav.
			$out .= '<span class="cn-page-nav" id="cn-page-nav">';

			$out .= '<a href="' . $url['first'] . '" title="' . __('First Page', 'connections') . '" class="cn-first-page' . $disabled['first'] . '">&laquo;</a> ';
			$out .= '<a href="' . $url['previous'] . '" title="' . __('Previous Page', 'connections') . '" class="cn-prev-page' . $disabled['previous'] . '">&lsaquo;</a> ';

			$out .= '<span class="cn-paging-input"><input type="text" size="1" value="' . $current . '" name="cn-pg" title="' . __('Current Page', 'connections') . '" class="current-page"> ' . __('of', 'connections') . ' <span class="total-pages">' . $pageCount . '</span></span> ';

			$out .= '<a href="' . $url['next'] . '" title="' . __('Next Page', 'connections') . '" class="cn-next-page' . $disabled['next'] . '">&rsaquo;</a> ';
			$out .= '<a href="' . $url['last'] . '" title="' . __('Last Page', 'connections') . '" class="cn-last-page' . $disabled['last'] . '">&raquo;</a>';

			$out .= '</span>';
		}

		// Output the page nav.
		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * Parent public function that outputs the various categories output formats.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect || radio || checkbox
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 * 	show_select_all (bool) Whether or not to show the "Select All" option. Used for select && multiselect only.
	 * 	select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * NOTE: The $atts array is passed to a number of private methods to output the categories.
	 *
	 * @access public
	 * @version 1.0
	 * @since 0.7.3
	 * @uses wp_parse_args()
	 * @param array $atts [optional]
	 * @return string
	 */
	public function category( $atts = NULL )
	{
		$defaults = array(
			'type' => 'select',
			'group' => FALSE,
			'default' => __('Select Category', 'connections'),
			'show_select_all' => TRUE,
			'select_all' => __('Show All Categories', 'connections'),
			'show_empty' => TRUE,
			'show_count' => FALSE,
			'depth' => 0,
			'parent_id' => array(),
			'return' => FALSE
		);

		$atts = wp_parse_args($atts, $defaults);

		switch ( $atts['type'] )
		{
			case 'select':
				$this->categorySelect($atts);
				break;

			case 'multiselect':
				$this->categorySelect($atts);
				break;

			case 'radio':
				$this->categoryInput($atts);
				break;

			case 'checkbox':
				$this->categoryInput($atts);
				break;

			case 'link':
				$this->categoryLink($atts);
				break;
		}
	}

	/**
	 * The private function called by cnTemplate::category that outputs the select, multiselect; grouped and ungrouped.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	default (string) The default string to show as the first item in the list. Used for select && multiselect only.
	 * 	show_select_all (bool) Whether or not to show the "Select All" option. Used for select && multiselect only.
	 * 	select_all (string) The string to use for the "Select All" option. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private function categorySelect( $atts ) {
		global $connections;
		$selected = '';

		// $selected = get_query_var('cn-cat-slug') ? get_query_var('cn-cat-slug') : array();

		if ( get_query_var( 'cn-cat' ) ) {
			$selected = get_query_var( 'cn-cat' );
		} elseif( get_query_var( 'cn-cat-slug' ) ) {
			$selected = get_query_var( 'cn-cat-slug' );
		}

		$level = 1;
		$out = '';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'type'            => 'select',
			'group'           => FALSE,
			'default'         => __( 'Select Category', 'connections' ),
			'show_select_all' => TRUE,
			'select_all'      => __( 'Show All Categories', 'connections' ),
			'show_empty'      => TRUE,
			'show_count'      => FALSE,
			'depth'           => 0,
			'parent_id'       => array(),
			'return'          => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace( ' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		$out .= "\n" . '<select class="cn-cat-select" name="' . ( ( $atts['type'] == 'multiselect' ) ? 'cn-cat[]' : 'cn-cat' ) . '"' . ( ( $atts['type'] == 'multiselect' ) ? ' MULTIPLE ' : '' ) . ( ( $atts['type'] == 'multiselect' ) ? '' : ' onchange="this.form.submit()" ' ) . 'data-placeholder="' . esc_attr($atts['default']) . '">';

		$out .= "\n" . '<option value=""></option>';

		if ( $atts['show_select_all'] ) $out .= "\n" . '<option value="">' . esc_attr( $atts['select_all'] ) . '</option>';

		foreach ( $categories as $key => $category ) {
			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

			// If grouping by root parent is enabled, open the optiongroup tag.
			if ( $atts['group'] && ! empty( $category->children ) )
				$out .= sprintf( '<optgroup label="%1$s">' , $category->name );

			// Call the recursive function to build the select options.
			$out .= $this->categorySelectOption( $category, $level, $atts['depth'], $selected, $atts );

			// If grouping by root parent is enabled, close the optiongroup tag.
			if ( $atts['group'] && ! empty( $category->children ) )
				$out .= '</optgroup>' . "\n";
		}

		$out .= '</select>' . "\n";

		if ( $atts['type'] == 'multiselect' ) $out .= $this->submit( array( 'return' => TRUE ) );

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the select options.
	 *
	 * Accepted option for the $atts property are:
	 * 	group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $selected An array of the selected category IDs.
	 * @param array $atts
	 * @return string
	 */
	private function categorySelectOption( $category, $level, $depth, $selected, $atts ) {

		$out = '';

		$defaults = array(
			'group'      => FALSE,
			'show_empty' => TRUE,
			'show_count' => TRUE
		);

		$atts = wp_parse_args( $atts, $defaults );

		// The padding in px to indent descendant categories. The 7px is the default pad applied in the CSS which must be taken in to account.
		$pad = ( $level > 1 ) ? $level * 12 + 7 : 7;
		//$pad = str_repeat($atts['pad_char'], max(0, $level));

		// Set the option SELECT attribute if the category is one of the currently selected categories.
		$strSelected = ( ( $selected == $category->term_id ) || ( $selected == $category->slug ) ) ? ' SELECTED ' : '';
		// $strSelected = $selected ? ' SELECTED ' : '';

		// Category count to be appended to the category name.
		$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

		// If option grouping is TRUE, show only the select option if it is a descendant. The root parent was used as the option group label.
		if ( ( $atts['group'] && $level > 1 ) && ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) ) {
			$out .= sprintf('<option style="padding-left: %1$dpx !important" value="%2$s"%3$s>' . /*$pad .*/ $category->name . $count . '</option>' , $pad , $category->term_id , $strSelected );
		}
		// If option grouping is FALSE, show the root parent and descendant options.
		elseif ( ! $atts['group'] && ( $atts['show_empty'] || ! empty($category->count) || ! empty($category->children) ) ) {
			$out .= sprintf('<option style="padding-left: %1$dpx !important" value="%2$s"%3$s>' . /*$pad .*/ $category->name . $count . '</option>' , $pad , $category->term_id , $strSelected );
		}

		/*
		 * Only show the descendants based on the following criteria:
		 * 	- There are descendant categories.
		 * 	- The descendant depth is < than the current $level
		 *
		 * When descendant depth is set to 0, show all descendants.
		 * When descendant depth is set to < $level, call the recursive function.
		 */
		if ( ! empty( $category->children ) && ($depth <= 0 ? -1 : $level) < $depth ) {
			foreach ( $category->children as $child ) {
				$out .= $this->categorySelectOption( $child, $level + 1, $depth, $selected, $atts );
			}
		}

		return $out;
	}

	/**
	 * The private function called by cnTemplate::category that outputs the radio && checkbox in a table layout.
	 * Each category root parent and its descendants are output in an unordered list.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string) The ouput type of the categories. Valid options options are: select || multiselect
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 * 	columns (int) The number of columns in the table.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private function categoryInput( $atts = NULL ) {
		global $connections;

		$selected = ( get_query_var('cn-cat') ) ? get_query_var('cn-cat') : array();
		$categories = array();
		$level = 0;
		$out = '';
		$trClass = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'depth'      => 0,
			'parent_id'  => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );


		if ( ! empty( $atts['parent_id'] ) && ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace( ' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		foreach ( $categories as $key => $category ) {
			// Remove any empty root parent categories so the table builds correctly.
			if ( ! $atts['show_empty'] && ( empty($category->count ) && empty( $category->children ) ) ) unset( $categories[ $key ] );

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) unset( $categories[ $key ] );
		}

		switch ( $atts['layout'] ) {

			case 'table':

				// Build the table grid.
				$table = array();
				$rows = ceil(count( $categories ) / $atts['columns'] );
				$keys = array_keys( $categories );

				for ( $row = 1; $row <= $rows; $row++ )
					for ( $col = 1; $col <= $atts['columns']; $col++ )
						$table[$row][$col] = array_shift($keys);

				$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
					$out .= '<tbody>';

					foreach ( $table as $row => $cols ) {

						$trClass = ( $trClass == 'alternate' ) ? '' : 'alternate';

						$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

						foreach ( $cols as $col => $key ) {

							// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
							if ( $key === NULL ) continue;

							$tdClass = array('cn-cat-td');
							if ( $row == 1 ) $tdClass[] = '-top';
							if ( $row == $rows ) $tdClass[] = '-bottom';
							if ( $col == 1 ) $tdClass[] = '-left';
							if ( $col == $atts['columns'] ) $tdClass[] = '-right';

							$out .= '<td class="' . implode( '', $tdClass ) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

								$out .= '<ul class="cn-cat-tree">';

									$out .= $this->categoryInputOption( $categories[ $key ], $level + 1, $atts['depth'], $selected, $atts);

								$out .= '</ul>';

							$out .= '</td>';
						}

						$out .= '</tr>';
					}

					$out .= '</tbody>';
				$out .= '</table>';

				break;

			case 'list':

				$out .= '<ul class="cn-cat-tree">';

				foreach ( $categories as $key => $category ) {

					// Limit the category tree to only the supplied root parent categories.
					if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

					// Call the recursive function to build the select options.
					$out .= $this->categoryInputOption( $categories[ $key ], $level + 1, $atts['depth'], $selected, $atts);
				}

				$out .= '</ul>';

				break;
		}


		if ( $atts['return']) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the list item.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string)
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $selected An array of the selected category IDs.
	 * @param array $atts
	 * @return string
	 */
	private function categoryInputOption( $category, $level, $depth, $selected, $atts ) {

		$out = '';

		$defaults = array(
			'type'       => 'radio',
			'show_empty' => TRUE,
			'show_count' => TRUE
		);

		$atts = wp_parse_args($atts, $defaults);

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty( $category->children ) ) {

			$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

			$out .= '<li class="cn-cat-parent">';

			$out .= sprintf( '<input type="%1$s" class="cn-radio" id="%2$s" name="cn-cat" value="%3$s" %4$s/>', $atts['type'], $category->slug, $category->term_id, checked( $selected, $category->term_id, FALSE ) );
			$out .= sprintf( '<label for="%1$s"> %2$s</label>', $category->slug, $category->name . $count );

			/*
			 * Only show the descendants based on the following criteria:
			 * 	- There are descendant categories.
			 * 	- The descendant depth is < than the current $level
			 *
			 * When descendant depth is set to 0, show all descendants.
			 * When descendant depth is set to < $level, call the recursive function.
			 */
			if ( ! empty( $category->children ) && ( $depth <= 0 ? -1 : $level ) < $depth ) {

				$out .= '<ul class="cn-cat-children">';

				foreach ( $category->children as $child ) {
					$out .= $this->categoryInputOption( $child, $level + 1, $depth, $selected, $atts );
				}

				$out .= '</ul>';
			}

			$out .= '</li>';
		}

		return $out;
	}

	/**
	 * The private function called by cnTemplate::category that outputs the category links in two formats:
	 *  - A table layout with one cell per root parent category containing all descendants in an unordered list.
	 *  - An unordered list.
	 *
	 * Accepted option for the $atts property are:
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 * 	depth (int) The number of levels deap to show categories. Setting to 0 will show all levels.
	 * 	parent_id (array) An array of root parent category IDs to limit the list to.
	 * 	layout (string) The layout to be used for rendering the categories. Valid options are: list || table
	 * 	columns (int) The number of columns in the table.
	 * 	return (bool) Whether or not to return or echo the result.
	 *
	 * @access private
	 * @version 1.0
	 * @since 0.7.3
	 * @uses get_query_var()
	 * @uses wp_parse_args()
	 * @param array $atts
	 * @return string
	 */
	private function categoryLink( $atts = NULL ) {
		global $connections;

		$categories = array();
		$level = 0;
		$out = '';
		$trClass = 'alternate';

		$categories = $connections->retrieve->categories();

		$defaults = array(
			'show_empty' => TRUE,
			'show_count' => TRUE,
			'depth'      => 0,
			'parent_id'  => array(),
			'layout'     => 'list',
			'columns'    => 3,
			'return'     => FALSE
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( ! empty( $atts['parent_id'] ) && ! is_array( $atts['parent_id'] ) ) {
			// Trim extra whitespace.
			$atts['parent_id'] = trim( str_replace(' ', '', $atts['parent_id'] ) );

			// Convert to array.
			$atts['parent_id'] = explode( ',', $atts['parent_id'] );
		}

		foreach ( $categories as $key => $category ) {
			// Remove any empty root parent categories so the table builds correctly.
			if ( ! $atts['show_empty'] && ( empty( $category->count ) && empty( $category->children ) ) ) unset( $categories[ $key ] );

			// Limit the category tree to only the supplied root parent categories.
			if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) unset( $categories[ $key ] );
		}

		switch ( $atts['layout'] ) {

			case 'table':

				// Build the table grid.
				$table = array();
				$rows = ceil(count( $categories ) / $atts['columns'] );
				$keys = array_keys( $categories );
				for ( $row = 1; $row <= $rows; $row++ )
					for ( $col = 1; $col <= $atts['columns']; $col++ )
						$table[ $row ][ $col ] = array_shift( $keys );

				$out .= '<table cellspacing="0" cellpadding="0" class="cn-cat-table">';
					$out .= '<tbody>';

					foreach ( $table as $row => $cols ) {
						$trClass = ( $trClass == 'alternate' ) ? '' : 'alternate';

						$out .= '<tr' . ( $trClass ? ' class="' . $trClass . '"' : '' ) . '>';

						foreach ( $cols as $col => $key ) {
							// When building the table grid, NULL will be the result of the array_shift when it runs out of $keys.
							if ( $key === NULL ) continue;

							$tdClass = array('cn-cat-td');
							if ( $row == 1 ) $tdClass[] = '-top';
							if ( $row == $rows ) $tdClass[] = '-bottom';
							if ( $col == 1 ) $tdClass[] = '-left';
							if ( $col == $atts['columns'] ) $tdClass[] = '-right';

							$out .= '<td class="' . implode( '', $tdClass) . '" style="width: ' . floor( 100 / $atts['columns'] ) . '%">';

								$out .= '<ul class="cn-cat-tree">';

									$out .= $this->categoryLinkDescendant( $categories[ $key ], $level + 1, $atts['depth'], array(), $atts );

								$out .= '</ul>';

							$out .= '</td>';
						}

						$out .= '</tr>';
					}

					$out .= '</tbody>';
				$out .= '</table>';

				break;

			case 'list':

				$out .= '<ul class="cn-cat-tree">';

				foreach ( $categories as $key => $category )
				{
					// Limit the category tree to only the supplied root parent categories.
					if ( ! empty( $atts['parent_id'] ) && ! in_array( $category->term_id, $atts['parent_id'] ) ) continue;

					// Call the recursive function to build the select options.
					$out .= $this->categoryLinkDescendant( $category, $level + 1, $atts['depth'], array(), $atts );
				}

				$out .= '</ul>';

				break;
		}

		if ( $atts['return'] ) return $out;
		echo $out;
	}

	/**
	 * The private recursive function to build the category link item.
	 *
	 * Accepted option for the $atts property are:
	 * 	type (string)
	 * 	show_empty (bool) Whether or not to display empty categories.
	 * 	show_count (bool) Whether or not to display the category count.
	 *
	 * @param object $category A category object.
	 * @param int $level The current category level.
	 * @param int $depth The depth limit.
	 * @param array $slug An array of the category slugs to be used to build the permalink.
	 * @param array $atts
	 * @return string
	 */
	private function categoryLinkDescendant ( $category, $level, $depth, $slug, $atts ) {
		global $wp_rewrite, $connections;

		$out = '';

		$defaults = array(
			'show_empty' => TRUE,
			'show_count' => TRUE
		);

		$atts = wp_parse_args($atts, $defaults);

		if ( $atts['show_empty'] || ! empty( $category->count ) || ! empty ( $category->children ) ) {

			$count = ( $atts['show_count'] ) ? ' (' . $category->count . ')' : '';

			/*
			 * Determine of pretty permalink is enabled.
			 * If it is, add the category slug to the array which will be imploded to be used to build the URL.
			 * If it is not, set the $slug to the category term ID.
			 */
			if ( $wp_rewrite->using_permalinks() ) {
				$slug[] = $category->slug;
			} else {
				$slug = array( $category->slug );
			}

			$out .= '<li class="cat-item cat-item-' . $category->term_id . ' cn-cat-parent">';

			// Create the permalink anchor.
			$out .= $connections->url->permalink( array(
				'type'   => 'category',
				'slug'   => implode( '/' , $slug ),
				'title'  => $category->name,
				'text'   => $category->name . $count,
				'return' => TRUE
				)
			);

			/*
			 * Only show the descendants based on the following criteria:
			 * 	- There are descendant categories.
			 * 	- The descendant depth is < than the current $level
			 *
			 * When descendant depth is set to 0, show all descendants.
			 * When descendant depth is set to < $level, call the recursive function.
			 */
			if ( ! empty( $category->children ) && ( $depth <= 0 ? -1 : $level ) < $depth ) {

				$out .= '<ul class="children cn-cat-children">';

				foreach ( $category->children as $child ) {
					$out .= $this->categoryLinkDescendant( $child, $level + 1, $depth, $slug, $atts );
				}

				$out .= '</ul>';
			}

			$out .= '</li>';
		}

		return $out;
	}
}
<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides the core support for the Template Customizer.
 *
 * @package     Connections
 * @subpackage  Template Customizer
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       8.4
 */
class cnTemplate_Customizer {

	/**
	 * @since 8.4
	 * @var string
	 */
	private $view;

	/**
	 * @since 8.4
	 * @var string
	 */
	private $slug = '';

	/**
	 * @since 8.4
	 * @var object
	 */
	private $template;

	/**
	 * @since 8.4
	 * @var array
	 */
	private $supports = array();

	/**
	 * @since 8.4
	 */
	public function __construct() {

		if ( ! isset( $_REQUEST['cn-template'] ) ) return;

		$this->setSlug();
		$this->setView();
		$this->hooks();
	}

	/**
	 * Add the actions and filter hooks.
	 *
	 * @access public
	 * @since  8.4
	 *
	 * @uses add_filter()
	 * @uses add_action()
	 * @uses remove_all_actions()
	 */
	public function hooks() {

		//add_action( 'wp_head', array( $this', 'remove_head_actions'), -1 );

		add_filter( 'admin_url', array( $this, 'admin_url'), 10, 3 );
		add_filter( 'cn_permalink', array( $this, 'permalink' ), 10, 2 );

		add_filter( 'cn_template_customizer_template', array( $this, 'getTemplate' ) );

		//add_action( 'customize_controls_enqueue_scripts', $this->customizer, 'enqueue_scripts' );
		//add_action( 'customize_preview_init', $this->customizer, 'enqueue_template_scripts', 99 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'themeCompatibility' ), 9999 );

		//add_action( 'wp_print_styles', array( $this', 'remove_all_styles'), 9999 );
		//add_action( 'wp_print_scripts', array( $this, 'remove_all_scripts'), 9999 );

		//add_action( 'template_include', array( $this, 'customizerPage' ) );
		add_action( 'customize_register', array( $this, 'registerControls') );
		add_action( 'customize_register', array( $this, 'registerSections') );
		add_action( 'customize_register', array( $this, 'registerTemplateControls') );
		add_filter( 'customize_section_active', array( $this, 'removeSections' ), 10, 2 );
		add_filter( 'customize_control_active', array( $this, 'setActiveControls' ), 10, 2 );

		remove_all_actions( 'cn_list_actions' );
		remove_all_actions( 'cn_entry_actions' );

		add_action( 'cn_entry_actions', array( $this, 'singleActions' ), 10, 2 );

		add_action( 'cn_template_customizer_register-card', array( $this, 'search' ), 10, 2 );
		add_action( 'cn_template_customizer_register-card', array( $this, 'pagination' ), 10, 2 );
		add_action( 'cn_template_customizer_register-card', array( $this, 'categorySelect' ), 10, 2 );

		add_action( 'cn_action_list_before', array( $this, 'instructions' ) );
		add_action( 'cn_action_list_before', array( $this, 'singleView' ) );
		add_action( 'cn_action_list_before', array( $this, 'categoryMessage' ) );

		//add_action( 'get_footer', array( $this, 'remove_footer_actions'), 9999 );
	}

	/**
	 * Get the template slug that is being customized from $_REQUEST.
	 *
	 * Sets @see cnTemplate_Customizer::$slug.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   sanitize_title_with_dashes()
	 */
	private function setSlug() {

		if ( isset( $_REQUEST['cn-template'] ) && ! empty( $_REQUEST['cn-template'] ) ) {

			$this->slug = sanitize_title_with_dashes( $_REQUEST['cn-template'] );
		}
	}

	/**
	 * Get the current view of the template that is being customized from $_REQUEST.
	 *
	 * Sets @see cnTemplate_Customizer::$view.
	 *
	 * @access private
	 * @since  8.4
	 */
	private function setView() {

		$this->view = isset( $_REQUEST['view'] ) && 'single' == $_REQUEST['view'] ? 'single' : 'card';
	}

	/**
	 * Callback for the `cn_template_customizer_template` filter which sets the template used on the directory home
	 * page to the template being customized.
	 *
	 * @see cnShortcode_Connections::shortcode()
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public function getTemplate( $atts ) {

		$atts['template'] = $this->slug;

		return $atts;
	}

	/**
	 * Gets the template's config object.
	 *
	 * @see cnTemplateFactory::register().
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplateFactory::$templates
	 * @uses   cnTemplate_Customizer::setupTemplateFeatures()
	 */
	private function getTemplateConfig() {

		if ( $this->slug ) {

			// $t == the template type $s == template slug.
			foreach ( cnTemplateFactory::$templates as $t => $s ) {

				if ( isset( cnTemplateFactory::$templates->{ $t }->{ $this->slug } ) ) {

					$this->template = cnTemplateFactory::$templates->{ $t }->{ $this->slug };

					break;
				}
			}

		}

		if ( $this->template->supports ) $this->setupTemplateFeatures( $this->template->supports );
	}

	/**
	 * Sets up an array that defines which features the template supports.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param mixed array|string $features
	 */
	private function setupTemplateFeatures( $features ) {

		if ( is_array( $features ) ) {

			foreach ( $features as $feature => $options ) {

				if ( is_array( $options ) ) {

					$this->supports[ $feature ] = $options;

				} else {

					$this->supports[ $options ] = TRUE;
				}

			}

		} else {

			$this->supports[ $features ] = TRUE;
		}
	}

	/**
	 * Determines whether or not a feature is supported.
	 *
	 * @access public
	 * @since  8.4
	 *
	 * @param string $feature
	 *
	 * @return bool
	 */
	public function supports( $feature ) {

		return array_key_exists( $feature, $this->supports );
	}

	/**
	 * Gets the options of the features the template supports.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::supports()
	 *
	 * @param string $feature
	 *
	 * @return array|bool
	 */
	private function getSupportsOptions( $feature ) {

		if ( $this->supports( $feature ) ) {

			return $this->supports[ $feature ];
		}

		return FALSE;
	}

	/**
	 * Sets up the Template Customizer features that the template supports.
	 *
	 * @access public
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::getTemplateConfig()
	 * @uses   cnTemplate_Customizer::supports()
	 * @uses   cnTemplate_Customizer::getSupportsOptions()
	 * @uses   cnTemplate_Customizer::registerFeatures()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function registerTemplateControls( $wp_customize ) {

		$this->getTemplateConfig();

		if ( $this->supports( 'customizer' ) ) {

			$features = $this->getSupportsOptions( 'customizer' );

			if ( FALSE !== $features ) {

				$this->registerFeatures( $features, $wp_customize );
			}
		}
	}

	/**
	 * Register the Customizer controls for the features the template supports.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   apply_filters()
	 * @uses   do_action()
	 * @uses   cnTemplate_Customizer::registerDisplayControls()
	 * @uses   cnTemplate_Customizer::registerImageControls()
	 * @uses   cnTemplate_Customizer::registerStringControls()
	 * @uses   cnTemplate_Customizer::registerAdvancedControls()
	 *
	 * @param array $features
	 * @param WP_Customize_Manager $wp_customize
	 */
	private function registerFeatures( $features, $wp_customize ) {

		$views = array(
			'card',
			'single',
		);

		/**
		 * Modify supported core template views which can be customized.
		 *
		 * @since 8.4
		 *
		 * @param array $views
		 */
		$views = apply_filters( 'cn_template_views', $views );

		foreach ( $views as $view ) {

			foreach ( $features as $feature => $options ) {

				if ( isset( $options[ $view ]['display'] ) ) {

					$this->registerDisplayControls( $view, $options[ $view ]['display'] );
				}

				if ( isset( $options[ $view ]['image'] ) ) {

					$this->registerImageControls( $view, $options[ $view ]['image'] );
				}

				if ( isset( $options[ $view ]['strings'] ) ) {

					$this->registerStringControls( $view, $options[ $view ]['strings'] );
				}

				if ( isset( $options[ $view ]['advanced'] ) ) {

					$this->registerAdvancedControls( $view , $options[ $view ]['advanced'] );
				}

				/**
				 * Register Customizer controls based on supported template feature.
				 *
				 * The variable part of the action hook name is the supported template feature.
				 *
				 * @since 8.4
				 *
				 * @param WP_Customize_Manager $wp_customize
				 * @param array                $options      The options of the supported template feature.
				 * @param object               $template     The registered template properties.
				 * @param string               $view         The current template view being customized.
				 */
				do_action( "cn_template_customizer_register-$feature", $wp_customize, $options[ $view ], $this->template, $view );
			}

			/**
			 * General action hook for registering Customizer controls.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param object               $template     The registered template properties.
			 * @param string               $view         The current template view being customized.
			 */
			do_action( "cn_template_customizer_register", $wp_customize, $this->template, $view );

			/**
			 * Register Customizer controls based on the current template view being customized.
			 *
			 * The variable part of the action hook name is the current template view being customized.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param array                $options      The options of the supported template feature.
			 * @param object               $template     The registered template properties.
			 * @param string               $view         The current template view being customized.
			 */
			do_action( "cn_template_customizer_register-$view", $wp_customize, $this->template );
		}
	}

	///**
	// * Display template being customized on a page template.
	// *
	// * @access private
	// * @since  8.4
	// *
	// * @param string $template
	// *
	// * @return string
	// */
	//public function customizerPage( $template ) {
	//
	//	if ( is_customize_preview() && isset( $_REQUEST['cn-customize-template'] ) ) {
	//
	//		return apply_filters( 'cn_template_customizer_template_path', CN_PATH . 'includes/template/inc.customizer-template.php' );
	//	}
	//
	//	return $template;
	//}

	/**
	 * Register a customize control type.
	 *
	 * Registered types are eligible to be rendered via JS and created dynamically.
	 *
	 * @see WP_Customize_Manager::register_control_type()
	 *
	 * @access private
	 * @since  8.6.7
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function registerControls( $wp_customize ) {

		$wp_customize->register_control_type( 'cnCustomizer_Control_Checkbox_Group' );
		$wp_customize->register_control_type( 'cnCustomizer_Control_Slider' );
	}

	/**
	 * Register the supported core Template Customizer sections.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   do_action()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function registerSections( $wp_customize ) {

		$wp_customize->add_panel(
			'cn_template',
			array(
				'priority'    => 10,
				'title'       => __( 'Connections Template', 'connections' ),
				'description' => __( 'The Template Customizer allows you to preview and save changes to how the template will appear on your site.', 'connections' ),
			)
		);

		/**
		 * Register Customizer sections before the core Template Customizer sections.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		do_action( 'cn_template_customizer_sections_before', $wp_customize );

		/**
		 * Register Customizer sections before the core Template Customizer sections.
		 *
		 * The variable part of the action hook name is the current template's slug.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @param string               $slug         The template that is being customized slug.
		 */
		do_action( 'cn_template_customizer_sections_before-' . $this->slug, $wp_customize );

		$wp_customize->add_section(
			'cn_template_customizer_section_global_display',
			array(
				'title'       => __( 'Global Options', 'connections' ),
				'description' => __( 'These options are global and will affect all templates.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 0,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_search',
			array(
				'title'       => __( 'Search', 'connections' ),
				//'description' => __( '', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 5,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_category_select',
			array(
				'title'       => __( 'Category Select', 'connections' ),
				'description' => esc_html__( 'Configure the template\'s category select features.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 10,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_pagination',
			array(
				'title'       => __( 'Pagination', 'connections' ),
				'description' => esc_html__( 'Configure the template\'s pagination features.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 20,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_display',
			array(
				'title'       => __( 'Display', 'connections' ),
				'description' => __( 'These options will only affect the template being customized.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 30,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_style',
			array(
				'title'       => __( 'Style', 'connections' ),
				'description' => __( 'Change the visual appearance of the template.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 40,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_image',
			array(
				'title'       => __( 'Image', 'connections' ),
				'description' => __( 'These options will override the settings found on the Connections : Settings admin page.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 50,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_strings',
			array(
				'title'       => __( 'Strings', 'connections' ),
				'description' => __( 'Customize the template\'s strings.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 60,
				'capability'  => 'edit_theme_options',
			)
		);

		$wp_customize->add_section(
			'cn_template_customizer_section_advanced',
			array(
				'title'       => __( 'Advanced', 'connections' ),
				'description' => __( 'Customize various advanced template features. Adjusting any of the following settings will override the global defaults in the template.', 'connections' ),
				'panel'       => 'cn_template',
				'priority'    => 70,
				'capability'  => 'edit_theme_options',
			)
		);

		/**
		 * Register Customizer sections after the core Template Customizer sections.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		do_action( 'cn_template_customizer_sections_after-' . $this->slug, $wp_customize );

		/**
		 * Register Customizer sections after the core Template Customizer sections.
		 *
		 * The variable part of the action hook name is the current template's slug.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @param string               $slug         The template that is being customized slug.
		 */
		do_action( 'cn_template_customizer_sections_after', $wp_customize );

		self::registerGlobalDisplayControl( $wp_customize );
	}

	/**
	 * Register the core Template Customizer settings and controls.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   do_action()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	private function registerGlobalDisplayControl( $wp_customize ) {

		/**
		 * Register Customizer settings controls before the core Template Customizer Global section.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		do_action( 'cn_template_customizer_section_global_display_before', $wp_customize );

		/**
		 * Register Customizer settings controls before the core Template Customizer Global section.
		 *
		 * The variable part of the action hook name is the current template's slug.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @param string               $slug         The template that is being customized slug.
		 */
		do_action( 'cn_template_customizer_section_global_display_before-' . $this->slug , $wp_customize );

		$wp_customize->add_setting(
			'connections_display_results[index]',
			array(
				'type'                 => 'option',
				'default'              => 0,
				'transport'            => 'refresh',
				'capability'           => 'edit_theme_options',
				//'sanitize_callback'    => 'sanitize_text_field',
				//'sanitize_js_callback' => '',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'index',
				array(
					'label'       => __( 'Character Index', 'connections' ),
					'type'        => 'checkbox',
					'section'     => 'cn_template_customizer_section_global_display',
					'settings'    => 'connections_display_results[index]',
					'description' => __( 'Show the character index at the top of the results list.', 'connections' )
				)
			)
		);

		$wp_customize->add_setting(
			'connections_display_results[index_repeat]',
			array(
				'type'                 => 'option',
				'default'              => 0,
				'transport'            => 'refresh',
				'capability'           => 'edit_theme_options',
				//'sanitize_callback'    => 'sanitize_text_field',
				//'sanitize_js_callback' => '',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'index_repeat',
				array(
					'label'       => __( 'Character Index Repeat', 'connections' ),
					'type'        => 'checkbox',
					'section'     => 'cn_template_customizer_section_global_display',
					'settings'    => 'connections_display_results[index_repeat]',
					'description' => __( 'Repeat the character index at the beginning of each character group.', 'connections' )
				)
			)
		);

		$wp_customize->add_setting(
			'connections_display_results[show_current_character]',
			array(
				'type'                 => 'option',
				'default'              => 0,
				'transport'            => 'refresh',
				'capability'           => 'edit_theme_options',
				//'sanitize_callback'    => 'sanitize_text_field',
				//'sanitize_js_callback' => '',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				'show_current_character',
				array(
					'label'       => __( 'Current Character', 'connections' ),
					'type'        => 'checkbox',
					'section'     => 'cn_template_customizer_section_global_display',
					'settings'    => 'connections_display_results[show_current_character]',
					'description' => __( 'Show the current character at the beginning of each character group.', 'connections' )
				)
			)
		);

		/**
		 * Register Customizer settings controls before the core Template Customizer Global section.
		 *
		 * The variable part of the action hook name is the current template's slug.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @param string               $slug         The template that is being customized slug.
		 */
		do_action( 'cn_template_customizer_section_global_display_after-' . $this->slug, $wp_customize );

		/**
		 * Register Customizer settings controls after the core Template Customizer Global section.
		 *
		 * @since 8.4
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		do_action( 'cn_template_customizer_section_global_display_after', $wp_customize );
	}

	/**
	 * Callback for the action `cn_template_customizer_register-card` hook which registers the settings controls for the
	 * category select drop down if the template supports it.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   do_action()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 * @param object               $template
	 */
	public function categorySelect( $wp_customize, $template ) {

		if ( $this->supports( 'category-select' ) ) {

			/**
			 * Register Customizer settings controls before the core Template Customizer Category Select section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_category_select_before', $wp_customize );

			/**
			 * Register Customizer settings controls before the core Template Customizer Category Select section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_category_select_before-' . $template->slug , $wp_customize, $template );

			$base = 'connections_template';
			$slug = $template->slug;
			//$id   = 'cn_card_border_width';

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][category_select]",
				array(
					'type'                 => 'option',
					'default'              => TRUE,
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_category_select",
					array(
						'label'       => __( 'Enable Category Filter', 'connections' ),
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_category_select',
						'settings'    => "{$base}_{$slug}[card][category_select]",
						'description' => __( 'Whether or not to enable the category select drop down.', 'connections' )
					)
				)
			);

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][show_empty_categories]",
				array(
					'type'                 => 'option',
					'default'              => TRUE,
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_show_empty_categories",
					array(
						'label'       => __( 'Show Empty', 'connections' ),
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_category_select',
						'settings'    => "{$base}_{$slug}[card][show_empty_categories]",
						'description' => __( 'Whether or not to display categories which have not entries assigned.', 'connections' )
					)
				)
			);

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][show_category_count]",
				array(
					'type'                 => 'option',
					'default'              => FALSE,
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_show_category_count",
					array(
						'label'       => __( 'Show Count', 'connections' ),
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_category_select',
						'settings'    => "{$base}_{$slug}[card][show_category_count]",
						'description' => __( 'Whether or not to display the number of entries assigned to the categories.', 'connections' )
					)
				)
			);

			/**
			 * Register Customizer settings controls after the core Template Customizer Category Select section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_category_select_after-' . $template->slug, $wp_customize, $template );

			/**
			 * Register Customizer settings controls after the core Template Customizer Category Select section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_category_select_after', $wp_customize );
		}
	}

	/**
	 * Callback for the action `cn_template_customizer_register-card` hook which registers the settings controls for the
	 * keyword search input if the template supports it.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   do_action()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 * @param object               $template
	 */
	public function search( $wp_customize, $template ) {

		if ( $this->supports( 'search' ) ) {

			/**
			 * Register Customizer settings controls before the core Template Customizer Search section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_search_before', $wp_customize );

			/**
			 * Register Customizer settings controls before the core Template Customizer Search section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_search_before-' . $template->slug , $wp_customize, $template );

			$base = 'connections_template';
			$slug = $template->slug;
			//$id   = 'cn_card_border_width';

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][search]",
				array(
					'type'                 => 'option',
					'default'              => TRUE,
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_search",
					array(
						'label'       => __( 'Enable', 'connections' ),
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_search',
						'settings'    => "{$base}_{$slug}[card][search]",
						'description' => __( 'Whether or not to display the search control', 'connections' )
					)
				)
			);

			/**
			 * Register Customizer settings controls after the core Template Customizer Search section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_search_after-' . $template->slug, $wp_customize, $template );

			/**
			 * Register Customizer settings controls after the core Template Customizer Search section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_search_after', $wp_customize );
		}
	}

	/**
	 * Callback for the action `cn_template_customizer_register-card` hook which registers the settings controls for the
	 * pagination control if the template supports pagination.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   do_action()
	 *
	 * @param WP_Customize_Manager $wp_customize
	 * @param object               $template
	 */
	public function pagination( $wp_customize, $template ) {

		if ( $this->supports( 'pagination' ) ) {

			/**
			 * Register Customizer settings controls before the core Template Customizer Pagination section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_pagination_before', $wp_customize );

			/**
			 * Register Customizer settings controls before the core Template Customizer Pagination section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_pagination_before-' . $template->slug , $wp_customize, $template );

			$base = 'connections_template';
			$slug = $template->slug;
			//$id   = 'cn_card_border_width';

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][pagination]",
				array(
					'type'                 => 'option',
					'default'              => TRUE,
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_pagination",
					array(
						'label'       => __( 'Enable Pagination Support', 'connections' ),
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_pagination',
						'settings'    => "{$base}_{$slug}[card][pagination]",
						'description' => __( 'Whether or not to enable pagination support.', 'connections' )
					)
				)
			);

			$wp_customize->add_setting(
				"{$base}_{$slug}[card][pagination_limit]",
				array(
					'type'       => 'option',
					'default'    => 20,
					'transport'  => 'refresh',
					'capability' => 'edit_theme_options',
					'sanitize_callback'    => 'absint',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					"cn_{$slug}_card_pagination_limit",
					array(
						'label'       => __( 'Page Limit', 'connections' ),
						'type'        => 'number',
						'section'     => 'cn_template_customizer_section_pagination',
						'settings'    => "{$base}_{$slug}[card][pagination_limit]",
						'description' => __( 'The maximum number of entries to display per page.', 'connections' ),
					)
				)
			);

			/**
			 * Register Customizer settings controls after the core Template Customizer Pagination section.
			 *
			 * The variable part of the action hook name is the current template's slug.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 * @param string               $slug         The template that is being customized slug.
			 */
			do_action( 'cn_template_customizer_section_pagination_after-' . $template->slug, $wp_customize, $template );

			/**
			 * Register Customizer settings controls after the core Template Customizer Pagination section.
			 *
			 * @since 8.4
			 *
			 * @param WP_Customize_Manager $wp_customize
			 */
			do_action( 'cn_template_customizer_section_pagination_after', $wp_customize );
		}
	}

	/**
	 * Register the settings option and Customizer control for each of the display options the template supports.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::getControlString()
	 *
	 * @param string $view
	 * @param array  $features
	 */
	private function registerDisplayControls( $view, $features ) {

		/** @var WP_Customize_Manager $wp_customize */
		global $wp_customize;

		$base = 'connections_template';
		$slug = $this->template->slug;

		foreach ( $features as $feature ) {

			$id     = "cn_{$slug}_{$view}_{$feature}";
			$option = "{$base}_{$slug}[{$view}][show_{$feature}]";
			$string = $this->getControlString( $feature );

			$wp_customize->add_setting(
				$option,
				array(
					'type'       => 'option',
					'default'    => TRUE,
					'transport'  => 'refresh',
					'capability' => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					$id,
					array(
						'label'       => $string['label'],
						'type'        => 'checkbox',
						'section'     => 'cn_template_customizer_section_display',
						'settings'    => $option,
						'description' => $string['desc'],
					)
				)
			);
		}
	}

	/**
	 * Register the Customizer controls based on the template view and which features the template supports per view.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param string $view
	 * @param array  $features
	 */
	private function registerImageControls( $view, $features ) {

		/** @var WP_Customize_Manager $wp_customize */
		global $wp_customize;

		$base = 'connections_template';
		$slug = $this->template->slug;

		foreach ( $features as $feature ) {

			$id     = "cn_{$slug}_{$view}_{$feature}";
			$option = "{$base}_{$slug}[{$view}][image_{$feature}]";

			switch ( $feature ) {

				case 'type':

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => 'photo',
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							//'sanitize_callback'    => 'sanitize_text_field',
							//'sanitize_js_callback' => '',
						)
					);

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id,
							array(
								'label'       => __( 'Type', 'connections' ),
								'type'        => 'select',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'Select image type to display.', 'connections' ),
								'choices'     => apply_filters(
									'cn_customizer_image_options',
									array(
										'none'  => __( 'None', 'connections' ),
										'photo' => __( 'Photo', 'connections' ),
										'logo'  => __( 'Logo', 'connections' ),
									)
								),
							)
						)
					);

					break;

				case 'width':

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => '',
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							'sanitize_callback'    => 'absint',
							//'sanitize_js_callback' => '',
						)
					);

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id,
							array(
								'label'       => __( 'Width', 'connections' ),
								'type'        => 'number',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'Set the image width in pixels.', 'connections' ),
							)
						)
					);

					break;

				case 'height':

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => '',
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							'sanitize_callback'    => 'absint',
							//'sanitize_js_callback' => '',
						)
					);

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id,
							array(
								'label'       => __( 'Height', 'connections' ),
								'type'        => 'number',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'Set the image height in pixels.', 'connections' ),
							)
						)
					);

					break;

				case 'crop_mode':

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => '1',
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							//'sanitize_callback'    => 'sanitize_text_field',
							//'sanitize_js_callback' => '',
						)
					);

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id,
							array(
								'label'       => __( 'Crop Mode', 'connections' ),
								'type'        => 'radio',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'Select the image crop mode.', 'connections' ),
								'choices'     => array(
									'1' => __(
										'Crop and resize proportionally to best fit the specified dimensions, maintaining the aspect ratio.',
										'connections'
									),
									'2' => __(
										'Resize proportionally to fit entire image into the specified dimensions and add margins if required.',
										'connections'
									),
									'3'  => __(
										'Resize proportionally adjusting the size of scaled image so there are no margins added.',
										'connections'
									),
									'none' => __( 'Resize to fit the specified dimensions (no cropping).', 'connections' )
								),
							)
						)
					);

					break;

				case 'fallback':

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => TRUE,
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							//'sanitize_callback'    => 'sanitize_text_field',
							//'sanitize_js_callback' => '',
						)
					);

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id,
							array(
								'label'       => __( 'Image Placeholder', 'connections' ),
								'type'        => 'checkbox',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'Whether or not an image placeholder is displayed if the selected image type does not exist.', 'connections' ),
							)
						)
					);

					$option = "{$base}_{$slug}[{$view}][image_{$feature}_string]";

					$wp_customize->add_setting(
						$option,
						array(
							'type'                 => 'option',
							'default'              => __( 'No Image Available', 'connections' ),
							'transport'            => 'refresh',
							'capability'           => 'edit_theme_options',
							//'sanitize_callback'    => 'sanitize_text_field',
							//'sanitize_js_callback' => '',
						)
					);

					//$option = "{$base}_{$slug}[{$view}][image_{$feature}_string]";

					$wp_customize->add_control(
						new WP_Customize_Control(
							$wp_customize,
							$id . '_string',
							array(
								'label'       => __( 'Image Placeholder Text', 'connections' ),
								'type'        => 'text',
								'section'     => 'cn_template_customizer_section_image',
								'settings'    => $option,
								'description' => __( 'The text to display if no image is available.', 'connections' ),
							)
						)
					);

					break;
			}

		}
	}

	/**
	 * Register the Customizer controls based on the template view and which features the template supports per view.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param string $view
	 * @param array  $strings
	 */
	private function registerStringControls( $view, $strings ) {

		/** @var WP_Customize_Manager $wp_customize */
		global $wp_customize;

		$base = 'connections_template';
		$slug = $this->template->slug;

		foreach ( $strings as $key => $args ) {

			$id     = "cn_{$slug}_{$view}_{$key}";
			$option = "{$base}_{$slug}[{$view}][str_{$key}]";

			$wp_customize->add_setting(
				$option,
				array(
					'type'                 => 'option',
					'default'              => $args['default'],
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					$id,
					array(
						'label'       => $args['label'],
						'type'        => 'text',
						'section'     => 'cn_template_customizer_section_strings',
						'settings'    => $option,
						'description' => isset( $args['desc'] ) && ! empty( $args['desc'] ) ? $args['desc'] : '',
					)
				)
			);

		}
	}

	/**
	 * Register the Customizer controls based on the template view and which features the template supports per view.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::getControlString()
	 *
	 * @param string $view
	 * @param array  $features
	 */
	private function registerAdvancedControls( $view, $features ) {

		/** @var WP_Customize_Manager $wp_customize */
		global $wp_customize;

		$base = 'connections_template';
		$slug = $this->template->slug;

		foreach ( $features as $feature ) {

			$id     = "cn_{$slug}_{$view}_{$feature}";
			$option = "{$base}_{$slug}[{$view}][{$feature}]";
			$string = $this->getControlString( $feature );

			$wp_customize->add_setting(
				$option,
				array(
					'type'                 => 'option',
					'default'              => '',
					'transport'            => 'refresh',
					'capability'           => 'edit_theme_options',
					//'sanitize_callback'    => 'sanitize_text_field',
					//'sanitize_js_callback' => '',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Control(
					$wp_customize,
					$id,
					array(
						'label'       => $string['label'],
						'type'        => 'text',
						'section'     => 'cn_template_customizer_section_advanced',
						'settings'    => $option,
						'description' => $string['desc'],
					)
				)
			);
		}
	}

	/**
	 * Returns a core template Customizer control strings (label and description).
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::controlStrings()
	 *
	 * @param string $feature
	 *
	 * @return array
	 */
	private function getControlString( $feature ) {

		$strings = $this->controlStrings();

		return $strings[ $feature ];
	}

	/**
	 * The core template Customizer control string.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   Connections_Directory()
	 * @uses   apply_filters()
	 *
	 * @return array
	 */
	private function controlStrings() {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		$addressTypes = array_keys( $instance->options->getDefaultAddressValues() );
		$phoneTypes   = array_keys( $instance->options->getDefaultPhoneNumberValues() );
		$emailTypes   = array_keys( $instance->options->getDefaultEmailValues() );
		$dateTypes    = array_keys( $instance->options->getDateOptions() );
		$linkTypes    = array_keys( $instance->options->getDefaultLinkValues() );

		$strings = array(
			'title' => array(
				'label' => __( 'Show the entry title.', 'connections' ),
				'desc'  => __( 'Whether or not to display the Title field.', 'connections' ),
			),
			'org' => array(
				'label' => __( 'Show the entry organization.', 'connections' ),
				'desc'  => __( 'Whether or not to display the Organization field.', 'connections' ),
			),
			'dept' => array(
				'label' => __( 'Show the entry department.', 'connections' ),
				'desc'  => __( 'Whether or not to display the Department field.', 'connections' ),
			),
			'contact_name' => array(
				'label' => __( 'Show the entry contact name.', 'connections' ),
				'desc'  => __( 'Whether or not to display the Contact First and Last Name fields.', 'connections' ),
			),
			'family' => array(
				'label' => __( 'Show the entry family members.', 'connections' ),
				'desc'  => __( 'Whether or not to display the family members.', 'connections' ),
			),
			'addresses' => array(
				'label' => __( 'Show the addresses.', 'connections' ),
				'desc'  => __( 'Whether or not to display the addresses.', 'connections' ),
			),
			'phone_numbers' => array(
				'label' => __( 'Show the phone numbers.', 'connections' ),
				'desc'  => __( 'Whether or not to display the phone numbers.', 'connections' ),
			),
			'email' => array(
				'label' => __( 'Show the email addresses.', 'connections' ),
				'desc'  => __( 'Whether or not to display the email addresses.', 'connections' ),
			),
			'im' => array(
				'label' => __( 'Show the IM addresses.', 'connections' ),
				'desc'  => __( 'Whether or not to display the IM addresses.', 'connections' ),
			),
			'social_media' => array(
				'label' => __( 'Show the social networks.', 'connections' ),
				'desc'  => __( 'Whether or not to display the social networks.', 'connections' ),
			),
			'links' => array(
				'label' => __( 'Show the links.', 'connections' ),
				'desc'  => __( 'Whether or not to display the links.', 'connections' ),
			),
			'dates' => array(
				'label' => __( 'Show the dates.', 'connections' ),
				'desc'  => __( 'Whether or not to display the dates.', 'connections' ),
			),
			'bio' => array(
				'label' => __( 'Show the bio.', 'connections' ),
				'desc'  => __( 'Whether or not to display the bio field.', 'connections' ),
			),
			'notes' => array(
				'label' => __( 'Show the notes.', 'connections' ),
				'desc'  => __( 'Whether or not to display the notes field.', 'connections' ),
			),
			'categories' => array(
				'label' => __( 'Show the categories assigned to the entry.', 'connections' ),
				'desc'  => __( 'Whether or not to display the categories.', 'connections' ),
			),
			'last_updated' => array(
				'label' => __( 'Show the last updated message.', 'connections' ),
				'desc'  => __( 'Whether or not to display the last updated message.', 'connections' ),
			),
			'return_to_top' => array(
				'label' => __( 'Show display to top.', 'connections' ),
				'desc'  => __( 'Whether or not to display the return to top arrow.', 'connections' ),
			),
			'name_format' => array(
				'label' => __( 'Name Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%prefix% %first% %middle% %last% %suffix%</code>' ), 'connections' ),
			),
			'contact_name_format' => array(
				'label' => __( 'Contact Name Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label%%separator% %first% %last%</code>' ), 'connections' ),
			),
			'address_format' => array(
				'label' => __( 'Address Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label% %line1% %line2% %line3% %city% %state%  %zipcode% %country%</code>' ), 'connections' ),
			),
			'email_format' => array(
				'label' => __( 'Email Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label%%separator% %address%</code>' ), 'connections' ),
			),
			'phone_format' => array(
				'label' => __( 'Phone Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label%%separator% %number%</code>' ), 'connections' ),
			),
			'link_format' => array(
				'label' => __( 'Link Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label%%separator% %title%</code>' ), 'connections' ),
			),
			'date_format' => array(
				'label' => __( 'Date Format', 'connections' ),
				'desc'  => __( sprintf( 'Default: %s', '<code>%label%%separator% %date%</code>' ), 'connections' ),
			),
			'address_types' => array(
				'label' => __( 'Display Address Types', 'connections' ),
				'desc'  => __( sprintf( 'Valid: %s', '<code>' . implode( '</code>, <code>', $addressTypes ) ) . '</code>', 'connections' ),
			),
			'phone_types' => array(
				'label' => __( 'Display Phone Types', 'connections' ),
				'desc'  => __( sprintf( 'Valid: %s', '<code>' . implode( '</code>, <code>', $phoneTypes ) ) . '</code>', 'connections' ),
			),
			'email_types' => array(
				'label' => __( 'Display Email Types', 'connections' ),
				'desc'  => __( sprintf( 'Valid: %s', '<code>' . implode( '</code>, <code>', $emailTypes ) ) . '</code>', 'connections' ),
			),
			'date_types' => array(
				'label' => __( 'Display Date Types', 'connections' ),
				'desc'  => __( sprintf( 'Valid: %s', '<code>' . implode( '</code>, <code>', $dateTypes ) ) . '</code>', 'connections' ),
			),
			'link_types' => array(
				'label' => __( 'Display Link Types', 'connections' ),
				'desc'  => __( sprintf( 'Valid: %s', '<code>' . implode( '</code>, <code>', $linkTypes ) ) . '</code>', 'connections' ),
			),
		);

		/**
		 * An associative array of core strings used by the Template Customizer.
		 *
		 * The array key is the string ID. The value is an associative array with two keys.
		 * The `label` key is the Customizer control label.
		 * The `desc` key is the Customizer control description.
		 *
		 * @since 8.4
		 *
		 * @param array $strings
		 */
		return apply_filters( 'cn_template_customizer_strings', $strings );
	}

	/**
	 * Filter the admin URL.
	 *
	 * Pass the template slug via the admin ajax url so the template specific Customizer panels, sections and settings
	 * are added via the @see do_action() calls in this class.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @param string   $url     The complete admin area URL including scheme and path.
	 * @param string   $path    Path relative to the admin area URL. Blank string if no path is specified.
	 * @param int|null $blog_id Blog ID, or null for the current blog.
	 *
	 * @return string
	 */
	public function admin_url( $url, $path, $blog_id ) {

		if ( isset( $_REQUEST['cn-template'] ) && ! empty( $_REQUEST['cn-template'] ) ) {

			$url = esc_url( $url . '?cn-template=' . $_REQUEST['cn-template'] );
		}

		return $url;
	}

	/**
	 * To ensure the Customizer continues to load the template specific panels, sections and settings the
	 * permalink needs to have the template query var added.
	 *
	 * Additionally, the single/detail view permalinks need to have the `single` query value added so the single/detail
	 * view specific setting controls are set to active.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   is_customize_preview()
	 * @uses   add_query_arg()
	 * @uses   esc_url()
	 *
	 * @param string $permalink
	 * @param array  $atts
	 *
	 * @return string
	 */
	public function permalink( $permalink, $atts ) {

		if ( is_customize_preview() ) {

			$permalink = add_query_arg(
				array(
					'cn-customize-template' => 'true',
					'cn-template'           => $this->slug,
				),
				$permalink
			);

			if ( isset( $atts['type'] ) && 'name' == $atts['type'] ) {

				$permalink = add_query_arg(
					array(
						'view' => 'single',
					),
					$permalink
				);
			}

		}

		return esc_url( $permalink );
	}

	/**
	 * Determine if the card or single Customizer sections are active.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   apply_filters()
	 *
	 * @param bool                 $active
	 * @param WP_Customize_Section $section
	 *
	 * @return bool
	 */
	public function removeSections( $active, $section ) {

		$exemptions = array(
			//'themes',
			'cn_template_customizer_section_category_select',
			'cn_template_customizer_section_search',
			'cn_template_customizer_section_pagination',
			'cn_template_customizer_section_display',
			'cn_template_customizer_section_image',
			'cn_template_customizer_section_strings',
			'cn_template_customizer_section_style',
			'cn_template_customizer_section_advanced',
		);

		/**
		 * The global section should not be displayed while viewing a single entry.
		 */
		if ( 'single' !== $this->view ) {

			$exemptions[] = 'cn_template_customizer_section_global_display';
		}

		/**
		 * Add other Customizer sections to the exemptions list so they will not be removed.
		 *
		 * @since 8.4
		 *
		 * @param array $exemptions
		 */
		$exemptions = apply_filters( 'cn_template_customizer_sections', $exemptions );

		if ( isset( $_REQUEST['cn-customize-template'] ) ) {

			if ( ! in_array( $section->id, $exemptions ) ) {

				$active = FALSE;
			}
		}

		return $active;
	}

	/**
	 * Determine if the card or single Customizer controls are active.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnString::startsWith()
	 *
	 * @param bool                 $active
	 * @param WP_Customize_Control $control
	 *
	 * @return bool
	 */
	public function setActiveControls( $active, $control ) {

		//$exemptions = apply_filters(
		//	'cn_template_customizer_sections',
		//	array(
		//		//'cn_template_customizer_section_strings',
		//	)
		//);

		$exemptions = array();

		if ( cnString::startsWith( 'cn_', $control->id ) ) {

			if ( cnString::startsWith( "cn_{$this->slug}_{$this->view}", $control->id ) ||
			     in_array( $control->section, $exemptions ) ) {

				$active = TRUE;

			} else {

				$active = FALSE;
			}

		}

		return $active;
	}

	//public function remove_head_actions() {
	//
	//	if ( ! isset( $_REQUEST['cn-customize-template'] ) ) return;
	//
	//	remove_all_actions( 'wp_head' );
	//}

	/**
	 * Remove all scripts except those that are required for the Customizer and the template to function.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   apply_filters()
	 * @uses   wp_dequeue_script()
	 */
	public function remove_all_scripts() {
		global $wp_scripts;

		if ( ! isset( $_REQUEST['cn-customize-template'] ) ) return;

		$exceptions = array(
			'jquery',
			//'query-monitor',
			'customize-base',
			'customize-loader',
			'customize-preview',
			'customize-models',
			'customize-views',
			'customize-controls',
			'customize-widgets',
			'customize-preview-widgets',
			'cn_rot13_js',
		);

		/**
		 * Add other Customizer sections to the exemptions list so they will not be removed.
		 *
		 * @since 8.4
		 *
		 * @param array $exemptions
		 */
		$exceptions = apply_filters( 'cn_template_customizer_script_exceptions', $exceptions );

		if ( is_object( $wp_scripts ) && isset( $wp_scripts->queue ) && is_array( $wp_scripts->queue ) ) {

			wp_dequeue_script( array_diff( $wp_scripts->queue, $exceptions ) );
		}
	}

	//public function remove_all_styles() {
	//	global $wp_styles;
	//
	//	if ( ! isset( $_REQUEST['cn-customize-template'] ) ) return;
	//
	//	$exceptions = array();
	//
	//	if ( is_object( $wp_styles ) && isset( $wp_styles->queue ) && is_array( $wp_styles->queue ) ) {
	//
	//		wp_dequeue_style( array_diff( $wp_styles->queue, $exceptions ) );
	//	}
	//}

	/**
	 * Dequeue the Themify Customizer scripts because it takes over the Customizer panel when loaded.
	 *
	 * @link http://connections-pro.com/support/topic/pro-pack-install-widget-template-issues/
	 *
	 * @access private
	 * @since  8.5.2
	 */
	public function themeCompatibility() {

		//if ( isset( $GLOBALS['themify_customizer'] ) ) {

			//$Themify_Customizer = $GLOBALS['themify_customizer'];

			wp_dequeue_script( 'themify-customize-control' );
		//}
	}

	/**
	 * Remove all footer actions except those that are required for the Customizer and the template to function.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses remove_action()
	 */
	public function remove_footer_actions() {
		global $wp_filter;

		if ( ! isset( $_REQUEST['cn-customize-template'] ) ) return;

		$exceptions = array(
			'wp_print_footer_scripts',
		);

		foreach ( $wp_filter['wp_footer'] as $priority => $action ) {

			$handle = key( $action );

			if ( in_array( $handle, $exceptions ) ) {

				continue;
			}

			remove_action( $handle, $action[ $handle ]['function'], $priority );
		}
	}

	/**
	 * Render the back link.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnURL::permalink()
	 *
	 * @param array    $atts
	 * @param cnOutput $entry Instance of the cnEntry class.
	 */
	public function singleActions( $atts = array(), $entry ) {

		echo '<ul><li>';

		cnURL::permalink(
			array(
				'type' => 'home',
				'text' => __( 'Go Back', 'connections' ),
				'return' => FALSE
			)
		);

		echo '</li></ul>';
	}

	/**
	 * Render the note informing the user of basic instructions.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::supports()
	 */
	public function instructions() {

		?>

		<div id="cn-customizer-messages">
			<ul id="cn-customizer-message-list">
				<li class="cn-customizer-message"><?php _e( 'Do not navigate away from the template customizer preview doing so could mean the loss of any unsaved changes.', 'connections' ) ?></li>
				<li class="cn-customizer-message"><?php _e( 'If any shortcode override options have been used, they will have priority and the Template Customizer options will not have any effect.', 'connections' ) ?></li>
			</ul>
		</div>

		<?php
	}

	/**
	 * Render the note informing the user knows the template supports independent customization of the single entry view.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::supports()
	 */
	public function singleView() {

		if ( $this->supports( 'single' ) && ! cnQuery::getVar( 'cn-entry-slug' ) ) {
			?>

			<div id="cn-customizer-messages">
				<ul id="cn-customizer-message-list">
					<li class="cn-customizer-message"><?php _e( '<strong>NOTE:</strong> Template supports single view. You can click an entry name and customize the single entry view independently from the results list view.', 'connections' ) ?></li>
				</ul>
			</div>

			<?php
		}
	}

	/**
	 * Render the note informing the user that the category select drop down is for customization/display purposes only,
	 * that is non-functional.
	 *
	 * @access private
	 * @since  8.4
	 *
	 * @uses   cnTemplate_Customizer::supports()
	 */
	public function categoryMessage() {

		if ( $this->supports( 'category-select' ) && ! cnQuery::getVar( 'cn-entry-slug' ) ) {
			?>

			<div id="cn-customizer-messages">
				<ul id="cn-customizer-message-list">
					<li class="cn-customizer-message"><?php _e( '<strong>NOTE:</strong> Category select is for customization purposes only. It will not filter the results.', 'connections' ) ?></li>
				</ul>
			</div>

			<?php
		}
	}
}

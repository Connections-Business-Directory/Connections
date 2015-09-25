<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class cnCard_Customizer {

	public static function init() {

		/**
		 * NOTE: The Template Customizer actions need added here. The template is activated too late in order
		 *       for additional Customizer panels, sections and settings to be able to be registered.
		 */
		add_action( 'cn_template_customizer_register-card', array( __CLASS__, 'customizer' ), 10, 2 );
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 * @param object               $template
	 */
	public static function customizer( $wp_customize, $template ) {

		$base = 'connections_template';
		$slug = $template->slug;
		//$id   = 'cn_card_border_width';

		$wp_customize->add_setting(
			"{$base}_{$slug}[card][border_width]",
			array(
				'type'       => 'option',
				'default'    => 1,
				'transport'  => 'refresh',
				'capability' => 'edit_theme_options',
				'sanitize_callback'    => 'absint',
				//'sanitize_js_callback' => '',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				"cn_{$slug}_card_border_width",
				array(
					'label'       => __( 'Border Width', 'connections' ),
					'type'        => 'number',
					'section'     => 'cn_template_customizer_section_style',
					'settings'    => "{$base}_{$slug}[card][border_width]",
					'description' => __( 'Enter border width in pixels.', 'connections' ),
				)
			)
		);

		$wp_customize->add_setting(
			"{$base}_{$slug}[card][border_color]",
			array(
				'type'       => 'option',
				'default'    => '#E3E3E3',
				'transport'  => 'refresh',
				'capability' => 'edit_theme_options',
				//'sanitize_callback'    => 'absint',
				//'sanitize_js_callback' => '',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"cn_{$slug}_card_border_color",
				array(
					'label'       => __( 'Border Color', 'connections' ),
					//'type'        => 'number',
					'section'     => 'cn_template_customizer_section_style',
					'settings'    => "{$base}_{$slug}[card][border_color]",
					//'description' => __( 'Enter border width in pixels.', 'connections' ),
				)
			)
		);

		$wp_customize->add_setting(
			"{$base}_{$slug}[card][border_radius]",
			array(
				'type'       => 'option',
				'default'    => 4,
				'transport'  => 'refresh',
				'capability' => 'edit_theme_options',
				'sanitize_callback'    => 'absint',
				//'sanitize_js_callback' => '',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				"cn_{$slug}_card_border_radius",
				array(
					'label'       => __( 'Border Radius', 'connections' ),
					'type'        => 'number',
					'section'     => 'cn_template_customizer_section_style',
					'settings'    => "{$base}_{$slug}[card][border_radius]",
					'description' => __( 'Enter border width in pixels.', 'connections' ),
				)
			)
		);
	}
}

cnCard_Customizer::init();

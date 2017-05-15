<?php
/*
Plugin Name: Genesis Design Palette Pro - Video Background
Plugin URI: https://evermoresites.com
Description: Add a video background to your homepage
Author: Logos Creative
Version: 0.0.1
Requires at least: 4.6
Author URI: https://evermoresites.com
*/
/*  Copyright 2016 Logos Creative

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GP_Pro_Video_Background
{

	/**
	 * Static property to hold our singleton instance
	 * @var GP_Pro_Video_Background
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return GP_Pro_Video_Background
	 */
	private function __construct() {

		// general backend
		add_action(	'admin_notices', array($this, 'gppro_active_check'), 10 );
		add_action(	'admin_notices', array($this, 'supported_theme_check'), 10 );

		// GP Pro specific
		add_filter( 'gppro_admin_block_add', array($this, 'video_background_block'), 81 );
		add_filter( 'gppro_sections', array($this, 'video_background_section'), 10, 2 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );

		// Add Vide
		add_action( 'wp_head', array($this, 'enqueue_style') );
		add_action( 'wp_footer', array($this, 'enqueue_scripts') );
		add_action( 'wp_footer', array($this, 'vide_init'), 999 );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return GP_Pro_Video_Background
	 */
	public static function getInstance() {

		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * check for GP Pro being active
	 *
	 */
	public function gppro_active_check() {
		// get the current screen
		$screen = get_current_screen();
		// bail if not on the plugins page
		if ( is_object( $screen ) && $screen->parent_file !== 'plugins.php' ) {
			return;
		}
		// run the active check
		$coreactive	= class_exists( 'Genesis_Palette_Pro' ) ? Genesis_Palette_Pro::check_active() : false;
		// active. bail
		if ( $coreactive ) {
			return;
		}
		// not active. show message
		echo '<div id="message" class="error fade below-h2"><p><strong>'.__( sprintf( 'This plugin requires Genesis Design Palette Pro to function and cannot be activated.' ), 'gppro-video-background' ).'</strong></p></div>';
		// hide activation method
		unset( $_GET['activate'] );
		// deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// and finish
		return;
	}

	/**
	 * Check for a supported theme being active
	 *
	 */
	public function supported_theme_check() {
		// get the current screen
		$screen = get_current_screen();
		// bail if not on the plugins page
		if ( is_object( $screen ) && $screen->parent_file !== 'plugins.php' ) {
			return;
		}
		// run the active check
		$themeactive = $this->is_theme_supported();
		// active. bail
		if ( $themeactive ) {
			return;
		}
		// not active. show message
		echo '<div id="message" class="error fade below-h2"><p><strong>'.__( sprintf( 'This plugin does not support the currently active theme. Please activate a supported theme and reactivate this plugin.' ), 'gppro-video-background' ).'</strong></p></div>';
		// hide activation method
		unset( $_GET['activate'] );
		// deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// and finish
		return;
	}

	/**
	 * Return the array of supported themes and their DOM elements
	 *
	 * @param  string $theme     A theme slug
	 *
	 * @return string $target  The DOM selector to target with the video background
	 */
	public function get_supported_themes_elements() {

		return array(
			'agency-pro' => '.backstretch',
			'agentpress-pro' => '.home-featured',
			'altitude-pro' => '.front-page-1',
			'atmosphere-pro' => '.front-page-1',
			'cafe-pro' => '.front-page-header',
			'centric-pro' => '.home-featured',
			'digital-pro' => '.front-page-1',
			'education-pro' => '.home-featured',
			'interior-pro' => '.front-page-1',
			'minimum-pro' => '.backstretch',
			'outreach-pro' => '.site-inner',
			'parallax-pro' => '.home-section-1',
			'remobile-pro' => '.home-intro',
			'showcase-pro' => '.bg-primary',
			'utility-pro' => '.home-welcome',
			'whitespace-pro' => '.welcome',
			'winning-agent' => '.home-welcome',
			'workstation-pro' => '.image-section-1'
		);

	}

	/**
	 * Add a new block to the sidebar.
	 *
	 * @param  array $blocks  Our existing blocks on the sidebar.
	 *
	 * @return array $blocks  Our updated array of blocks on the sidebar.
	 */
	public function video_background_block( $blocks ) {

		// Bail if on multisite and user does not have access OR if the current theme is not supported
		if ( ( is_multisite() && ! current_user_can( 'upload_files' ) )  || !$this->is_theme_supported() ) {
			return $blocks;
		}

		// Create our new block.
		$blocks['video-background-'] = array(
			'tab'       => __( 'Video Background', 'gppro-video-background' ),
			'title'     => __( 'Video Background', 'gppro-video-background' ),
			'intro'     => __( 'Add a video background to your homepage.' ),
			'slug'      => 'video_background',
		);

		// Return the updated array.
		return $blocks;
	}

	/**
	 * Add our new CSS section to the block we created.
	 *
	 * @param  array  $sections  The individual sections being set up.
	 * @param  string $class     The body class applied.
	 *
	 * @return array  $sections  The new array of individual sections being set up.
	 */
	public function video_background_section( $sections, $class ) {

		// Bail if the current theme is not supported
		if ( !$this->is_theme_supported() ) {
			return $sections;
		}

		// Set up the 2 sections.
		$sections['video_background']   = array(
			'video-background--mp4-setup' => array(
				'title'     => __( 'Video (MP4)', 'gppro-video-background' ),
				'data'      => array(
					'video-background--mp4'   => array(
						'input'    => 'custom',
						'target'   => '',
						'selector' => '',
						'image'    => 'video',
						'desc'      => __( 'Only the .mp4 file type is allowed here.', 'gppro-video-background' ),
						'callback'  => array( $this, 'get_video_background_mp4_input' ),
					),
				),
			),
			'video-background--webm-setup' => array(
				'title'     => __( 'Video (WebM)', 'gppro-video-background' ),
				'data'      => array(
					'video-background--webm'   => array(
						'input'    => 'custom',
						'target'   => '',
						'selector' => '',
						'image'    => 'video',
						'desc'      => __( 'Only the .webm file type is allowed here.', 'gppro-video-background' ),
						'callback'  => array( $this, 'get_video_background_webm_input' ),
					),
				),
			),
			'video-background--mobile-setup' => array(
				'title'     => __( 'Mobile Image', 'gppro-video-background' ),
				'data'      => array(
					'video-background--mobile'   => array(
						'input'    => 'custom',
						'target'   => '',
						'selector' => '',
						'image'    => 'fallback',
						'desc'      => __( 'This image will show instead of the video background on mobile devices (for performance reasons).', 'gppro-video-background' ),
						'callback'  => array( $this, 'get_video_poster_input' ),
					),
				),
			),
		); // End the section.

		// Return the updated array.
		return $sections;
	}

	/**
	 * input field for video-background uploader
	 *
	 * @return string $input
	 */
	public static function get_video_background_mp4_input( $field, $item ) {

		// bail if pieces are missing
		if ( ! $field || ! $item || !$this->is_theme_supported() ) {
			return;
		}

		// fetch data for field
		$id     = GP_Pro_Helper::get_field_item( $field, 'id' );
		$name   = GP_Pro_Helper::get_field_item( $field, 'name' );
		$data   = get_option( 'gppro-settings' );
		$value  = '';

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-mp4' ] ) ) {
			$value  = $data[ 'video-background-mp4' ];
		}

		// escape the URL if we have it
		$value  = ! empty( $value ) ? esc_url( $value ) : '';

		// an empty
		$input  = '';

		// field wrapper
		$input .= '<div class="gppro-input gppro-image-input gppro-' . sanitize_html_class( $id ) . '-input">';

		$input .= '<div class="gppro-input-item gppro-input-wrap gppro-image-wrap gppro-video-background-wrap">';

		$input .= '<span class="gppro-image-field-wrap">';
		$input .= '<input type="url" id="' . sanitize_html_class( $id ) . '" name="' . esc_attr( $name ) . '" class="gppro-upload-field gppro-' . sanitize_html_class( $id ) . '-field" value="'.esc_url( $value ).'">';
		$input .= '</span>';

		$input .= '</div>';

		$input .= '<div class="gppro-input-item gppro-input-label choice-label">';

		$input .= '<span class="choice-label image-choice-label">';
		$input .= '<input id="' . sanitize_html_class( $field ) . '" type="button" class="button button-secondary button-small gppro-image-upload gppro-' . sanitize_html_class( $id ) . '-upload" value="' . __( 'Select', 'gppro' ) . '">';
		$input .= '</span>';

		$input .= '</div>';

		// handle description
		if ( ! empty( $item['desc'] ) ) {
			$input .= GP_Pro_Setup::get_input_desc( $item['desc'] );
		}

		$input .= '</div>';

		// return the input
		return $input;
	}

	/**
	 * input field for video-background uploader
	 *
	 * @return string $input
	 */
	public static function get_video_background_webm_input( $field, $item ) {

		// bail if pieces are missing
		if ( ! $field || ! $item || !$this->is_theme_supported() ) {
			return;
		}

		// fetch data for field
		$id     = GP_Pro_Helper::get_field_item( $field, 'id' );
		$name   = GP_Pro_Helper::get_field_item( $field, 'name' );
		$data   = get_option( 'gppro-settings' );
		$value  = '';

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-webm' ] ) ) {
			$value  = $data[ 'video-background-webm' ];
		}

		// escape the URL if we have it
		$value  = ! empty( $value ) ? esc_url( $value ) : '';

		// an empty
		$input  = '';

		// field wrapper
		$input .= '<div class="gppro-input gppro-image-input gppro-' . sanitize_html_class( $id ) . '-input">';

		$input .= '<div class="gppro-input-item gppro-input-wrap gppro-image-wrap gppro-video-background-wrap">';

		$input .= '<span class="gppro-image-field-wrap">';
		$input .= '<input type="url" id="' . sanitize_html_class( $id ) . '" name="' . esc_attr( $name ) . '" class="gppro-upload-field gppro-' . sanitize_html_class( $id ) . '-field" value="'.esc_url( $value ).'">';
		$input .= '</span>';

		$input .= '</div>';

		$input .= '<div class="gppro-input-item gppro-input-label choice-label">';

		$input .= '<span class="choice-label image-choice-label">';
		$input .= '<input id="' . sanitize_html_class( $field ) . '" type="button" class="button button-secondary button-small gppro-image-upload gppro-' . sanitize_html_class( $id ) . '-upload" value="' . __( 'Select', 'gppro' ) . '">';
		$input .= '</span>';

		$input .= '</div>';

		// handle description
		if ( ! empty( $item['desc'] ) ) {
			$input .= GP_Pro_Setup::get_input_desc( $item['desc'] );
		}

		$input .= '</div>';

		// return the input
		return $input;
	}

	/**
	 * input field for video-background uploader
	 *
	 * @return string $input
	 */
	public static function get_video_poster_input( $field, $item ) {

		// bail if pieces are missing
		if ( ! $field || ! $item || !$this->is_theme_supported() ) {
			return;
		}

		// fetch data for field
		$id     = GP_Pro_Helper::get_field_item( $field, 'id' );
		$name   = GP_Pro_Helper::get_field_item( $field, 'name' );
		$data   = get_option( 'gppro-settings' );
		$value  = '';

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-mobile' ] ) ) {
			$value  = $data[ 'video-background-mobile' ];
		}

		// escape the URL if we have it
		$value  = ! empty( $value ) ? esc_url( $value ) : '';

		// an empty
		$input  = '';

		// field wrapper
		$input .= '<div class="gppro-input gppro-image-input gppro-video-poster-input">';

		$input .= '<div class="gppro-input-item gppro-input-wrap gppro-image-wrap gppro-video-poster-wrap">';

		$input .= '<span class="gppro-image-field-wrap">';
		$input .= '<input type="url" id="' . sanitize_html_class( $id ) . '" name="' . esc_attr( $name ) . '" class="gppro-upload-field gppro-video-poster-field" value="'.esc_url( $value ).'">';
		$input .= '</span>';

		$input .= '</div>';

		$input .= '<div class="gppro-input-item gppro-input-label choice-label">';

		$input .= '<span class="choice-label image-choice-label">';
		$input .= '<input id="' . sanitize_html_class( $field ) . '" type="button" class="button button-secondary button-small gppro-image-upload gppro-video-poster-upload" value="' . __( 'Select', 'gppro' ) . '">';
		$input .= '</span>';

		$input .= '</div>';

		// handle description
		if ( ! empty( $item['desc'] ) ) {
			$input .= GP_Pro_Setup::get_input_desc( $item['desc'] );
		}

		$input .= '</div>';

		// return the input
		return $input;
	}

	/**
	 * call admin JS files
	 *
	 * @return mixed
	 */
	public function admin_scripts() {

		// check for our current DPP screen
		if ( false === $check = GP_Pro_Utilities::check_current_dpp_screen() || !$this->is_theme_supported() ) {
			return;
		}

		// set our prefix
		// @TODO
		//$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.js' : '.min.js';
		$suffix = '.js';

		wp_enqueue_script( 'gppro-video-background', plugins_url( 'lib/js/gppro.video-background'.$suffix, __FILE__ ), array( 'gppro-admin', 'jquery' ), GPP_VER, true );
		wp_localize_script( 'gppro-video-background', 'adminVideoBackgroundData', array(
			'videobgtitle'       => __( 'Select Your Video File', 'gppro' ),
			'postertitle'       => __( 'Select Your Image File', 'gppro' )
		));

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function enqueue_style() {

		if ( $this->is_theme_supported() && is_front_page() ) {

			echo '
			<style type="text/css">
			    .genesis-bg-video {
			        background: none;
			        opacity: 0;
			    }
				.genesis-bg-video video {
					max-width: none;
				}
			</style>';

		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function enqueue_scripts() {

		wp_register_script(
			'vide',
			plugins_url('bower_components/vide/dist/jquery.vide.min.js', __FILE__),
			array('jquery'),
			'0.5.1',
			true
		);

		if ( $this->is_theme_supported() && is_front_page() ) {

			wp_enqueue_script( 'vide' );

		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function vide_init() {

		if ( !$this->is_theme_supported() || !is_front_page() ) {
			return;
		}

		$data = get_option( 'gppro-settings' );
		$video = '';
		$poster = '';

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-mp4' ] ) ) {
			$mp4 = $data[ 'video-background-mp4' ];
		}

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-webm' ] ) ) {
			$webm = $data[ 'video-background-webm' ];
		}

		// If data for that viewport exists, send it back.
		if ( ! empty( $data[ 'video-background-mobile' ] ) ) {
			$poster  = $data[ 'video-background-mobile' ];
		}

		// Escape the URL if we have it
		$mp4 = ! empty( $mp4 ) ? esc_url( $mp4 ) : '';
		$webm = ! empty( $webm ) ? esc_url( $webm ) : '';
		$poster  = ! empty( $poster ) ? esc_url( $poster ) : '';
		$posterext = pathinfo($poster, PATHINFO_EXTENSION);

		// Get target DOM element
		$target = $this->get_target_dom_element();

		if ( $mp4 && $target ) {

			echo '
			<script>
				jQuery(function($) {
					var $targetDiv = $("' . $target . '");
					$targetDiv
					.addClass("genesis-bg-video")
					.vide({
					    mp4: "' . $mp4 . '",
					    webm: "' . $webm . '",
					    poster: "' . $poster . '"
					}, {
						position: "0% 0%",
						posterType: "' . $posterext . '"
					});
					$(".genesis-bg-video video")
					.on("canplay", function() {
						$targetDiv.fadeTo("slow", 1);
					});
				});
			</script>';

		}

	}

	/**
	 * Get the targeted DOM element when you pass in the theme slug
	 *
	 * @param  string $theme     A theme slug
	 *
	 * @return string $target  The DOM selector to target with the video background
	 */
	public function get_target_dom_element($theme = '') {

		if ( !$theme) {
			$theme = get_stylesheet();
		}

		$lookup = $this->get_supported_themes_elements();

		if ( !array_key_exists($theme, $lookup) ) {
			return '';
		}

		return $lookup[$theme];

	}

	/**
	 * Check if current (or arbitrary) theme is supported
	 *
	 * @param  string $theme     A theme slug
	 *
	 * @return boolean
	 */
	public function is_theme_supported($theme = '') {

		if ( !$theme) {
			$theme = get_stylesheet();
		}

		$lookup = $this->get_supported_themes_elements();

		if ( array_key_exists($theme, $lookup) ) {
			return true;
		}

		return false;

	}

/// end class
}

// Instantiate our class
$GP_Pro_Video_Background = GP_Pro_Video_Background::getInstance();


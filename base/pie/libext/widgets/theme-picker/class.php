<?php
/**
 * PIE API: widget extensions, theme picker widget class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE-extensions
 * @subpackage widgets
 * @since 1.0
 */

Pie_Easy_Loader::load( 'components/widgets/component' );

/**
 * Theme picker widget
 *
 * @package PIE-extensions
 * @subpackage widgets
 */
class Pie_Easy_Exts_Widgets_Theme_Picker
	 extends Pie_Easy_Widgets_Widget
{
	/**
	 * Current theme data
	 *
	 * @var array
	 */
	private $theme;

	/**
	 * Allowed themes data
	 *
	 * @var array
	 */
	private $themes;

	/**
	 */
	public function init_styles()
	{
		parent::init_styles();

		// slurp admin styles
		$this->style()
			->section( 'admin' )
			->cache( 'admin', 'admin.css' );
	}

	/**
	 */
	public function init_scripts()
	{
		parent::init_scripts();

		// slurp admin styles
		$this->script()
			->section( 'admin' )
			->cache( 'admin', 'admin.js' );
	}

	/**
	 * Load all themes information into local properties
	 * 
	 * @todo only list themes which implement the scheme
	 */
	protected function load_theme_data()
	{
		Pie_Easy_Loader::load_wpadmin_lib( 'ms' );
		Pie_Easy_Loader::load_wpadmin_lib( 'theme' );

		// get current theme
		$ct = current_theme_info();

		// get all themes
		$this->themes = get_allowed_themes();

		// extract current theme
		$this->theme = $this->themes[$ct->name];
	}

	/**
	 * Format URL to a theme's screenshot
	 *
	 * @param array $theme
	 * @return string
	 */
	public function get_sshot_url( $theme )
	{
		return $theme['Theme Root URI'] . '/' . $theme['Stylesheet'] . '/' . $theme['Screenshot'];
	}

	/**
	 * Format URL to a theme's preview
	 *
	 * @param array $theme
	 * @return string
	 */
	public function get_preview_url( $theme )
	{
		return add_query_arg(
			array(
				'preview' => 1,
				'template' => $theme['Template'],
				'stylesheet' => $theme['Stylesheet'],
				'TB_iframe' => 1
			),
			get_option( 'home' ) . '/'
		);
	}

	/**
	 * Format URL to activate a theme
	 *
	 * This is a specialized param list that will work for AJAX only
	 *
	 * @param array $theme
	 * @return string
	 */
	public function get_activate_url( $theme )
	{
		return
			sprintf(
				'?template=%s&amp;stylesheet=%s',
				urlencode($theme['Template']),
				urlencode($theme['Stylesheet'])
			);
	}

	/**
	 */
	public function get_template_vars()
	{
		// load theme data once
		$this->load_theme_data();

		return array(
			'theme' => $this->theme,
			'themes' => $this->themes,
		);
	}

	//
	// AJAX
	//

	/**
	 */
	public function init_ajax()
	{
		parent::init_ajax();
		
		add_action( 'wp_ajax_pie_easy_exts_widgets_theme_picker_activate', array( $this, 'ajax_switch_theme' ) );
	}

	/**
	 * Switch theme
	 *
	 * @todo make sure template is a theme which implements the scheme
	 */
	public function ajax_switch_theme()
	{
		// check for post id
		if ( isset( $_POST['template'] ) && isset( $_POST['stylesheet'] ) ) {

			// we are switching here people
			$template = trim( $_POST['template'] );
			$stylesheet = trim( $_POST['stylesheet'] );

			// attempt to activate the theme
			// this function does not return a freaking value, come on!!!
			switch_theme( $template, $stylesheet);

			// have to assume it worked
			Pie_Easy_Ajax::response( true, __('Theme activated', pie_easy_text_domain) );

		} else {
			Pie_Easy_Ajax::response( false, __('Theme activation failed', pie_easy_text_domain) );
		}
	}
}

?>

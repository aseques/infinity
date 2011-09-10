<?php
/**
 * PIE API: base factory class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage base
 * @since 1.0
 */

Pie_Easy_Loader::load( 'base/componentable', 'utils/files' );

/**
 * Make creating concrete components easy
 *
 * @package PIE
 * @subpackage base
 */
abstract class Pie_Easy_Factory extends Pie_Easy_Componentable
{
	/**
	 * Map of created classes and their extension type
	 * 
	 * @var Pie_Easy_Map
	 */
	private $ext_map;

	/**
	 * Set or get the ext that triggered a class to be loaded
	 *
	 * @param object|string $class_name
	 * @param string $ext
	 * @return string|false
	 */
	final public function ext( $class, $ext = null )
	{
		// handle map initialization
		if ( !$this->ext_map instanceof Pie_Easy_Map ) {
			$this->ext_map = new Pie_Easy_Map();
		}

		// class name
		$class_name = ( is_object( $class ) ) ? get_class( $class ) : $class;

		// set or get
		if ( $ext ) {
			// class must exist
			if ( class_exists( $class_name ) ) {
				// update map
				$this->ext_map->add( $class_name, $ext );
				return true;
			} else {
				throw new Exception( sprintf( 'The class "%s" does not exist', $class_name ) );
			}
		} else {
			return $this->ext_map->item_at( $class_name );
		}
	}

	/**
	 * Load a component extension
	 *
	 * Override this class to load component class files which exist outside of PIE's path
	 *
	 * @param string $ext Name of the extension
	 * @param string $class_prefix Prefix of class to load (if known)
	 * @return string Name of the class which was loaded
	 */
	public function load_ext( $ext, $class_prefix = null )
	{
		// format extension file name
		$file = $ext . DIRECTORY_SEPARATOR . 'extension.php';

		// look for scheme files first
		$file_theme =
			Pie_Easy_Scheme::instance()->locate_extension_file(
				$this->policy()->get_handle(), $file
			);

		// find a theme file?
		if ( $file_theme ) {
			$class_name = Pie_Easy_Files::file_to_class( $ext, $class_prefix );
			require_once $file_theme;
		} else {
			$class_name = Pie_Easy_Loader::load_ext( sprintf('%s/%s', $this->policy()->get_handle(), $ext ) );
		}
		
		$this->ext( $class_name, $ext );

		return $class_name;
	}

	/**
	 * Return an instance of a component
	 *
	 * @param string $theme
	 * @param string $name
	 * @param string $type
	 * @return Pie_Easy_Component
	 */
	public function create( $theme, $name, $type )
	{
		// load it from alternate location
		$class_name = $this->load_ext( $type );

		// create new component
		$component = new $class_name( $theme, $name, $type );

		// set the policy
		$component->policy( $this->policy() );

		// all done
		return $component;
	}

}

?>

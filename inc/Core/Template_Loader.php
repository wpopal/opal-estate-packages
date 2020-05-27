<?php
namespace Opalestate_Packages\Core;

class Template_Loader {
	/**
	 * Gets template path
	 *
	 * @access public
	 * @param $name
	 * @param $plugin_dir
	 * @return string
	 * @throws \Exception
	 */
	public static function locate( $name, $plugin_dir = OPALESTATE_PACKAGES_PLUGIN_URL ) {
		$template = '';

		// Current theme base dir
		if ( ! empty( $name ) ) {
			$template = locate_template( "{$name}.php" );
		}

		// Child theme
		if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/opal-estate-packages/{$name}.php" ) ) {
			$template = get_stylesheet_directory() . "/opal-estate-packages/{$name}.php";
		}

		// Original theme
		if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/opal-estate-packages/{$name}.php" ) ) {
			$template = get_template_directory() . "/opal-estate-packages/{$name}.php";
		}

		// Plugin
		if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/{$name}.php" ) ) {
			$template = $plugin_dir . "/templates/{$name}.php";
		}

		// Nothing found
		if ( empty( $template ) ) {
			throw new \Exception( "Template /templates/{$name}.php in plugin dir {$plugin_dir} not found." );
		}

		return $template;
	}

	/**
	 * Loads template content
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $plugin_dir
	 * @return string
	 */
	public static function get_template_part( $name, $args = array(), $plugin_dir = '' ) {
		if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args, EXTR_SKIP );
		}

		if ( ! $plugin_dir ) {
			$plugin_dir = OPALESTATE_PACKAGES_PLUGIN_PATH;
		}

		$path = static::locate( $name, $plugin_dir );
		ob_start();
		include $path;
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}

<?php
/**
 * The loader file.
 *
 * @package Opalestate_Packages
 */

/**
 * First, we need autoload via Composer to make everything works.
 */
require_once trailingslashit( __DIR__ ) . 'vendor/autoload.php';

/**
 * Then, require the main class.
 */
require_once trailingslashit( __DIR__ ) . 'inc/functions.php';
require_once trailingslashit( __DIR__ ) . 'inc/Plugin.php';

/**
 * Alias the class "Opalestate_Packages\Plugin" to "Opalestate_Packages".
 */
class_alias( \Opalestate_Packages\Plugin::class, 'Opalestate_Packages', false );

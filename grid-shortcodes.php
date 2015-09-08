<?php

namespace FunctionLabs;

/*
	Plugin Name: Grid Shortcodes
*/

class Grid_Shortcodes {
	/**
	 * HTML for inserting at the beginning of each col
	 * as a fix for wpautop not properly wrapping the first paragraph
	 */
	static $autopfix = '<p style="display:none;"><!-- autopfix --></p>';

	static $default_atts = array(
		'id'    => '',
		'class' => '',
		'lg_offset' => '',
		'md_offset' => '',
		'sm_offset' => '',
		'xs_offset' => ''
	);

	static function init() {
		self::add_shortcodes();

		add_filter( 'the_content', array( __CLASS__, 'do_grid_shortcodes' ), 7 );
		add_filter( 'the_content', array( __CLASS__, 'cleanup' ), 999 );
	}

	/**
	 * Process all grid shortcodes
	 * 'the_content' filter callback
	 * Only renders grid markup.
	 */
	static function do_grid_shortcodes( $content ) {
		global $shortcode_tags;

		// backup
		$_shortcode_tags = $shortcode_tags;

		// clear
		remove_all_shortcodes();

		// add
		self::add_shortcodes();

		// do
		$content = do_shortcode( $content );

		// restore
		$shortcode_tags = $_shortcode_tags;

		return $content;
	}

	/**
	 * Remove our autopfix html from output as it is no longer needed
	 */
	static function cleanup( $content ) {
		return str_replace( self::$autopfix, '', $content );
	}

	private static function add_shortcodes() {
		$tags = array(
			'row'
		);

		foreach( array( 'lg', 'md', 'sm', 'xs' ) as $size ){
			for( $i=1; $i<=12; $i++ ){
				$tags[] = sprintf( 'col-%s-%d', $size, $i );
			}
		}

		foreach ( $tags as $tag ) {
			add_shortcode( $tag, array( __CLASS__, 'grid_shortcodes' ) );
		}
	}

	/**
	 * Master callback for all grid shortcodes
	 */
	static function grid_shortcodes( $atts, $content, $tag ) {
		extract( shortcode_atts( self::$default_atts, $atts) );

		$grid_classes = self::get_grid_classes( $tag, $atts );
		$content =  self::$autopfix . trim( $content );

		if( !empty( $class ) ){
			$grid_classes[] = $class;
		}

		$inner = do_shortcode( $content );

		$grid = sprintf( '<div class="%s" id="%s">%s</div>', esc_attr( implode(' ', $grid_classes) ), esc_attr( $id ), $inner );

		return $grid;
	}

	static function get_grid_classes( $tag, $atts ){
		extract( shortcode_atts( self::$default_atts, $atts) );
		$classes = array( $tag );
		if( $tag !== 'row' ){
			foreach( array( 'lg', 'md', 'sm', 'xs' ) as $size ){
				$var = $size . '_offset';
				$offset_number = intval( $$var );

				if( !empty( $offset_number ) ){
					$classes[] = sprintf('col-%s-offset-%d', $size, $offset_number);
				}
			}
		}
		return $classes;
	}

}

add_action( 'init', array( 'FunctionLabs\Grid_Shortcodes', 'init' )  );
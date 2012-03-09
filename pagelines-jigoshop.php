<?php
/*
Plugin Name: PageLines-JigoShop
Plugin URI: http://www.pagelines.com
Author: PageLines
Author URI: http://www.pagelines.com
Demo: 
External: 
Description: JigoShop fixes....
PageLines: true
Version: 1.0
*/

class PageLinesJigoShop {
	
	/**
	 * Construct, always run when class is initiated
	 *
	 */
	function __construct() {

		if ( is_admin() )
			return;
			
		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename( dirname( __FILE__ ) ) );
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename( dirname( __FILE__ ) ) );
		
		add_action( 'wp_head', array( &$this, 'jigoshop_actions' ) );
		add_filter( 'pagelines_lesscode', array( &$this, 'jigoshop_less' ), 10, 1 );
		add_action( 'wp_print_styles', array( &$this, 'head_css' ) );		
	}
	
	function head_css() {
			
		if ( ! $this->check() )
			return;
		
		$style = sprintf( '%s/%s', $this->base_url, 'style.css' );
		
		wp_register_style( 'pl-jigoshop', $style );
		wp_enqueue_style( 'pl-jigoshop' );		
	
	}
	
	function jigoshop_less( $less ) {
		
		
		$less .= pl_file_get_contents( sprintf( '%s/color.less', $this->base_dir ) );
		
		return $less;
	}
	
	function jigoshop_actions() {

		if ( ! $this->check() )
			return;
		
	    remove_action( 'jigoshop_before_main_content', 'jigoshop_output_content_wrapper', 10 );
	    remove_action( 'jigoshop_after_main_content', 'jigoshop_output_content_wrapper_end', 10);
	    add_action( 'jigoshop_before_main_content', array( &$this, 'open_jigoshop_content_wrappers' ), 10 );
	    add_action( 'jigoshop_after_main_content', array( &$this, 'close_jigoshop_content_wrappers' ), 10 );
	
	}
	
		function open_jigoshop_content_wrappers() {
		    printf( '%1$s<!-- PageLines jigoshop before -->%1$s<section id="content" class="container fix"><div class="texture"><div class="content"><div class="content-pad"><div id="pagelines_content" class="fix"><div id="column-wrap" class="fix"><div id="column-main" class="mcolumn fix"><div class="mcolumn-pad"><section id="postloop" class="copy top-postloop postloop-bottom"><div class="copy-pad"><article class="page type-page hentry fpost"><div class="hentry-pad "><div class="entry_wrap fix"><div class="entry_content">%1$s<!-- PageLines jigoshop before -->%1$s', "\n" );

		}
	
		function close_jigoshop_content_wrappers() {
		     printf( '%1$s<!-- PageLines jigoshop before -->%1$s</div></div></div></article></div></section></div></div></div>%1$s<!-- PageLines jigoshop before -->%1$s', "\n" );
		}
		
		function check() {
			
			if ( ! function_exists( 'is_jigoshop' ) || ! function_exists( 'ploption' ) )
				return false;

			if ( ! is_jigoshop() )
				return false;
			
			return true;
		}
	
} // class end


// this starts the plugin code.
new PageLinesJigoShop;

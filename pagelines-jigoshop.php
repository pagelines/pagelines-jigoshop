<?php
/*
Plugin Name: PageLines Jigoshop Integration
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

		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename( dirname( __FILE__ ) ) );
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename( dirname( __FILE__ ) ) );

		if ( is_admin() )
			$this->admin_setup();
		
		add_action( 'wp_head', array( &$this, 'jigoshop_actions' ) );
		add_filter( 'pagelines_lesscode', array( &$this, 'jigoshop_less' ), 10, 1 );
		add_action( 'wp_print_styles', array( &$this, 'head_css' ) );
		add_action( 'template_redirect', array( &$this, 'jigo_integration' ) );	
	}	

	function admin_setup() {
		
		add_action( 'admin_print_styles', array( &$this, 'admin_css' ) );
		add_action( 'admin_init', array( &$this, 'admin_page' ) );
		add_filter( 'postsmeta_settings_array', array( &$this, 'jigo_meta' ), 10, 1 );
		add_filter( 'pl_cpt_dragdrop', array( &$this, 'jigo_templates' ), 1, 2 );

	
	}


	function admin_page() {
		
		pl_add_options_page( array( 
			'name'	=> 'jigoshop',
			'raw'	=> $this->instructions(),
		 	'title'	=> 'Jigoshop Instructions.'	
		) );	
	}


	function instructions() {
		
		return '<p>All pages are handled in their respective page meta settings except the main shop page, as this is an archive it requires a special meta page to control layout.</p>
				<p>It is not possible to add sections to the shop or product content areas, you can however add sections to the header/morefoot/footer areas and hide them by default then enable them on the product archive special meta tab.</p>';
		
		
	}

	
	/**
	 *	Remove products from the template setup area, we cant control them so remove them.
	 *  Individual products have meta settings, product archive is handled in special meta.
	 */	
	function jigo_templates( $public_post_type, $dragdrop ) {
		
		if ( 'product' == $public_post_type )
			return false;
		return true;
	}
	
	
	
	/**
	 *	Add integration to store page
	 */
	function jigo_integration() {

		if ( ! $this->check() )
			return;
		if ( is_archive() )
			new PageLinesIntegration( 'product_archive' );
	}
	
	/**
	 *	Add tab to Special Meta
	 */
	function jigo_meta( $d ) {

		global $metapanel_options;

		$meta = array(
		
		'product_archive' => array(
			'metapanel' => $metapanel_options->posts_metapanel( 'product_archive', 'product_archive' ),
			'icon'		=> $this->base_url.'/icon.png'
		) );
			$d = array_merge($d, $meta);

			return $d;
		}

	/**
	 *	Remove duplicate jquery-ui styles
	 */
	function admin_css() {
		
		if ( function_exists( 'ploption') )
			wp_deregister_style( 'jquery-ui-jigoshop-styles' );
	}
	
	/**
	 *	Register our css and enqueue
	 */
	function head_css() {
			
		if ( ! $this->check() )
			return;
		
		$style = sprintf( '%s/%s', $this->base_url, 'style.css' );
		
		wp_register_style( 'pl-jigoshop', $style );
		wp_enqueue_style( 'pl-jigoshop' );		
	
	}

	/**
	 *	Include the LESS css file
	 */	
	function jigoshop_less( $less ) {
		
		
		$less .= pl_file_get_contents( sprintf( '%s/color.less', $this->base_dir ) );
		
		return $less;
	}


	/**
	 *	Add the markup to product pages
	 */	
	function jigoshop_actions() {

		if ( ! $this->check() )
			return;
		
	    remove_action( 'jigoshop_before_main_content', 'jigoshop_output_content_wrapper', 10 );
	    remove_action( 'jigoshop_after_main_content', 'jigoshop_output_content_wrapper_end', 10);
	    add_action( 'jigoshop_before_main_content', array( &$this, 'open_jigoshop_content_wrappers' ), 10 );
	    add_action( 'jigoshop_after_main_content', array( &$this, 'close_jigoshop_content_wrappers' ), 10 );
	
	}
	
	function open_jigoshop_content_wrappers() {
	
		?><!-- PageLines jigoshop before -->
			<section id="content" class="pl-jigo container fix">
				<div class="texture">
					<div class="content">
						<div class="content-pad">
							<div id="pagelines_content" class="fix">
								<div id="column-wrap" class="fix">
									<div id="column-main" class="mcolumn fix">
										<div class="mcolumn-pad">
											<section id="postloop" class="copy top-postloop postloop-bottom">
												<div class="copy-pad">
													<article class="page type-page hentry fpost">
														<div class="hentry-pad ">
															<div class="entry_wrap fix">
																<div class="entry_content">
		<?php 

		}
	
	function close_jigoshop_content_wrappers() {

		?>
																</div>
															</div>
														</div>
													</article>
												</div>
											</section>
										</div>
									</div>
								</div>
		<?php 

	}

	/**
	 *	Check if we are in jigoshop and PageLines Framework.
	 */		
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

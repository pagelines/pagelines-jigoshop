<?php
/*
Plugin Name: Jigoshop for PageLines
Plugin URI: http://www.pagelines.com
Author: PageLines
Author URI: http://www.pagelines.com
Demo: http://demo.pagelines.com/framework/shop
Description: Refines and configures the popular Jigoshop plugin for seamless integration into PageLines. 
PageLines: true
Version: 1.4.6
Edition: pro
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
		add_filter( 'pl_cpt_dragdrop', array( &$this, 'jigo_templates' ), 11, 3 );	
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
	function jigo_templates( $bool, $public_post_type, $area ) {
		
		if ( 'product' === $public_post_type )
			$bool = false;
		elseif( 'product_variation' === $public_post_type )
			$bool = false;
		else
			$bool =  $bool;

		return $bool;
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

		if ( ! $this->check() ) {

			// wp_deregister_style( 'jigoshop_frontend_styles' );
			wp_deregister_style( 'jqueryui_styles' );
			wp_deregister_style( 'jigoshop_fancybox_styles' );
		}
		$style = sprintf( '%s/%s', $this->base_url, 'style.css' );		
		wp_register_style( 'pl-jigoshop', $style );
		wp_enqueue_style( 'pl-jigoshop' );			
	}




	/**
	 *	Add the markup to product pages
	 */	
	function jigoshop_actions() {

		if ( ! $this->check() )
			return;
		
	    remove_action( 'jigoshop_before_main_content', 'jigoshop_output_content_wrapper', 10 );
	    remove_action( 'jigoshop_after_main_content', 'jigoshop_output_content_wrapper_end', 10);
	
		if ( version_compare( CORE_VERSION, '2.2.3', '<' ) )  {
	    	add_action( 'jigoshop_before_main_content', array( &$this, 'open_jigoshop_content_wrappers' ), 10 );
	    	add_action( 'jigoshop_after_main_content', array( &$this, 'close_jigoshop_content_wrappers' ), 10 );
		}
		?>
		
		<script>
		// Initialize Blocks
		jQuery(window).load(function() {
			jQuery('.products .product').equalizer();

		});
		
		</script>
		
		<?php
	
	}
	
	function open_jigoshop_content_wrappers() {
	
		?><!-- PageLines jigoshop before -->
			<section id="content" class="pl-jigo container fix">
				<div class="texture">
					<div class="content">
						<div class="content-pad">
							<div id="pagelines_content" class="fix <?php echo pl_layout_mode() ?>">
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
								<?php echo $this->get_sidebar(); ?>
								</div>
		<?php 

	}

	function get_sidebar() {
	
		if ( 'two-sidebar-center' != pl_layout_mode() )
			return '';
		ob_start(); ?>
<div id="sidebar1" class="scolumn fix">
	<div class="scolumn-pad">
		<section id="sb_primary" class="copy no_clone section-sb_primary">
			<div class="copy-pad">
<?php pagelines_draw_sidebar('sb_primary', 'Primary Sidebar', 'includes/widgets.default'); ?>
			</div>
		</section>
	</div>
</div>
<?php return ob_get_clean();
		
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
	
	/**
	 *	Include the LESS css file
	 */	
	function jigoshop_less( $less ) {
		
		$less .= pl_file_get_contents( sprintf( '%s/color.less', $this->base_dir ) );		
		return $less;
	}
	
} // class end


// this starts the plugin code.
new PageLinesJigoShop;
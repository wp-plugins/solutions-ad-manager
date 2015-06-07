<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://solutionsbysteve.com
 * @since      0.1.0
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/public
 * @author     Steven Maloney <steve@solutionsbysteve.com>
 */
class Solutions_Ad_Manager_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $solutions_ad_manager    The ID of this plugin.
	 */
	private $solutions_ad_manager;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $solutions_ad_manager       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $solutions_ad_manager, $version ) {

		$this->solutions_ad_manager = $solutions_ad_manager;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->solutions_ad_manager, plugin_dir_url( __FILE__ ) . 'css/solutions-ad-manager-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->solutions_ad_manager, plugin_dir_url( __FILE__ ) . 'js/solutions-ad-manager-public.js', array( 'jquery' ), $this->version, false );

	}
	
	public function Solutions_Ad_Manager_Redirect(){
		if( isset($_GET['sam-redirect-to']) && isset($_GET['sam-post-id']) ){
			//add to clicks
			$clicks = get_post_meta($_GET['sam-post-id'], 'solutions_ad_clicks', true);
			$clicks++;
			update_post_meta($_GET['sam-post-id'], 'solutions_ad_clicks', $clicks);
			//redirect
			if( !empty($_GET['sam-redirect-to']) ){
				wp_redirect( $_GET['sam-redirect-to'], 302 );
				exit;
			}else{
				wp_redirect( esc_url("http://" . $_SERVER['HTTP_HOST']  . strtok($_SERVER['REQUEST_URI'],'?') ) );
				exit;
				
			}
		}
		
	}
	
	
	
	/**
	 * Decides which emebeder to use for media.
	 *
	 * @since    0.4.0
	 */
	public function sam_oembed($URL = NULL){
		$code = '';
		if( !is_null($URL) && !empty($URL) ){
			if(strpos($URL, 'youtu.be') !== false || strpos($URL, 'youtube.com') !== false){
				
				$youtube = array();
				//Get video ID
				if( strpos($URL, 'youtu.be') !== false ){
					$youtube = parse_url($URL);
					$youtube['v'] = ltrim($youtube['path'], '/');
				}elseif( strpos($URL, 'youtube.com') !== false ){
					parse_str( parse_url( $URL, PHP_URL_QUERY ), $youtube );
				}
				if(!isset($youtube['v'])){ return 'error';}
				$embedURL = 'https://www.youtube.com/embed/'.$youtube['v'].'?feature=oembed';
				
				//check if playlist
				if(isset($youtube['list'])){
					$embedURL .= '&listType=playlist&list='.$youtube['list'];
				}
				
				//Get user options
				$options = get_option( 'solutions-ad-manager-options', array() );
				//check for show title
				if(!isset( $options['solutions-ad-manager-youtube-showtitle'] )){
					$embedURL .= '&showinfo=0';
				}
				//check for show controls
				if(!isset( $options['solutions-ad-manager-youtube-showcontrols'] )){
					$embedURL .= '&controls=0';
				}
				//check for autoplay
				if(isset( $options['solutions-ad-manager-youtube-autoplay'] )){
					$embedURL .= '&autoplay=1';
				}
				//check for showrelated
				if(!isset( $options['solutions-ad-manager-youtube-showrelated'] )){
					$embedURL .= '&rel=0';
				}
				
				$code = '<iframe class="youtube-media" width="640" height="360" src="'.$embedURL.'" frameborder="0" allowfullscreen=""></iframe>';
			}else{
				$code = wp_oembed_get( $URL );
			}
		}
		return $code;
	}
	
		/**
	 * Register Shortcodes.
	 *
	 * @since    0.7.0
	 */
	 public function register_solutions_ad_manager_shortcode() {
		 add_shortcode( 'sam-display-ad', array( $this, 'sam_display_ad_shortcode') );
	 }
	 // [sam-display-ad group='' specific='' show_title='']
	 public function sam_display_ad_shortcode( $atts ){

		// Attributes
		extract( shortcode_atts(
			array(
				'group' => NULL,
				'specific' => NULL,
				'show_title' => NULL,
			), $atts )
		);
		
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options['solutions-ad-manager-stretch-image'] )){ 
			$stretchImage = false;
		}else{
			$stretchImage = $options['solutions-ad-manager-stretch-image'];
		}
		
		//Create Output
		$output  = '';
		
		//GROUPS
		if( !is_null($group) && !empty($group)  ){
			
			global $sam_ad_array;
			$term = get_term_by( 'slug', $group, 'solutions-ad-group' );
			$group = $term->slug;
			$query_args = array( 
				'post_type' => array('solutions-ad-manager'),
				$term->taxonomy => $group,
				'orderby' => 'rand',
				'nopaging' => 'true', //shows all adds in stead of 10 per query
			);
			if( empty($sam_ad_array[$group]) || !$sam_ad_array[$group]->have_posts() ){
				// Create new query
				$sam_ad_array[$group] = new WP_Query( $query_args );
			}
			if ( $sam_ad_array[$group]->have_posts() ) {
				$sam_ad_array[$group]->the_post();
				
				$output .= '<div class="solutions-ad-manager-shortcode" id="samid-'.$sam_ad_array[$group]->post->ID.'">';
				
				$siteURL = home_url();
				$meta = array();
				$meta['URL'] = get_post_meta( $sam_ad_array[$group]->post->ID, 'solutions_ad_url', true );
				$meta['title'] = get_the_title();
				$meta['media'] = esc_html(get_post_meta( $sam_ad_array[$group]->post->ID, 'solutions_ad_oembed', true ));
				if ( !is_null($show_title) && !empty($meta['title']) ) {
					$output .= '<h2 class="shortcode-title">';
					if(!empty($meta['URL'])){$output .= '<a href="' . $meta['URL'] . '">';}
					$output .= $meta['title'];
					if(!empty($meta['URL'])){$output .= '</a>';}
					$output .= '</h2>';
				}
				if ( !empty($meta['media']) ){
					$output .= '<div class="media">';
					$output .= $this->sam_oembed($meta['media']);
					$output .= '</div>';
				}else{
					$output .= '<div class="image">';
					if(!empty($meta['URL'])){$output .= '<a href="' . esc_url($siteURL) . '?sam-redirect-to=' . esc_url( $meta['URL'] ) . '&sam-post-id=' . $sam_ad_array[$group]->post->ID . '" rel="nofollow">';}
					$output .= get_the_post_thumbnail( $sam_ad_array[$group]->post->ID,  'full' );
					if(!empty($meta['URL'])){$output .= '</a>';}
					$output .= '</div>';
				}
				
				$output .= '</div>';
			} else {
				$output .= '<div class="solutions-ad-manager-shortcode">';
				$output .= __( 'No Ad Found', 'solutions-ad-manager' );
				$output .= '</div>';
			}

		
		//SPECIFIC
		}elseif( !is_null($specific) && !empty($specific)  ){
			
			
			$query_args = array( 
				'post_type' => array('solutions-ad-manager'),
				'p' => $specific,
			);
			
			$specificAd = new WP_Query( $query_args );
			
		
			$output .= '<div class="solutions-ad-manager-shortcode" id="samid-'.$sam_ad_array[$group]->post->ID.'">';
			
			if ( $specificAd->have_posts() ) {
				$specificAd->the_post();
				
				$siteURL = home_url();
				$meta = array();
				$meta['URL'] = get_post_meta( $specificAd->post->ID, 'solutions_ad_url', true );
				$meta['title'] = get_the_title();
				$meta['media'] = esc_html(get_post_meta( $specificAd->post->ID, 'solutions_ad_oembed', true ));
				if ( !is_null($show_title) && !empty($meta['title']) ) {
					$output .= '<h2 class="shortcode-title">';
					if(!empty($meta['URL'])){$output .= '<a href="' . $meta['URL'] . '">';}
					$output .= $meta['title'];
					if(!empty($meta['URL'])){$output .= '</a>';}
					$output .= '</h2>';
				}
				if ( !empty($meta['media']) ){
					$output .= '<div class="media">';
					$output .= $this->sam_oembed($meta['media']);
					$output .= '</div>';
				}else{
					$output .= '<div class="image">';
					if(!empty($meta['URL'])){$output .= '<a href="' . esc_url($siteURL) . '?sam-redirect-to=' . esc_url( $meta['URL'] ) . '&sam-post-id=' . $specificAd->post->ID . '" rel="nofollow">';}
					$output .= get_the_post_thumbnail( $specificAd->post->ID,  'full' );
					if(!empty($meta['URL'])){$output .= '</a>';}
					$output .= '</div>';
				}
				
			} else {
				$output .= '<div class="solutions-ad-manager-shortcode">';
				$output .= __( 'No Ad Found', 'solutions-ad-manager' );
				$output .= '</div>';
			}
			
			$output .= '</div>';
		
		
		}
		
		
		
		return $output;
	 }



}

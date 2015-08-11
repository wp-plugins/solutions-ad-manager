<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://solutionsbysteve.com
 * @since      0.1.0
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/admin
 * @author     Steven Maloney <steve@solutionsbysteve.com>
 */
class Solutions_Ad_Manager_Admin {

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
	 * @param      string    $solutions_ad_manager       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $solutions_ad_manager, $version ) {

		$this->solutions_ad_manager = $solutions_ad_manager;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->solutions_ad_manager, plugin_dir_url( __FILE__ ) . 'css/solutions-ad-manager-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->solutions_ad_manager, plugin_dir_url( __FILE__ ) . 'js/solutions-ad-manager-admin.js', array( 'jquery' ), $this->version, false );

	}

	
	/**
	 * Include Metabox Library.
	 *
	 * @since    0.1.0
	 */
	public function register_meta_boxes() {
		
		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/metaboxes/init.php';
			
	}
	
	/**
	 * Create Custom Meta.
	 * https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress/wiki/Field-Types
	 * @since    0.1.0
	 */
	 public function define_meta_boxes( $meta_boxes ) {
		$meta_boxes = array(
			'solutions_ad_meta' => array(
				'id' => 'solutions_ad_meta',
				'title' => __( 'Ad Meta', 'solutions-ad-manager' ),
				'pages' => array('solutions-ad-manager'),//post_type.
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true,
				'fields' => array(
					array(
						'name' => 'End Date',
						'id'   => 'solutions_ad_end_date',
						'type' => 'text_datetime_timestamp',
						'default' => date( "U", strtotime('+1 year'))
					),
					array(
						'name' => __( 'Website URL', 'solutions-ad-manager' ),
						'id' => 'solutions_ad_url',
						'type' => 'text_url',
					),
					array(
						'name' => __( 'Clicks', 'solutions-ad-manager' ),
						'default' => 0,
						'id' => 'solutions_ad_clicks',
						'type' => 'text_small',
						'attributes'  => array(
							'disabled'    => 'disabled',
						),
					)
				)
			),
			'solutions_ad_media' => array(
				'id' => 'solutions_ad_media',
				'title' => __( 'Ad Media', 'solutions-ad-manager' ),
				'pages' => array('solutions-ad-manager'),//post_type.
				'context' => 'normal',
				'priority' => 'high',
				'show_names' => true,
				'fields' => array(
					array(
						'name' => __( 'Media', 'solutions-ad-manager' ),
						'desc' => 'Enter a youtube, twitter, or instagram URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.',
						'id' => 'solutions_ad_oembed',
						'type' => 'oembed',
					),
				)
			)
		);
		return $meta_boxes;
	}
	
	
	/**
	 * Custom Post Type Columns.
	 *
	 * @since    0.1.0
	 * @updated  0.5.0 - remove thumbnail from columns
	 */
	public function solutions_ad_manager_custom_columns( $columns ) {
		unset($columns['date']);
		unset($columns['thumbnail']);
		$columns['url'] = __( 'URL', 'solutions-ad-manager' );
		$columns['status'] = __( 'Status', 'solutions-ad-manager' );
		$columns['media'] = __( 'Media', 'solutions-ad-manager' );
		$columns['clicks'] = __( 'Clicks', 'solutions-ad-manager' );
		return $columns;
	}
	
	// Display the column content
	public function solutions_ad_manager_custom_columns_display( $column, $post_id ) {
		switch ( $column ) {
			case 'url' :
				$meta = get_post_meta($post_id, 'solutions_ad_url', true);
				if ( !$meta ){
					$meta = '—';
				}
				echo $meta;
				break;
			case 'status' :
				$meta = get_post_status($post_id);
				if( $meta == 'publish' ){
					$meta = 'Active';
				}elseif( $meta == 'pending' ){
					$meta = 'Pending Review';
				}elseif( $meta == 'draft' ){
					$meta = 'Inactive';
				}else{
					$meta = '—';
				}
				echo $meta;
				break;
			case 'media' :
				$media = esc_html(get_post_meta( $post_id, 'solutions_ad_oembed', true ));
				$image = get_the_post_thumbnail( $post_id,  array(50,50) );
				if ( !empty($media) ){
					//$meta = wp_oembed_get($media, array('width'=>50,'height'=>50));
					$meta = 'Media';
				}elseif( !empty($image) ){
					$meta = $image;
				}else{
					$meta = '—';
				}
			
				echo $meta;
				break;
			case 'clicks' :
				$meta = get_post_meta($post_id, 'solutions_ad_clicks', true);
				if ( !$meta ){
					$meta = '—';
				}
				echo $meta;
				break;
		}
	}
	// Register the column as sortable
	public function solutions_ad_manager_custom_columns_sortable( $columns ) {
		$columns['clicks'] = 'clicks';
		return $columns;
	}
	public function solutions_ad_manager_custom_columns_sortable_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'clicks' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'solutions_ad_clicks',
				'orderby' => 'meta_value_num'
			) );
		}
		return $vars;
	}
	
	
	/**
	 * Taxonomy Filters.
	 *
	 * @since    0.1.0
	 */
	public function solutions_ad_manager_taxonomy_filters() {
		global $typenow;
		// an array of all the taxonomyies you want to display. Use the taxonomy name or slug
		$taxonomies = array('solutions-ad-group');
		// must set this to the post type you want the filter(s) displayed on
		if( $typenow == 'solutions-ad-manager' ){
			foreach ($taxonomies as $tax_slug) {
				$tax_obj = get_taxonomy($tax_slug);
				$tax_name = $tax_obj->labels->all_items;
				$terms = get_terms($tax_slug);
				if(count($terms) > 0) {
					echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
					echo "<option value=''>$tax_name</option>";
					foreach ($terms as $term) { 
						echo '<option ';
						echo 'value="' . $term->slug . '"';
						if(isset($_GET[$tax_slug]) && $_GET[$tax_slug] == $term->slug ){
							echo ' selected="selected"';
						}
						echo '>' . $term->name .' (' . $term->count .')</option>'; 
					}
				echo "</select>";
				}
			}
		}
	}
	
	
	/**
	 * Setting Page.
	 *
	 * @since    0.1.0
	 */
	function solutions_ad_manager_add_admin_menu(  ) { 
		//remove add new from menu
		global $submenu;
		unset($submenu['edit.php?post_type=solutions-ad-manager'][10]);
		//add how to use to menu
		add_submenu_page( 
			'edit.php?post_type=solutions-ad-manager', 
			__( 'Solutions Ad Manager', 'solutions-ad-manager' ), 
			__( 'How To Use', 'solutions-ad-manager' ), 
			'manage_options', 
			$this->solutions_ad_manager.'-howto',
			array( $this, 'solutions_ad_manager_howto_page' ) 
		);
		//add options page
		add_submenu_page( 
			'edit.php?post_type=solutions-ad-manager', 
			__( 'Solutions Ad Manager', 'solutions-ad-manager' ), 
			__( 'Options', 'solutions-ad-manager' ), 
			'manage_options', 
			$this->solutions_ad_manager.'-options',
			array( $this, 'solutions_ad_manager_options_page' ) 
		);
	}
	function solutions_ad_manager_howto_page(  ) { 
		/**
		 * Contains markup for the admin "How To Use" page
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/solutions-ad-manager-admin-display-howto.php';
	}
	function solutions_ad_manager_options_page(  ) { 
		/**
		 * Contains markup for the admin "How To Use" page
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/solutions-ad-manager-admin-display.php';
	}
	// Add settings link on plugin page
	function solutions_ad_manager_plugin_settings_link($links) { 
	  $settings_link = '<a href="'.get_admin_url(NULL, 'edit.php?post_type=solutions-ad-manager&page=solutions-ad-manager-howto').'"> ' . __( 'How To Use', 'solutions-ad-manager' ) . '</a>'; 
	  array_unshift($links, $settings_link); 
	  $settings_link = '<a href="'.get_admin_url(NULL, 'edit.php?post_type=solutions-ad-manager&page=solutions-ad-manager-options').'"> ' . __( 'Options', 'solutions-ad-manager' ) . '</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}

	/**
	 * Settings for Media Options.
	 *
	 * @since    0.2.0
	 * @updated  0.4.0 - fix settings names
	 */
	function solutions_ad_manager_settings_init(  ) { 
		register_setting( 'solutions-ad-manager-options', 'solutions-ad-manager-options' );
		add_settings_section(
			$this->solutions_ad_manager.'-image-section', 
			__( 'Image Options', 'solutions-ad-manager' ), 
			array( $this, 'sad_image_section_render' ), 
			'solutions-ad-manager-options'
		);
		add_settings_field( 
			$this->solutions_ad_manager.'-stretch-image', 
			__( 'Stretch Image to fit', 'solutions-ad-manager' ), 
			array( $this, 'sad_image_stretch_render' ), 
			'solutions-ad-manager-options', 
			$this->solutions_ad_manager.'-image-section' 
		);
		
		add_settings_section(
			$this->solutions_ad_manager.'-youtube-section', 
			__( 'Youtube Options', 'solutions-ad-manager' ), 
			array( $this, 'sad_youtube_section_render' ), 
			'solutions-ad-manager-options'
		);
		add_settings_field( 
			$this->solutions_ad_manager.'-youtube-showtitle', 
			__( 'Show Title', 'solutions-ad-manager' ), 
			array( $this, 'sad_youtube_showtitle_render' ), 
			'solutions-ad-manager-options', 
			$this->solutions_ad_manager.'-youtube-section' 
		);
		add_settings_field( 
			$this->solutions_ad_manager.'-youtube-showcontrols', 
			__( 'Show Controls', 'solutions-ad-manager' ), 
			array( $this, 'sad_youtube_showcontrols_render' ), 
			'solutions-ad-manager-options', 
			$this->solutions_ad_manager.'-youtube-section' 
		);
		add_settings_field( 
			$this->solutions_ad_manager.'-youtube-autoplay', 
			__( 'Autoplay', 'solutions-ad-manager' ), 
			array( $this, 'sad_youtube_autoplay_render' ), 
			'solutions-ad-manager-options', 
			$this->solutions_ad_manager.'-youtube-section' 
		);
		add_settings_field( 
			$this->solutions_ad_manager.'-youtube-showrelated', 
			__( 'Show Related', 'solutions-ad-manager' ), 
			array( $this, 'sad_youtube_showrelated_render' ), 
			'solutions-ad-manager-options', 
			$this->solutions_ad_manager.'-youtube-section' 
		);
	}
	
	function sad_image_section_render(  ) { 
		echo __( 'The section below only provides settings for images.', 'solutions-ad-manager' );
	}
	function sad_image_stretch_render(  ) { 
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options[$this->solutions_ad_manager.'-stretch-image'] )){ $options[$this->solutions_ad_manager.'-stretch-image'] = 0;}
		?>
		<input type="checkbox" name="<?php echo 'solutions-ad-manager-options' ?>[<?php echo $this->solutions_ad_manager.'-stretch-image' ?>]" value="1"<?php checked( 1, $options[$this->solutions_ad_manager.'-stretch-image'], true )?>>
		<?php
	}
	
	
	function sad_youtube_section_render(  ) { 
		echo __( 'The section below only provides settings for playback of youtube videos.', 'solutions-ad-manager' );
	}
	function sad_youtube_showtitle_render(  ) { 
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options[$this->solutions_ad_manager.'-youtube-showtitle'] )){ $options[$this->solutions_ad_manager.'-youtube-showtitle'] = 0;}
		?>
		<input type="checkbox" name="<?php echo 'solutions-ad-manager-options' ?>[<?php echo $this->solutions_ad_manager.'-youtube-showtitle' ?>]" value="1"<?php checked( 1, $options[$this->solutions_ad_manager.'-youtube-showtitle'], true )?>>
		<?php
	}
	function sad_youtube_showcontrols_render(  ) { 
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options[$this->solutions_ad_manager.'-youtube-showcontrols'] )){ $options[$this->solutions_ad_manager.'-youtube-showcontrols'] = 0;}
		?>
		<input type="checkbox" name="<?php echo 'solutions-ad-manager-options' ?>[<?php echo $this->solutions_ad_manager.'-youtube-showcontrols' ?>]" value="1"<?php checked( 1, $options[$this->solutions_ad_manager.'-youtube-showcontrols'], true )?>>
		<?php
	}
	function sad_youtube_autoplay_render(  ) { 
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options[$this->solutions_ad_manager.'-youtube-autoplay'] )){ $options[$this->solutions_ad_manager.'-youtube-autoplay'] = 0;}
		?>
		<input type="checkbox" name="<?php echo 'solutions-ad-manager-options' ?>[<?php echo $this->solutions_ad_manager.'-youtube-autoplay' ?>]" value="1"<?php checked( 1, $options[$this->solutions_ad_manager.'-youtube-autoplay'], true )?>>
		<?php
	}
	function sad_youtube_showrelated_render(  ) { 
		$options = get_option( 'solutions-ad-manager-options' );
		if(!isset( $options[$this->solutions_ad_manager.'-youtube-showrelated'] )){ $options[$this->solutions_ad_manager.'-youtube-showrelated'] = 0;}
		?>
		<input type="checkbox" name="<?php echo 'solutions-ad-manager-options' ?>[<?php echo $this->solutions_ad_manager.'-youtube-showrelated' ?>]" value="1"<?php checked( 1, $options[$this->solutions_ad_manager.'-youtube-showrelated'], true )?>>
		<?php
	}
	



}

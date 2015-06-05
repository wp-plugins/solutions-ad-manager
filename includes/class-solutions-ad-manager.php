<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://solutionsbysteve.com
 * @since      0.1.0
 * @basename   solutions-ad-manager/solutions-ad-manager.php
 *
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Solutions_Ad_Manager
 * @subpackage Solutions_Ad_Manager/includes
 * @author     Steven Maloney <steve@solutionsbysteve.com>
 */
class Solutions_Ad_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Solutions_Ad_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $solutions_ad_manager    The string used to uniquely identify this plugin.
	 */
	protected $solutions_ad_manager;

	/**
	 * The basename of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	public $basename;
	
	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {

		$this->solutions_ad_manager = 'solutions-ad-manager';
		$this->version = '0.6.4';
		$this->basename = 'solutions-ad-manager/solutions-ad-manager.php';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Solutions_Ad_Manager_Loader. Orchestrates the hooks of the plugin.
	 * - Solutions_Ad_Manager_i18n. Defines internationalization functionality.
	 * - Solutions_Ad_Manager_Admin. Defines all hooks for the admin area.
	 * - Solutions_Ad_Manager_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solutions-ad-manager-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solutions-ad-manager-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-solutions-ad-manager-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-solutions-ad-manager-public.php';

		$this->loader = new Solutions_Ad_Manager_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Solutions_Ad_Manager_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Solutions_Ad_Manager_i18n();
		$plugin_i18n->set_domain( $this->get_solutions_ad_manager() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Solutions_Ad_Manager_Admin( $this->get_solutions_ad_manager(), $this->get_version() );
		//Register Post Type
		$this->loader->add_action( 'init', $this, 'Register_Ad_Post_Type' );
		$this->loader->add_action( 'init', $this, 'Register_Ad_Group_Taxonomy' );
		//Meta Boxes
		$this->loader->add_action( 'init', $plugin_admin, 'register_meta_boxes' );
		$this->loader->add_filter( 'cmb_meta_boxes', $plugin_admin, 'define_meta_boxes' );
		//Custom Columns
		$this->loader->add_filter( 'restrict_manage_posts', $plugin_admin, 'solutions_ad_manager_taxonomy_filters' );
		$this->loader->add_filter( 'manage_edit-solutions-ad-manager_columns', $plugin_admin, 'solutions_ad_manager_custom_columns' );
		$this->loader->add_action( 'manage_solutions-ad-manager_posts_custom_column', $plugin_admin, 'solutions_ad_manager_custom_columns_display', 10, 2 );
		$this->loader->add_filter( 'manage_edit-solutions-ad-manager_sortable_columns', $plugin_admin, 'solutions_ad_manager_custom_columns_sortable' );
		$this->loader->add_filter( 'request', $plugin_admin, 'solutions_ad_manager_custom_columns_sortable_orderby' );
		//Settings Page
		$this->loader->add_action( 'admin_init', $plugin_admin, 'solutions_ad_manager_settings_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'solutions_ad_manager_add_admin_menu' );
		$this->loader->add_filter( "plugin_action_links_".$this->basename, $plugin_admin, 'solutions_ad_manager_plugin_settings_link' );
		//Enqueue scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		//Widgets
		$this->loader->add_action( 'widgets_init', $this, 'register_solutions_ad_manager_widget' );
		//Add Support
		$this->loader->add_action( 'after_setup_theme', $this, 'custom_theme_setup' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Solutions_Ad_Manager_Public( $this->get_solutions_ad_manager(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'Solutions_Ad_Manager_Redirect' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//Widgets
		$this->loader->add_action( 'widgets_init', $this, 'register_solutions_ad_manager_widget' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_solutions_ad_manager() {
		return $this->solutions_ad_manager;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Solutions_Ad_Manager_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	
	/* ONLY ADD STUFF THAT NEEDS TO BE USED IN BOTH PUBLIC AND ADMIN AREA */
	/* REFERENCE WITH $this->functionname() */
	
	/**
	 * Registers the post type.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function Register_Ad_Post_Type() {
		$labels = array(
			'name'                => _x( 'Ads', 'Post Type General Name', 'solutions-ad-manager' ),
			'singular_name'       => _x( 'Ad', 'Post Type Singular Name', 'solutions-ad-manager' ),
			'menu_name'           => __( 'Ad Manager', 'solutions-ad-manager' ),
			'name_admin_bar'      => __( 'Ad', 'solutions-ad-manager' ),
			'parent_item_colon'   => __( 'Parent Ad:', 'solutions-ad-manager' ),
			'all_items'           => __( 'Ads', 'solutions-ad-manager' ),
			'add_new_item'        => __( 'Add New Ad', 'solutions-ad-manager' ),
			'add_new'             => __( 'Add New', 'solutions-ad-manager' ),
			'new_item'            => __( 'New Ad', 'solutions-ad-manager' ),
			'edit_item'           => __( 'Edit Ad', 'solutions-ad-manager' ),
			'update_item'         => __( 'Update Ad', 'solutions-ad-manager' ),
			'view_item'           => __( 'View Ad', 'solutions-ad-manager' ),
			'search_items'        => __( 'Search Ad', 'solutions-ad-manager' ),
			'not_found'           => __( 'Not found', 'solutions-ad-manager' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'solutions-ad-manager' ),
		);
		$args = array(
			'label'               => __( 'ad', 'solutions-ad-manager' ),
			'description'         => __( 'Ad', 'solutions-ad-manager' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'thumbnail', 'revisions' ),
			'taxonomies'          => array( 'solutions-ad-group' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 101,
			'menu_icon'           => 'dashicons-groups',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
		);
		register_post_type( 'solutions-ad-manager', $args );
	}

	/**
	 * Registers the post type.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function Register_Ad_Group_Taxonomy() {
		$labels = array(
			'name'                       => _x( 'Group', 'Taxonomy General Name', 'solutions-ad-manager' ),
			'singular_name'              => _x( 'Group', 'Taxonomy Singular Name', 'solutions-ad-manager' ),
			'menu_name'                  => __( 'Groups', 'solutions-ad-manager' ),
			'all_items'                  => __( 'All Groups', 'solutions-ad-manager' ),
			'parent_item'                => __( 'Parent Group', 'solutions-ad-manager' ),
			'parent_item_colon'          => __( 'Parent Group:', 'solutions-ad-manager' ),
			'new_item_name'              => __( 'New Group Name', 'solutions-ad-manager' ),
			'add_new_item'               => __( 'Add New Group', 'solutions-ad-manager' ),
			'edit_item'                  => __( 'Edit Group', 'solutions-ad-manager' ),
			'update_item'                => __( 'Update Group', 'solutions-ad-manager' ),
			'view_item'                  => __( 'View Group', 'solutions-ad-manager' ),
			'separate_items_with_commas' => __( 'Separate menus with commas', 'solutions-ad-manager' ),
			'add_or_remove_items'        => __( 'Add or remove menus', 'solutions-ad-manager' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'solutions-ad-manager' ),
			'popular_items'              => __( 'Popular Groups', 'solutions-ad-manager' ),
			'search_items'               => __( 'Search Groups', 'solutions-ad-manager' ),
			'not_found'                  => __( 'Not Found', 'solutions-ad-manager' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'solutions-ad-group', array( 'solutions-ad-manager' ), $args );
	}

	/**
	 * Registers the widget.
	 *
	 * @since     0.1.0
	 */
	public function register_solutions_ad_manager_widget() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solutions-ad-manager-widget.php';
		register_widget( 'Solutions_Ad_Manager_Random_From_Group_Widget' );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solutions-ad-manager-widget-display-specific.php';
		register_widget( 'Solutions_Ad_Manager_Specific_Widget' );
	}

	/**
	 * Adds theme support for thumbnails.
	 *
	 * @since     0.3.0
	 */
	public function custom_theme_setup() {
		add_theme_support( 'post-thumbnails', array( 'solutions-ad-manager' ) );
	}


}

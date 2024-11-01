<?php
/*
Plugin Name: VD Ratings
Plugin URI: vrajeshdave.wordpress.com
Description: by using this plugin ,you can rate your post,page,and anything..!
Version: 1.2
Author: Vrajesh Dave
Author URI: vrajeshdave.wordpress.com
License: GPLv2 or later
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if ( ! class_exists( 'Rating' ) ) {
	class Rating
	{		
		protected $tag = 'vd_ratings';	
		protected $name = 'Rating';
		protected $version = '1.2';
		public function __construct()
		{
			add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));			
			add_action( 'wp_ajax_vd_rating', array(&$this, 'vd_set_ratings'));
			add_action( 'wp_ajax_nopriv_vd_rating',array(&$this, 'vd_set_ratings'));
			add_action('wp_head', array(&$this, 'vd_custom_rate_color'));			
			add_filter('vd_client_ip','vd_get_client_ip',10,0);
			
		}	
		
		protected function _enqueue()
		{
			$plugin_path = plugin_dir_url( __FILE__ );
			$plugin_path_css = plugin_dir_url( __FILE__ ).'css/';
			$plugin_path_js = plugin_dir_url( __FILE__ ).'js/';
			if ( !wp_style_is($this->tag, 'enqueued' ) ) {
				wp_enqueue_style(
					$this->tag,
					$plugin_path_css . 'vd_rating_css.css',
					array(),
					$this->version
				);				
				wp_enqueue_style(
					$this->tag,
					$plugin_path_css . 'bootstrap-rating.css',
					array(),
					$this->version
				);
				if( !wp_style_is( 'dashicons' ) ){					
					wp_enqueue_style( 'dashicons' );
				}
			}
			if ( !wp_script_is($this->tag, 'enqueued' ) ) {
				if (!wp_script_is( 'jquery')) {				
					wp_enqueue_script( 'jquery' );
				}				
				wp_enqueue_script(
					$this->tag,
					$plugin_path_js . 'bootstrap-rating.js',				
					array( 'jquery' ),
					$this->version
				);
				wp_enqueue_script(
					'jquery-' . $this->tag.'tooltip',
					$plugin_path_js . 'tooltip.js',
					array( 'jquery'),
					'1.0' 
				);				
				wp_register_script(
					'jquery-' . $this->tag.'localize',
					$plugin_path_js . 'vd_rating_js.js',
					array( 'jquery'),
					'1.0' 
				);
				wp_enqueue_script('jquery-' . $this->tag.'localize');
				$options = array(
					 'ajaxurl' => admin_url( 'admin-ajax.php' )
				);
				wp_localize_script('jquery-' . $this->tag.'localize','vd_ratings_obj', $options );
			}
		}
		public function shortcode( $atts, $content = null )
		{
			extract( shortcode_atts( array(				
				'id' => false
			), $atts ) );			
			$this->_enqueue();		
			$rate = $this->_get_rating($id);					
			return $rate;
		}
		
		protected function _get_rating($id=false){
			$id = !$id ? get_the_ID() : $id;
			$rate = get_post_meta($id, '_vd_rate', true) ? get_post_meta($id, '_vd_rate', true) : 0;
			$readonly='';
			if(get_option('_vd_user_login')==1){				
				if(is_user_logged_in()){
					$readonly='';
				}else{
						$readonly='readonly';
				}					
				}
			$rate_content='<div id="vd_rate" class="container">				
							<input id="inputid" type="hidden" class="rating check" data-filled="dashicons dashicons-star-filled" data-empty="dashicons dashicons-star-empty" data-fractions="2" value="'.esc_attr($rate).'" data-id="'.esc_attr($id) .'" autocomplete="off" '.esc_attr($readonly).' />    
						</div>';
			return $rate_content;
		}
		
		public function vd_set_ratings(){
			$get_rate = (is_numeric($_REQUEST['rate']) && ((int)$_REQUEST['rate']>0))  ? $_REQUEST['rate']:0;
			$get_pid = (is_numeric($_REQUEST['pid']) && ((int)$_REQUEST['pid']>0))  ? $_REQUEST['pid']:0;
			
			$vd_user_id = get_current_user_id();
			if ($vd_user_id == 0) {
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$uid = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$uid = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$uid = $_SERVER['REMOTE_ADDR'];
				}
			} else {
				$uid=$vd_user_id ;
				/******Used to get listing of user rating********/
				$user_rate = get_user_meta($uid,'rating_once', true);			
				if(empty($user_rate)){ $user_rate = array(); }			
				if (in_array($get_pid,$user_rate)) {	
										
				}
				else{	
					array_push($user_rate,$get_pid);				
				}
				update_user_meta( $uid,'_vd_user_rate',$user_rate);
				
			}
			$post_rate = get_post_meta($get_pid,'_vd_post_rate',true);						
			$post_all_rate = get_post_meta($get_pid,'_vd_rate',true);			
			if(!empty($post_all_rate)){
				$new_all_rate = ($post_all_rate+$get_rate)/2;
				$new_all_rate =round($new_all_rate*2)/ 2;
				
			}else{
				$new_all_rate=$get_rate;
				$new_all_rate =round($new_all_rate*2)/ 2;
			}
			
			if(empty($post_rate)){ $post_rate = array(); }			
			if (array_key_exists($uid,$post_rate)){
				$post_rate[$uid]=$get_rate;
			}
			else{
				$post_rate[$uid]=$get_rate;				
			}
			update_post_meta($get_pid,'_vd_post_rate',$post_rate);		
			update_post_meta($get_pid,'_vd_rate',$new_all_rate);				
			
			die();
		}
		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init()
		{			
			$this->init_settings();			
		} 
		
		public function init_settings()
		{			
			wp_enqueue_script(
					'jquery-' . $this->tag.'adminjs',
					plugin_dir_url( __FILE__ ).'js/admin_settings.js',
					array( 'jquery'),
					'1.0' 
			);				
			register_setting('wp_plugin_template-group', '_vd_user_login');
			register_setting('wp_plugin_template-group', '_vd_rate_color');
		} 
		
		public function add_menu()
		{
			add_options_page(
				__('VD Ratings','vd_rate'), 
				__('VD Ratings','vd_rate'), 
				'manage_options', 
				'vd_rating_template', 
				array(&$this, 'vd_rating_settings_page'));
		} 
		
		public function vd_rating_settings_page()
		{
			if(!current_user_can('manage_options'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.','vd_rate'));
			}			
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		} 
		public function vd_custom_rate_color()
		{
		 	$vd_rate_color = get_option('_vd_rate_color') ? get_option('_vd_rate_color'):'#000000';
			?>
			<style>
			.dashicons.dashicons-star-filled{color:<?php echo esc_attr($vd_rate_color); ?>}
			.dashicons-star-empty{color:<?php echo esc_attr($vd_rate_color); ?>}
			</style>
			<?php
		}
	}
 }


if(class_exists('Rating'))
{	
	register_activation_hook(__FILE__, array('Rating', 'activate'));
	register_deactivation_hook(__FILE__, array('Rating', 'deactivate'));	
	$vd_rating_obj = new Rating();
		
	if(isset($vd_rating_obj))
	{		
		function plugin_settings_link($links)
		{ 
			$settings_link = '<a href="options-general.php?page=vd_rating_template">'.__('Settings','vd_rate').'</a>'; 
			array_unshift($links, $settings_link); 
			return $links; 
		}
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
	}

}
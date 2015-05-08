<?php
/**
 * Drag and drop room building and reservation plugin for WordPress
 *
 *
 *
 * @package   SBS
 * @author    nilove <nilovecse05@gmail.com> | faysal <faysal.haque@gmail.com>
 * @link      https://nilove.github.io | https://faysalhaque.github.io
 * @copyright 2014 http://uouapps.com
 *
 * @wordpress-plugin
 * Plugin Name:       Drag n Drop Room building and reservation 
 * Plugin URI:        http://uouapps.com/
 * Description:       Drag and drop room building and reservation plugin for WordPress
 * Version:           1.0.0
 * Author:            uouapps.com 
 * Author URI:        http://uouapps.com
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    add_action( 'plugins_loaded', array( 'Room_Drag', 'get_instance' ) );
    if( ! class_exists('Room_Drag') ){

        class Room_Drag{
            const VERSION = '1.0.0';
            protected $plugin_slug;
            private static $instance;
            protected $templates;

            public static function get_instance() {

                if( null == self::$instance ) {
                    self::$instance = new Room_Drag();
                } // end if

                return self::$instance;

            } // end getInstance

            
            private function __construct(){

                $this->plugin_locale = 'uou';

                define( 'UOU_RMD_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
                define( 'UOU_RMD_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
                define( 'UOU_PACKAGE_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
                define( 'UOU_RMD_URL_OBJECTS_RED',untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) )."/objects/red/");
                define( 'UOU_RMD_URL_OBJECTS_GREEN',untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) )."/objects/green/");
                define( 'UOU_RMD_URL_OBJECTS_ORANGE',untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) )."/objects/orange/");
                define( 'UOU_RMD_URL_OBJECTS_GREY',untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) )."/objects/grey/");
                require_once( UOU_RMD_DIR . '/includes/class-uou-load-template.php' );
                require_once( UOU_RMD_DIR . '/includes/class-uou-rmd-post-type.php');
                require_once( UOU_RMD_DIR . '/includes/class-wc-product-reserve.php');
                require_once( UOU_RMD_DIR . '/includes/class-uou-rmd-ajax-frontend-request.php');
                require_once( UOU_RMD_DIR . '/includes/uou-rmd-functions.php');
                require_once( UOU_RMD_DIR . '/includes/admin/class-uou-reserve-admin.php');
                add_action( 'init', array( $this, 'rmd_load_plugin_textdomain' ) );
                add_action( "add_meta_boxes", array($this,"rmd_add_meta_boxes_main" ) ); 
                add_action( "add_meta_boxes", array($this,"rmd_add_meta_boxes_schedule" ) ); 
                add_action( 'admin_enqueue_scripts', array( $this, 'rmd_admin_load_scripts' ) );
                add_action( 'wp_enqueue_scripts', array( $this , 'rmd_load_scripts' ) );
                add_action( 'save_post', array( $this, 'saveData' ));
                add_action( 'save_post', array( $this, 'saveScheduleData' )); 
                add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );
                add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ) );
                add_filter('template_include', array( $this, 'view_project_template') );
                
                add_action('wp_footer', array($this ,'rmd_html_frontendend') );
                
                add_filter( 'product_type_selector' , array( $this, 'add_selector_uou' ) );
                add_filter( 'product_type_options', array( $this, 'product_type_options' ) );
                // add_filter( 'product_type_options', array( $this, 'reserve_product_type_options' ) );
                add_filter('woocommerce_add_cart_item_data',array($this,'uou_add_item_data'),1,2);
                add_filter('woocommerce_get_cart_item_from_session', array($this, 'uou_get_cart_items_from_session'), 1, 3 );
                add_filter('woocommerce_checkout_cart_item_quantity', array($this, 'uou_add_user_custom_option_from_session_into_cart' ),1,3);  
                add_filter('woocommerce_cart_item_price', array($this, 'uou_add_user_custom_option_from_session_into_cart' ),1,3);
                add_action('woocommerce_add_order_item_meta', array( $this, 'uou_add_values_to_order_item_meta' ),1,2);
                add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'uou_remove_user_custom_data_options_from_cart'),1,1);
                add_action( 'woocommerce_new_order', array($this, 'uou_new_order') );
                add_action( 'woocommerce_loaded', array( $this, 'includes' ) );
                add_filter('woocommerce_single_product_image_html', array($this, 'woocommerce_single_product_image_thumbnail_html'),10);
                add_action('woocommerce_reserve_add_to_cart', array($this, 'add_to_cart'),30);
                $this->templates = array(
                    'custom-template.php' => __( 'Custom Template', $this->plugin_slug ),
                );
                $templates = wp_get_theme()->get_page_templates();
                $templates = array_merge( $templates, $this->templates );
            }
            
            public function uou_add_item_data($cart_item_data,$product_id)
            {
                global $woocommerce;
                session_start();

                if (isset($_SESSION['room_schedule'])) {
                    $option = $_SESSION['room_schedule'];
                    $new_value = array('wdm_user_custom_data_value' => $option);
                }
                if(empty($option))
                    return $cart_item_data;
                else
                {    
                    if(empty($cart_item_data))
                        return $new_value;
                    else
                        return array_merge($cart_item_data,$new_value);
                }
                unset($_SESSION['room_schedule']);
            }

            public function uou_get_cart_items_from_session($item,$values,$key)
            {
                if (array_key_exists( 'wdm_user_custom_data_value', $values ) )
                {
                    $item['wdm_user_custom_data_value'] = $values['wdm_user_custom_data_value'];
                }       
                return $item;
            }

            public function uou_add_user_custom_option_from_session_into_cart( $product_name, $values, $cart_item_key ) {
                
                $decoded_data=json_decode(stripcslashes($values['wdm_user_custom_data_value']),true);
                
                $schedule_data_str="";
                foreach ($decoded_data as $d) 
                {
                    $parts1=explode("T", $d["start"]);
                    $parts2=explode("T", $d["end"]);
                    $schedule_data_str.="<tr><td>{$d["title"]}</td><td><table><tr><td>Start :</td><td>{$parts1[0]} {$parts1[1]}</td></tr><tr><td>End :</td><td>{$parts2[0]} {$parts2[1]}</td></tr></table></td></tr>";
                }
                if(count($values['wdm_user_custom_data_value']) > 0)
                {
                    $return_string = $product_name . "";
                    $return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
                    $return_string .= $schedule_data_str;
                    $return_string .= "</table>"; 
                    return $return_string;
                }
                else
                {
                    return $product_name;
                }
            }

            public function uou_add_values_to_order_item_meta( $item_id, $values ) {
                global $woocommerce,$wpdb;
                $user_custom_values = $values['wdm_user_custom_data_value'];
                if(!empty($user_custom_values))
                {
                    $decoded_data=json_decode(stripcslashes($user_custom_values),TRUE);
                    $html_str="<table>";
                    $html_str.="<tr><td></td><td>Start</td><td>End</td></tr>";
                    foreach ($decoded_data as $key => $value) {
                        $parts1=explode("T", $value["start"]);
                        $parts2=explode("T", $value["end"]);
                        $html_str.="<tr><td>{$value["title"]}</td><td>{$parts1[0]} {$parts1[1]}</td><td>{$parts2[0]} {$parts2[1]}</td></tr>";
                       }   
                    $html_str.="</table>";   
                    wc_add_order_item_meta($item_id,'wdm_user_custom_data',$user_custom_values);
                    wc_add_order_item_meta($item_id,'wdm_user_custom_html',$html_str);  
                }
            }

            public function uou_remove_user_custom_data_options_from_cart( $cart_item_key ) {
                global $woocommerce;
                // Get cart
                $cart = $woocommerce->cart->get_cart();
                // For each item in cart, if item is upsell of deleted product, delete it
                foreach( $cart as $key => $values)
                {
                if ( $values['wdm_user_custom_data_value'] == $cart_item_key )
                    unset( $woocommerce->cart->cart_contents[ $key ] );
                }
            }

            public function uou_new_order($order_id) {
                global $woocommerce;
                $conflict=0;
                $cart = $woocommerce->cart->get_cart();
                foreach ($cart as $key => $value) {
                    if($this->rmd_check_resource($value["wdm_user_custom_data_value"]) == 1)
                    {
                        $conflict =1;
                        break;
                    }
                }
                if($conflict == 0)
                {
                    foreach ($cart as $key => $value) {
                        
                        $this->rmd_save_resource($value["wdm_user_custom_data_value"]);
                    }  
                     
                }
                else
                {
                    die("Error in procession request");
                }
                //
            }

            
            
            public function rmd_save_resource($room_schedule)
            {
            $post_data = sanitize_text_field($room_schedule);
            $post_data=json_decode(stripslashes($post_data),TRUE);

            foreach ($post_data as $key => $value) 
            {
                $exist_schedule = get_post_meta($value["resouce_id"], 'room_schedule', true );
                if($exist_schedule == '')
                {
                    $exist_schedule = array();
                }
                else
                {
                    $exist_schedule = json_decode($exist_schedule,TRUE);    
                }
                
                if($this->checkEventConflict($value["start"],$value["end"],$exist_schedule) == 0)
                {
                    $exist_schedule[]=$value;
                    $s = update_post_meta($value["resouce_id"], 'room_schedule', json_encode($exist_schedule) );
                }    
            }
            }

            public function checkOverlap($b1,$e1,$b2,$e2)
            {   
            $conflict=0;
            if($e2 > $b1 && $e1 > $b2)
            {
                $conflict=1;
            }
            return $conflict;
            }

            public function checkEventConflict($start_time,$end_time,$event_array)
            {
                $conflict=0;
                foreach ($event_array as $d)
                {
                    if($this->checkOverlap(strtotime($start_time), strtotime($end_time),strtotime($d["start"]),strtotime($d["end"])))
                    {
                        $conflict=1;
                    }
                }
                if($conflict == 0)
                {

                }
                return $conflict;
            }

            public function rmd_check_resource($room_schedule)
            {
                
            $conflict=0;    
            $post_data = sanitize_text_field($room_schedule);
            $post_data=json_decode(stripslashes($post_data),TRUE);
            
            foreach ($post_data as $key => $value) 
            {
                $exist_schedule = get_post_meta($value["resouce_id"], 'room_schedule', true );
                if($exist_schedule == '')
                {
                    $exist_schedule=array();
                }
                else
                {
                    $exist_schedule = json_decode($exist_schedule,TRUE);    
                }
                
                if($this->checkEventConflict($value["start"],$value["end"],$exist_schedule) == 1)
                {
                    $conflict=1;
                    
                }    
            }
            
            return $conflict;
            }


            public function woocommerce_single_product_image_thumbnail_html() {
                return " ";
            }
            


            public function add_to_cart() {
                wc_get_template( 'single-product/add-to-cart/reserve.php',$args = array(), $template_path = '', UOU_PACKAGE_TEMPLATE_PATH);
            }
            
           /**
             * Tweak product type options
             * @param  array $options
             * @return array
             */
            public function product_type_options( $options ) {
                $options['virtual']['wrapper_class'] .= ' show_if_reserve';
                return $options;
            }

            /**
             * Add extra product type options
             * @param  array $options
             * @return array
             */
            // public function reserve_product_type_options( $options ) {
            //     return array_merge( $options, array(
            //         'reserve_has_price' => array(
            //             'id'            => '_reserve_has_price',
            //             'wrapper_class' => 'show_if_reserve',
            //             'label'         => __( 'Has price', 'uou' ),
            //             'description'   => __( 'Enable this if this reservation product can be reserved by a customer defined custom price for any seat', 'uou' ),
            //             'default'       => 'no'
            //         )
            //     ) );
            // }
            
            public function add_selector_uou($types) {
    		$types[ 'reserve' ] = __( 'Reservation product', 'uou' );
    		return $types;
    	   }
            
            function saveScheduleData($post_id)
            {
                if ( ! isset( $_POST['myplugin_inner_schedule_box_nonce'] ) )
    			return $post_id;

    		    $nonce = $_POST['myplugin_inner_schedule_box_nonce'];

    		// Verify that the nonce is valid.
    		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_schedule_box' ) )
    			return $post_id;

    		// If this is an autosave, our form has not been submitted,
                    //     so we don't want to do anything.
    		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
    			return $post_id;

    		// Check the user's permissions.
    		if ( 'page' == $_POST['post_type'] ) {

    			if ( ! current_user_can( 'edit_page', $post_id ) )
    				return $post_id;
    	
    		} else {

    			if ( ! current_user_can( 'edit_post', $post_id ) )
    				return $post_id;
    		}

    		/* OK, its safe for us to save the data now. */

    		// Sanitize the user input.
    		$mydata = sanitize_text_field( $_POST['room_schedule'] );
    		update_post_meta( $post_id, 'room_schedule', $mydata );
            }
            
            
            function saveData($post_id)
            {
    		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) )
    			return $post_id;

    		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

    		// Verify that the nonce is valid.
    		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
    			return $post_id;

    		// If this is an autosave, our form has not been submitted,
                    //     so we don't want to do anything.
    		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
    			return $post_id;

    		// Check the user's permissions.
    		if ( 'page' == $_POST['post_type'] ) {

    			if ( ! current_user_can( 'edit_page', $post_id ) )
    				return $post_id;
    	
    		} else {

    			if ( ! current_user_can( 'edit_post', $post_id ) )
    				return $post_id;
    		}
    		$mydata = sanitize_text_field( $_POST['load_config'] );
    		update_post_meta( $post_id, 'load_config', $mydata );
                    $mydata = sanitize_text_field( $_POST['load_data'] );
    		update_post_meta( $post_id, 'load_data', $mydata );
                    $mydata = sanitize_text_field( $_POST['canvas_config'] );
    		update_post_meta( $post_id, 'canvas_config', $mydata );
                    $mydata = sanitize_text_field( $_POST['last_index'] );
    		update_post_meta( $post_id, 'last_index', $mydata );
                    $mydata = sanitize_text_field( $_POST['room_schedule'] );
    		update_post_meta( $post_id, 'room_schedule', $mydata );

            }

            public function rmd_load_scripts()
            {
                global $post;
                //if ( is_page_template( 'custom-template.php' ) ) 
                //{
                    wp_register_style( 'bootstrap-css', UOU_RMD_URL. '/assets/css/bootstrap.min.css', array(), false, 'all' );
                    wp_enqueue_style( 'bootstrap-css' );
                    wp_register_style( 'fullcalendar', UOU_RMD_URL. '/assets/css/fullcalendar.css', array(), false, 'all' );
                    wp_enqueue_style( 'fullcalendar' );

                    wp_register_style( 'date_time_css', UOU_RMD_URL. '/assets/css/jquery.datetimepicker.css', array(), false, 'all' );
                    wp_enqueue_style( 'date_time_css' );

                    wp_register_style( 'font-awesome', UOU_RMD_URL. '/assets/css/font-awesome.min.css', array(), false, 'all' );
                    wp_enqueue_style( 'font-awesome' );


                    wp_register_style( 'render-css', UOU_RMD_URL. '/assets/css/render.css', array(), false, 'all' );
                    wp_enqueue_style( 'render-css' );



                    wp_register_script( 'moment.min', UOU_RMD_URL. '/assets/js/moment.min.js', array(), false, true );
                    wp_enqueue_script( 'moment.min' );
                    wp_enqueue_script('jquery');
                    wp_register_script( 'atmf-bootstrap', UOU_RMD_URL. '/assets/js/bootstrap.js', array('jquery'), false, true );
                    wp_enqueue_script( 'atmf-bootstrap' );
                    wp_register_script( 'fullcalendar', UOU_RMD_URL. '/assets/js/fullcalendar.min.js', array(), false, true );
                    wp_enqueue_script( 'fullcalendar' );
                    // wp_register_script( 'date_time', UOU_RMD_URL. '/assets/js/jquery.datetimepicker.js', array(), false, true );
                    // wp_enqueue_script( 'date_time' );
                    
                    // wp_register_script( 'jquery-ui-js', UOU_RMD_URL. '/assets/js/jquery-ui-1.9.2.custom.min.js', array(), false, true );
                    // wp_enqueue_script( 'jquery-ui-js' );

                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'jquery-ui-datepicker' );

                    wp_register_script( 'render', UOU_RMD_URL. '/assets/js/render.js', array(), false, true );
                    wp_enqueue_script( 'render' );
                    wp_localize_script( 'render', 'rmd', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                    $params=array("page_width"=>"width",
                                 "page_height"=>"height",  
                      "background_image_panel"=>"background-image",
                        "background-position1"=>"background-position",
                        "background-position2"=>"background-position",
                        "background-position3"=>"background-position",
                        "background-position4"=>"background-position",
                        "background-position5"=>"background-position",
                        "background-position6"=>"background-position",
                        "background-position7"=>"background-position",
                        "background-position8"=>"background-position",
                                    "repeat-x"=>"background-repeat",
                                    "repeat-y"=>"background-repeat"
                                 );
                    wp_localize_script( 'render', 'params',$params);
                    $current_post_id=0;
                    if(isset($post->ID))
                    {
                        $current_post_id=$post->ID;
                    }
                    wp_localize_script( 'render', 'post',array("post_id"=>$current_post_id));
                    wp_localize_script( 'render', 'objects_color',array("red"=>UOU_RMD_URL_OBJECTS_RED,
                                                                        "green"=>UOU_RMD_URL_OBJECTS_GREEN,
                                                                        "orange"=>UOU_RMD_URL_OBJECTS_ORANGE,
                                                                        "grey"=>UOU_RMD_URL_OBJECTS_GREY    
                                                                        ));
                    
                //}
                
            }


            public function rmd_admin_load_scripts($hook){

                global $post;
                if( $hook == 'post.php' || $hook == 'post-new.php'){

                    
                    wp_register_style( 'bootstrap-css', UOU_RMD_URL. '/assets/css/bootstrap.min.css', array(), false, 'all' );
                    wp_enqueue_style( 'bootstrap-css' );

                    wp_register_style( 'jquery-ui-css', UOU_RMD_URL. '/assets/css/jquery-ui.min.css', array(), false, 'all' );
                    wp_enqueue_style( 'jquery-ui-css' );
                    
                
                    
                    wp_register_style( 'custom', UOU_RMD_URL. '/assets/css/custom.css', array(), false, 'all' );
                    wp_enqueue_style( 'custom' );
                    
                    
                    
                    wp_register_script( 'moment.min', UOU_RMD_URL. '/assets/js/moment.min.js', array(), false, true );
                    wp_enqueue_script( 'moment.min' );
                    
                    wp_enqueue_script('jquery');

                    wp_register_script( 'atmf-bootstrap', UOU_RMD_URL. '/assets/js/bootstrap.js', array(), false, true );
                    wp_enqueue_script( 'atmf-bootstrap' );

                    // wp_register_script( 'jquery-ui-js', UOU_RMD_URL. '/assets/js/jquery-ui-1.9.2.custom.min.js', array(), false, true );
                    // wp_enqueue_script( 'jquery-ui-js' );
                    
                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'jquery-ui-draggable' );
                    wp_enqueue_script( 'jquery-ui-droppable' );
                    wp_enqueue_script( 'jquery-ui-resizable' );
                    
                    
                    
                    wp_register_script( 'fullcalendar', UOU_RMD_URL. '/assets/js/fullcalendar.min.js', array(), false, true );
                    wp_enqueue_script( 'fullcalendar' );

                    wp_register_script( 'custom', UOU_RMD_URL. '/assets/js/custom.js', array(), false, true );
                    wp_enqueue_script( 'custom' );
                    wp_localize_script( 'custom', 'rmd', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                    
                    wp_enqueue_script('media-upload');
                    wp_enqueue_script('thickbox');

                }
                else
                {
                    wp_register_style( 'fullcalendar', UOU_RMD_URL. '/assets/css/fullcalendar.css', array(), false, 'all' );
                    wp_enqueue_style( 'fullcalendar' );
                
                    wp_register_script( 'moment.min', UOU_RMD_URL. '/assets/js/moment.min.js', array(), false, true );
                    wp_enqueue_script( 'moment.min' );

                    wp_register_script( 'fullcalendar', UOU_RMD_URL. '/assets/js/fullcalendar.min.js', array(), false, true );
                    wp_enqueue_script( 'fullcalendar' );

                    wp_register_script( 'trigger_calender', UOU_RMD_URL. '/assets/js/trigger_calender.js', array(), false, true );
                    wp_enqueue_script( 'trigger_calender' );    
                }
                
            }

            /**
             * Load text domain for translation
             *
             * @return void
             * @verison 1.0.0
             * @since   1.0.0
             */

            public function rmd_load_plugin_textdomain(){

                $domain = $this->plugin_slug;
                $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

                load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
                load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
            }

            public function rmd_add_meta_boxes_main( $post )
            {
                add_meta_box(
                    'drag_app',
                    'Block Option',
                    array( $this , 'rmd_drag_box'),
                    'product',
                    'normal',
                    'core'
                );
            }


            public function rmd_drag_box( $post )
            {
                $template_loader = new Uou_Load_Template();
                ob_start();
                $template = $template_loader->locate_template( 'drag.php' );

                if(is_user_logged_in() ){
                    include( $template );
                }
                echo ob_get_clean();
            }
            
            
            public function rmd_add_meta_boxes_schedule( $post )
            {
                add_meta_box(
                    'drag_app',
                    'Block Option',
                    array( $this , 'rmd_schedule_box'),
                    'resource',
                    'normal',
                    'core'
                );
            }
            
            
            public function rmd_schedule_box( $post )
            {
                $template_loader = new Uou_Load_Template();
                ob_start();
                $template = $template_loader->locate_template( 'schedule.php' );

                if(is_user_logged_in() ){
                    include( $template );
                }
                echo ob_get_clean();
                // echo '<a href="#" id="create_search_options">Create/Update Search Options</a>';
            }



             /**
             * Adds our template to the pages cache in order to trick WordPress
             * into thinking the template file exists where it doens't really exist.
             *
             * @param   array    $atts    The attributes for the page attributes dropdown
             * @return  array    $atts    The attributes for the page attributes dropdown
             * @verison	1.0.0
             * @since	1.0.0
             */

            public function register_project_templates( $atts ) {

                $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

                $templates = wp_cache_get( $cache_key, 'themes' );
                if ( empty( $templates ) ) {
                    $templates = array();
                } // end if

                wp_cache_delete( $cache_key , 'themes');
                $templates = array_merge( $templates, $this->templates );
                wp_cache_add( $cache_key, $templates, 'themes', 1800 );

                return $atts;

            } // end register_project_templates

            /**
             * Checks if the template is assigned to the page
             *
             * @version	1.0.0
             * @since	1.0.0
             */

            public function view_project_template( $template ) {

                global $post;

                if ( !isset( $post ) ) return $template;

                if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
                    return $template;
                } // end if

                $file = plugin_dir_path( __FILE__ ) . 'templates/' . get_post_meta( $post->ID, '_wp_page_template', true );

                if( file_exists( $file ) ) {
                    return $file;
                } // end if

                return $template;

            } 
            // end view_project_template
            public function get_locale() {
                return $this->plugin_slug;
            } // end get_locale
            
            public function rmd_html_frontendend(){

                $template_loader =  new Uou_Load_Template();
                ob_start();
                $template = $template_loader->locate_template( 'frontpopup.php' );

                //if(is_user_logged_in() ){
                    include( $template );
                //}
                echo ob_get_clean();

            }

        }
    }

} else {
    function uou_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'Please Install WooCommerce first before activating the Drag n Drop Room building and reservation. You can download WooCommerce from <a href="http://wordpress.org/plugins/woocommerce/">here</a>.' ); ?></p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'uou_admin_notice' );
}

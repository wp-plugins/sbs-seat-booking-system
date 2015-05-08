<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class Uou_Careers_Admin {

    public function __construct(){
        include_once( UOU_RMD_DIR . '/includes/vendor/wrapper/class.settingsapi.php' );

        $this->load_settings();

        add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_tab' ), 5 );
        add_action( 'woocommerce_product_write_panels', array( $this, 'booking_panels' ) );


        add_action( 'woocommerce_process_product_meta', array( $this,'save_product_data' ), 20 );



    }

    /**
     * Show the booking tab
     */
    public function add_tab() {
      include( 'views/html-reserve-tab.php' );
    }

    /**
     * Show the booking panels views
     */
    public function booking_panels() {
      global $post;

      $post_id = $post->ID;

      wp_enqueue_script( 'wc_bookings_writepanel_js' );

      include( 'views/html-reserve-pricing.php' );
    }

    /**
     * Save Booking data for the product
     *
     * @param  int $post_id
     */
    public function save_product_data( $post_id ) {
      global $wpdb;

      $product_type         = empty( $_POST['product-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['product-type'] ) );
      $has_additional_costs = false;

      if ( 'reserve' !== $product_type ) {
        return;
      }

      // Save meta
      $meta_to_save = array(
        '_uou_reserve_cost'                       => 'float',
      );

      foreach ( $meta_to_save as $meta_key => $sanitize ) {
        $value = ! empty( $_POST[ $meta_key ] ) ? $_POST[ $meta_key ] : '';
        switch ( $sanitize ) {
          case 'float' :
            $value = floatval( $value );
            break;
          default :
            $value = sanitize_text_field( $value );
        }
        update_post_meta( $post_id, $meta_key, $value );
      }


      update_post_meta( $post_id, '_regular_price', '' );
      update_post_meta( $post_id, '_sale_price', '' );

      // Set price so filters work
      update_post_meta( $post_id, '_price', get_post_meta( $post_id, '_uou_reserve_cost', true ) + get_post_meta( $post_id, '_wc_base_cost', true ) );


    }


    public function load_settings(){
        $page = new Page('Room Settings', array('type' => 'menu'));

        $settings = array();


        // $settings['General'] = array();



        $fields = array();
        $fields = array(

            array(
               'label' => '', 
               'type'  => 'hidden_text',
               'name'  => 'schedule_event',
               'desc' => '<div id="calendar_"></div>'
            )
        );

       $settings['Schedule']['fields'] = $fields;



        new TSettingsApi( $page , $settings );
    }

}

new Uou_Careers_Admin();
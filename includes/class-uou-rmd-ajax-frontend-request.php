<?php


class Uou_Rmd_Ajax_Frontend_Request {




    public function __construct(){

        add_action( "wp_ajax_nopriv_rmd_process", array ( $this, 'rmd_process' ) );
        add_action( "wp_ajax_rmd_process",array( $this,'rmd_process'));
        
        add_action( "wp_ajax_nopriv_rmd_create_resource", array ( $this, 'rmd_create_resource' ) );
        add_action( "wp_ajax_rmd_create_resource",array( $this,'rmd_create_resource'));
        
        
        add_action( "wp_ajax_nopriv_rmd_save_resource", array ( $this, 'rmd_save_resource' ) );
        add_action( "wp_ajax_rmd_save_resource",array( $this,'rmd_save_resource'));
        
        add_action( "wp_ajax_nopriv_rmd_get_schedule", array ( $this, 'rmd_get_schedule' ) );
        add_action( "wp_ajax_rmd_get_schedule",array( $this,'rmd_get_schedule'));

        add_action( "wp_ajax_nopriv_rmd_saveIn_session", array ( $this, 'saveIn_session' ) );
        add_action( "wp_ajax_rmd_saveIn_session",array( $this,'saveIn_session'));


        add_action( 'woocommerce_before_calculate_totals', array( $this, 'uou_add_custom_price'));
    }


    /*-------------------------------------------------------------------------
     START UOU BOOKING ADD CUSTOM PRICE
    ------------------------------------------------------------------------- */

    public function uou_add_custom_price( $cart_object ) {

        foreach ( $cart_object->cart_contents as $key => $value ) {
            $temp = $value['wdm_user_custom_data_value'];

            $decode = json_decode(stripslashes($temp), true);
            $custom_price = 0;
            
            foreach ($decode as $k => $v) {
                $custom_price += $v['price'];
            }

            $value['data']->price = $custom_price;

        }
    }


    public function saveIn_session()
    {

        $post_data = sanitize_text_field($_POST['room_schedule']);
        session_start();
        $_SESSION['room_schedule'] = $post_data;
        print_r($_SESSION);
        die();
    }
    
    public function rmd_get_schedule()
    {  
        $room_schedule = get_post_meta( $_POST["resouce_id"], 'room_schedule', true );
        if($room_schedule != "")
        {
            $room_schedule=  json_decode($room_schedule,TRUE);
        }
        else
        {
            $room_schedule=array();
        }
        echo json_encode(array("schedule"=>$room_schedule));
        wp_die();
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
        return $conflict;
    }
    public function rmd_save_resource()
    {
        $post_data = sanitize_text_field($_POST['room_schedule']);
        $post_data=json_decode(stripslashes($post_data),TRUE);
        print_r($post_data);
        foreach ($post_data as $key => $value) 
        {
            $exist_schedule = get_post_meta($value["resouce_id"], 'room_schedule', true );
            $exist_schedule = json_decode($exist_schedule,TRUE);
            if($this->checkEventConflict($value["start"],$value["end"],$exist_schedule) == 0)
            {
                $exist_schedule[]=$value;
                update_post_meta($value["resouce_id"], 'room_schedule', json_encode($exist_schedule));
            }    
        }
    }

    public function rmd_process(){

        $value1 = get_post_meta($_POST["post_id"], 'load_config', true );
        $value2 = get_post_meta($_POST["post_id"], 'load_data', true );
        $value3 = get_post_meta($_POST["post_id"], 'canvas_config', true );
        $room_schedule = get_option('schedule','schedule_event');
        $rs=json_decode($value2,TRUE);
        //print_r($rs);
        $resource_id=array();
        foreach ($rs as $key => $value) 
        {
              if(isset($value["resource_id"]) && $value["resource_id"]!="")
              {
                $resource_id[]=$value["resource_id"];  
              }
        }
        $resource_schedule=array();
        if(!empty($resource_id))
        {
            $resource_id=implode(",",$resource_id);
            global $wpdb;
            $table_name = $wpdb->prefix . 'postmeta';
            $sql="SELECT * FROM {$table_name} WHERE post_id in({$resource_id})  and meta_key='room_schedule'";
            $results = $wpdb->get_results( $sql, ARRAY_N );
            foreach ($results as $key => $value) 
            {
                $resource_schedule[$value[1]]=json_decode($value[3],TRUE);
            }
        }

        global $woocommerce;
        $cart = $woocommerce->cart->get_cart();
        if(!empty($cart))
        {
            $inserted_resource=array();
            $group_by_resource=array();
            foreach( $cart as $key => $values)
            {
                if(isset($values['wdm_user_custom_data_value']))
                {
                    $decoded_data=json_decode(stripcslashes($values['wdm_user_custom_data_value']),TRUE);
                    foreach ($decoded_data as $key => $value) 
                    {
                        $group_by_resource[$value["resouce_id"]][]=$value;
                    }
                }
            }
            if(!empty($group_by_resource))
            {
                foreach ($group_by_resource as $key => $value) 
                {
                    if(isset($resource_schedule[$key]))
                    {
                        foreach ($value as $d) 
                        {
                            $resource_schedule[$key][]=$d;
                        }    
                    }
                    else
                    {
                        $resource_schedule[$key]=$value;
                    }
                }
            }
        }
        
        //print_r($resource_schedule);
        echo json_encode(array(
                        "load_config"=>json_decode($value1,TRUE),
                          "load_data"=>json_decode($value2,TRUE),
                      "canvas_config"=>json_decode($value3,TRUE),
                      "room_schedule"=>json_decode($room_schedule['schedule_event'],TRUE),
                      "resource_schedule"=>$resource_schedule
        ));

        wp_die();
    }
    
    
    public function rmd_create_resource()
    {
        $resource = array(
          'post_title'    => $_POST["widget_title"],
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_type'     => 'resource'
        );
        if($_POST["resource_id"] == "")
        {
            $resource_id=wp_insert_post($resource);
        }
        else
        {
            $resource_id=$_POST["resource_id"];
            $resource['ID']=$_POST["resource_id"];
            wp_update_post( $resource );
        }
        echo json_encode(array("resource_id"=>$resource_id));
        wp_die();
        
    }

}


new Uou_Rmd_Ajax_Frontend_Request();



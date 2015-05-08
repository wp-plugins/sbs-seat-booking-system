<?php



class Uou_Rmd_Post_Types {

    public function __construct(){

        include_once( UOU_RMD_DIR . '/includes/vendor/cuztom/cuztom.php' );

        $this->create_post_type();
    }


    public function create_post_type(){

            $product  = new Cuztom_Post_Type( 'product', array(
               /* "menu_position" => 25,
                'has_archive' => true,
                'supports' => array('title', 'editor','thumbnail' ,'excerpt'),*/
                'rewrite' => false,
            ) );
            
            $rosource  = new Cuztom_Post_Type( 'resource', array(
               /* "menu_position" => 25,
                'has_archive' => true,
                'supports' => array('title', 'editor','thumbnail' ,'excerpt'),*/
                'rewrite' => false,
            ) );    
                


    }

}

new Uou_Rmd_Post_Types();



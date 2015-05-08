<style>
    .widget
    {
        min-width: 1% !important;
    }
    .page_property
    {
        width: 60px;
        height: 34px;
    }
    .position_radio
    {
        margin-right: 15px !important;
    }
    .setting_title
    {
        padding-left: 40px !important;
        cursor:pointer !important;
    }
    #dropdiv
    {
        border: 1px solid grey;
        border-radius: 5px;
        
    }
    .wrp {
		margin: 40px 10px;
		padding: 0;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		font-size: 14px;
	}
    #calendar {
		max-width: 900px;
		margin: 0 auto;
	}
</style>
<div id="accordion">
<h3 class="setting_title">Objects</h3>
<div>
    <?php
    $json_data_object=file_get_contents(UOU_RMD_URL."/objects.json"); 
    $json_data_object=json_decode($json_data_object,true);
    $objects_array=array();
    foreach ($json_data_object as $key => $value) 
    {
        $objects_array[$key]=UOU_RMD_URL."/objects/grey/{$value}";
    }
    
    ?>
<div class="widget_list">
                <?php 
                foreach ($objects_array as $d)
                {
                    ?>
                    <img src='<?=$d;?>' class='to_drag'/>
                    <?php
                }
                ?>
            <div class="clear"></div>
            </div>
</div>



<h3 class="setting_title">Canvas settings</h3>
<div>
    <div id="page_config_panel"> 
            <input data="width" placeholder="width" class="page_property page_config" type="text" id="page_width"/>*<input data="height" placeholder="height" class="page_property page_config" type="text" id="page_height">&nbsp;<input class="btn btn-default" type="button" name="bg_image" id="upload_image_button" value="Background Image"/>
            <input type="hidden" data="background-image" id="background_image_panel" value="none;" class="page_config">
            <br/><br/>
            Background-position:<br/>
            <table>
                <tr>
                    <td><input data='background-position' id="background-position1" class="position_radio page_config" value="left top" type="radio" name="bg_position"></td>
                    <td>
                        Left Top
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position2" class="position_radio page_config" value="left bottom" type="radio" name="bg_position"></td>
                    <td>
                        left bottom
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position3" class="position_radio page_config" value="right top" type="radio" name="bg_position"></td>
                    <td>
                        right top
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position4" class="position_radio page_config" value="right center" type="radio" name="bg_position"></td>
                    <td>
                        right center
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position5" class="position_radio page_config" value="right bottom" type="radio" name="bg_position"></td>
                    <td>
                        right bottom
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position6" class="position_radio page_config" value="center top" type="radio" name="bg_position"></td>
                    <td>
                        center top
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position7" class="position_radio page_config" value="center center" type="radio" name="bg_position"></td>
                    <td>
                        center center
                    </td>
                </tr>
                <tr>
                    <td><input data='background-position' id="background-position8" class="position_radio page_config" value="center bottom" type="radio" name="bg_position"></td>
                    <td>
                        center bottom
                    </td>
                </tr>
            </table>
            Repeat X <input data='background-repeat' value='repeat-x' type="checkbox" name="repeat-x" id="repeat-x" class="page_config"/>&nbsp;
            Repeat y <input data='background-repeat' value='repeat-y' type="checkbox" name="repeat-y" id="repeat-y" class="page_config"/>
    </div>
</div>
<h3 style="display:none;" id="wd_settings" class="setting_title edit_panel">Widget Settings</h3>
<div style="display:none;" class="edit_panel"> 
                <h2>Edit widget</h2>
                <input type="hidden" id="widget_id" />
                <input type="hidden" class="to_select" id="resource_id" />
                <label for="widget_title"><?php _e('Title', 'uou'); ?></label>
                <input class="span2 margin-bottom to_select form-control" type="text" id="widget_title" placeholder="Title"/>
                
                <label for="widget_price"><?php _e('Price', 'uou'); ?></label>
                <input class="span2 margin-bottom to_select form-control" type="number" value="0" id="widget_price" />

                <input type="hidden" class="to_select"  id="widget_type" value="bookable_object"/>
                <label for="widget_description"><?php _e('Description', 'uou'); ?></label>
                <textarea class="to_select form-control" placeholder="Description" rows="5" id="widget_description"></textarea>
                <div class="clear"></div>
                <br/>
                <input id="save_this" class="btn btn-default" type="button" value="Save"/>
            </div>
</div>
<div class='wrapper' style="margin-top:10px;">
        
        <div class="clear"></div>
        <div id='dropdiv' class='drop_container float-left common_height'></div>
        
    </div>
<div class="clear"></div>
  
<?php 
$value1 = get_post_meta( $post->ID, 'load_config', true );
$value2 = get_post_meta( $post->ID, 'load_data', true );
$value3 = get_post_meta( $post->ID, 'canvas_config', true );
$value4 = get_post_meta( $post->ID, 'last_index', true );
$value5 = get_post_meta( $post->ID, 'room_schedule', true );
if($value3 == "")
{
    $rem_url=UOU_RMD_URL;
    $value3=  json_encode(array("page_width"=>"736",
                                "page_height"=>"736",
                                "background_image_panel"=>"none",
                                "repeat-x"=>"repeat-x",
                                "repeat-y"=>"repeat-y"            
                               )
                         );
}
wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );
echo '<input type="hidden" id="load_config" name="load_config" value="' . esc_attr( $value1 ) . '"/>';
echo '<input type="hidden" id="load_data" name="load_data" value="' . esc_attr( $value2 ) . '"/>';
echo '<input type="hidden" id="canvas_config" name="canvas_config" value="' . esc_attr( $value3 ) . '"/>';
echo '<input type="hidden" id="last_index" name="last_index" value="' . esc_attr( $value4 ) . '"/>';
echo '<input type="hidden" id="room_schedule" name="room_schedule" value="' . esc_attr( $value5 ) . '"/>';
?>
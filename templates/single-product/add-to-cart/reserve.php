<?php
/**
 * Simple product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

?>

<div class="clear"></div>
<div id="cart_table_container">
	<table width='100%' class="table">
        <thead>
          <tr>
            <th></th>
            <th>Start time</th>
            <th>End time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="schedule_table">
          
        </tbody>
      </table>
</div>

<form class="cart" method="post" enctype='multipart/form-data'>
<div class="reservation-form">
            

            <form class="default-form" action="index.html">
              <div class="row">
                
                <div class="col-md-4">
                    <div class="banner-search">
                    <div class="banner-search-inner">
                      <ul class="custom-list tab-content-list">
                        <li class="tab-content active">
                          <span class="calendar-input input-right" title="Departure">
                            <input id="book_date" type="text" name="departure" placeholder="Date" data-dateformat="">
                            <i class="fa fa-calendar"></i>
                          </span>
                        </li>
                      </ul>
                    </div>
                    <!-- end .banner-search-inner -->
                  </div>

                </div>

                
              
                <div class="col-md-4">
                  <div class="banner-search">
                    <div class="banner-search-inner">
                      <ul class="custom-list tab-content-list">
                        <li class="tab-content active">
                          <span class="select-box" title="Time">
                            <select id="start_in_time" class="time_drop_down" name="Time" data-placeholder="Start">
                              <option value="">START</option>
                              <?php 
                              for($i=0;$i<24;$i++)
                              {
                                $num_padded=sprintf("%02s",$i);
                                ?>
                                <option value="<?php echo "{$num_padded}:00";?>"><?php echo "{$num_padded}:00";?></option>
                                <?php 
                              }
                              ?>
                            </select>
                          </span>
                        </li>
                      </ul>
                    </div>
                    <!-- end .banner-search-inner -->
                  </div>
                  <!-- end .banner-search -->
                </div>

                <div class="col-md-4">
                  <div class="banner-search">
                    <div class="banner-search-inner">
                      <ul class="custom-list tab-content-list">
                        <li class="tab-content active">
                          <span class="select-box" title="Time">
                            <select id="end_in_time" class="time_drop_down" name="Time" data-placeholder="End">
                              <option value="">END</option>
                              <?php 
                              for($i=0;$i<24;$i++)
                              {
                                $num_padded=sprintf("%02s",$i);
                                ?>
                                <option value="<?php echo "{$num_padded}:00";?>"><?php echo "{$num_padded}:00";?></option>
                                <?php 
                              }
                              ?>
                            </select>
                          </span>
                        </li>
                      </ul>
                    </div>
                    <!-- end .banner-search-inner -->
                  </div>
                  <!-- end .banner-search -->
                </div>
                
            </form>
            <div class="clear"></div>
            <input type="hidden" id="start_time"/>
            <input type="hidden" id="end_time"/>
            <input type="hidden" id="reserve_price">
            <div id="room_canvas"></div>
              <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?><input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />
              <div style="margin:auto;width:200px;">
              <input type="hidden" id="currency_symbol" value="<?php echo get_woocommerce_currency_symbol(); ?>">
              <p class="booking_cost"><?php _e('Total Booking Cost : &nbsp;', 'uou') ?><?php echo get_woocommerce_currency_symbol(); ?><span class = "total_cost"></span></p>
              <button type="submit" class="single_add_to_cart_button"><i class="fa fa-check-square-o"></i> Make Reservation</button>
              </div>
            <!--<button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>-->

    <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
            </div> 
            
            
          </div>


	
	 

	 	
    <!--<input type='button' id='dummy_trigger' value="Save"/>-->
	</form>
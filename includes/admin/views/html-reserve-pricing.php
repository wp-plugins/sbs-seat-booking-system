<div id="reserve_pricing" class="panel woocommerce_options_panel">
	<div class="options_group">

		<?php woocommerce_wp_text_input( array( 'id' => '_uou_reserve_cost', 'label' => __( 'Base cost', 'uou' ), 'description' => __( 'One-off cost for the reservation as a whole.', 'uou' ), 'value' => get_post_meta( $post_id, '_uou_reserve_cost', true ), 'type' => 'number', 'desc_tip' => true, 'custom_attributes' => array(
			'min'   => '0.00',
			'step' 	=> '0.01'
		) ) ); ?>

	</div>
</div>
<?php
class WC_Product_Reserve extends WC_Product {

	public function __construct( $product ) {
		$this->product_type = 'reserve';
		parent::__construct( $product );
	}
}

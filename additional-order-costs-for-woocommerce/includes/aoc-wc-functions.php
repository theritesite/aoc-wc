<?php
function aoc_wc_calculate_addition_costs_on_order( $somecosts ) {
	$sum = (float) 0.00;
	$sum = floatval( array_sum( wp_list_pluck(  $somecosts , 'cost' ) ) );
	return $sum;
}

?>
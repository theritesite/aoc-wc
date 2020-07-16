window.AdditionalOrderCostsForWooCommerce = window.AdditionalOrderCostsForWooCommerce || {};

( function( window, document, $, plugin ) {
	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window = $( window );
		$c.body = $( document.body );
	};

	plugin.bindEvents = function() {
	};

	$( plugin.init );
}( window, document, jQuery, window.AdditionalOrderCostsForWooCommerce ) );

jQuery(document).ready(function( $ ) {
	'use strict';

	// Displays the tooltip to get the new cost of goods total for an order
	$('.inside').on( 'click', '.edit-additional-cost', function( e ) {
		e.preventDefault();
		$('.edit-aoc-tooltip').toggle(false)

		// $( e.target.id ).toggle(true);
		$('.additional-cost-row' ).toggle(true);
		$('.edit-aoc-buttons').toggle(true);
		$('.edit-additional-cost').toggle(false);
		$('.additional-cost-edit').toggle(true);
		$('.additional-cost-view').toggle(false);
		// $('.additional-cost-view-' + index ).toggle(false);
		// $('#edit-additional-costs-tooltip-' + index ).toggle(true)
		// $('#edit-additional-costs-tooltip-' + index ).focus()


		// $('#edit-total-cogs-tooltip').toggle(true);
		// $('#total-cogs-input').focus();

		if ( AOCWC.debug )
			console.log('clicked total tooltip!');
	});

	// Displays the tooltip to get the new cost of goods total for an order
	$('.inside').on( 'click', '.aoc-cancel-button', function( e ) {
		e.preventDefault();

		// $('.additional-cost-row' ).toggle(false);
		$('.edit-aoc-buttons').toggle(false);
		$('.edit-additional-cost').toggle(true);
		$('.additional-cost-edit').toggle(false);
		$('.additional-cost-view').toggle(true);

		if ( AOCWC.debug )
			console.log('clicked cancel in the tooltip!');
	});

	// Displays the tooltip to get the new cost of goods total for an order
	$('.inside').on( 'click', '.aoc-save-button', function( e ) {
		e.preventDefault();
		let debug = AOCWC.debug;
		// $('.edit-aoc-tooltip').toggle(false)

		var labels = $('input[name="aoc_label[]"]');
		var costs  = $('input[name="aoc_cost[]"]');
		var index = 0;
		var additional_cost_data = [];
		var error_at = [];

		labels.each( function() { 
			var aVal = $(this).val();
			console.log( aVal );
			if ( !aVal ) {
				if ( costs[index].value > 0 || costs[index].value < 0 ) {
					// Trigger error
					error_at.push( { 'index': index, 'code': 10 } );
				} // Skip else as that would be blanks on both anyways.
			}
			else if ( aVal ) {
				if ( costs[index].value == 0 ) {
					// Trigger error
					error_at.push( { 'index': index, 'code': 11 } );
				}
				else {
					// Need to store this
					additional_cost_data.push( { 'label': aVal, 'cost': costs[index].value } );
				}
			}
			index++;
		} );

		if ( debug )
			console.log( additional_cost_data );

		if ( error_at.length > 0 ) {
			// TODO: need to make error trigger that will go through and highlight the errored inputs with correct message.
			return;
		}

		// $( e.target.id ).toggle(true);
		let indexer = $(this).data( 'aoc' );
		$('.additional-cost-edit-' + indexer ).toggle(false);
		$('.additional-cost-view-' + indexer ).toggle(true);
		$('#edit-additional-costs-tooltip-' + indexer ).toggle(false)

		var data = {
			action: 'aoc_wc_set_costs',
			security: AOCWC.nonce,
			post_id: $('#post_ID').val(),
			aoc: additional_cost_data,
		};
		
		$.post( ajaxurl, data, function(response)  {
			if ( debug )
				console.log("Additional Costs sent!");
			$(this).addClass('loading');
			$(this).attr('disabled', true);
		})
		.done(function() {
			$(this).attr('disabled', false);
			$(this).removeClass('loading');
			
		})
		.fail(function() {
			if ( debug )
				console.log("failed");
		})
		.success(function(response) {
			if ( debug )
				console.log("success");
			
			let additional_labels = $('span[id="aoc-label-view[]"]');
			let additional_costs = $('span[id="aoc-cost-view[]"]');
			let index = 0;

			$('.additional-cost-edit').toggle(false);
			$('.edit-aoc-buttons').toggle(false);
			$('.edit-additional-cost').toggle(true);

			additional_labels.each( function() {
				if ( undefined !== response.payload.cost_data[index] ) {
					$(this).toggle(true);
					$(this).html( response.payload.cost_data[index].label );
				}
				else {
					$(this).toggle(false);
				}
				index++;
			});

			index = 0;
			additional_costs.each( function() {
				if ( undefined !== response.payload.cost_data[index] ) {
					$(this).toggle(true);
					$(this).html(
						'<span class="woocommerce-Price-currencySymbol">' + AOCWC.currency + '</span>' + response.payload.cost_data[index].cost
					);
				}
				else {
					$(this).toggle(false);
				}
				index++;
			});
		})
		.always(function() {
			
		});
	});
});

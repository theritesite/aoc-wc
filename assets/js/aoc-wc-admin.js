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

	var AddCostsToOrderHandler = function() {
		var self = this;

		self.displayErrors = self.displayErrors.bind( self );
		self.editCosts = self.editCosts.bind( self );
		self.cancelEdit = self.cancelEdit.bind( self );
		self.saveCosts = self.saveCosts.bind( self );
		$( '.inside' )
			.on( 'error_aoc_validation', this.displayErrors )
			.on( 'click', '.edit-aoc', this.editCosts )
			.on( 'click', '.aoc-cancel-button', this.cancelEdit )
			.on( 'click', '.aoc-save-button', this.saveCosts )
			.on( 'change', 'input.aoc-edit.aoc-error', this.toggleError );
	}

	AddCostsToOrderHandler.prototype.toggleError = function( e ) {
		console.log( 'toggling off' );
		var aVal = $(this).val();
		if ( aVal ) {
			console.log( 'in the statement' );
			$(this).removeClass( 'aoc-error' );
		}
	}

	// Displays the tooltip to get the new cost of goods total for an order
	AddCostsToOrderHandler.prototype.editCosts = function( e ) {
		e.preventDefault();
		$('.edit-aoc-tooltip').toggle(false)

		// $( e.target.id ).toggle(true);
		$('.aoc-row' ).toggle(true);
		$('.edit-aoc-buttons').toggle(true);
		$('.edit-aoc').toggle(false);
		$('.aoc-edit').toggle(true);
		$('.aoc-view').toggle(false);
		// $('.aoc-view-' + index ).toggle(false);
		// $('#edit-aocs-tooltip-' + index ).toggle(true)
		// $('#edit-aocs-tooltip-' + index ).focus()


		// $('#edit-total-cogs-tooltip').toggle(true);
		// $('#total-cogs-input').focus();

		if ( AOCWC.debug )
			console.log('clicked total tooltip!');
	}

	// Displays the tooltip to get the new cost of goods total for an order
	AddCostsToOrderHandler.prototype.cancelEdit = function( e ) {
		e.preventDefault();

		// $('.aoc-row' ).toggle(false);
		$('.edit-aoc-buttons').toggle(false);
		$('.edit-aoc').toggle(true);
		$('.aoc-edit').toggle(false);
		$('.aoc-view').toggle(true);

		if ( AOCWC.debug )
			console.log('clicked cancel in the tooltip!');
	}

	// Displays the tooltip to get the new cost of goods total for an order
	AddCostsToOrderHandler.prototype.saveCosts = function( e ) {
		e.preventDefault();
		let debug = AOCWC.debug;

		var labels = $('input[name="aoc_label[]"]');
		var costs  = $('input[name="aoc_cost[]"]');
		var index = 0;
		var additional_cost_data = [];
		var error_at = [];

		labels.each( function() { 
			var aVal = $(this).val();
			if ( ! aVal ) {
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
			$( '.inside' ).trigger( 'error_aoc_validation', [ error_at ] );
			return;
		}

		let indexer = $(this).data( 'aoc' );
		$('.aoc-edit-' + indexer ).toggle(false);
		$('.aoc-view-' + indexer ).toggle(true);
		$('#edit-aocs-tooltip-' + indexer ).toggle(false)

		var data = {
			action: 'aoc_wc_set_costs',
			security: AOCWC.nonce,
			post_id: $('#post_ID').val(),
			aoc: additional_cost_data,
		};
		
		$.post( ajaxurl, data, function(response)  {
			if ( debug )
				console.log( 'Additional Costs sent!' );
			$(this).addClass('loading');
			$(this).attr('disabled', true);
		})
		.done( function() {
			$(this).attr('disabled', false);
			$(this).removeClass('loading');
			
		})
		.fail( function() {
			if ( debug )
				console.log( 'failed' );
		})
		.success( function( response ) {
			if ( debug )
				console.log( 'success' );
			
			let additional_labels = $('span[id="aoc-label-view[]"]');
			let additional_costs = $('span[id="aoc-cost-view[]"]');
			let index = 0;

			$('.aoc-edit').toggle(false);
			$('.edit-aoc-buttons').toggle(false);
			$('.edit-aoc').toggle(true);

			additional_labels.each( function() {
				if ( undefined !== response.payload.cost_data[index] ) {
					console.log( "cost was saved, adding the html and toggling on: this is the label" );
					$(this).toggle(true);
					$(this).html( response.payload.cost_data[index].label );
				}
				else {
					console.log( "this is in the else" );
					$(this).toggle(false);
				}
				index++;
			});

			index = 0;
			additional_costs.each( function() {
				if ( undefined !== response.payload.cost_data[index] ) {
					console.log( "cost was saved, adding the html and toggling on: this is the cost" );
					$(this).toggle(true);
					$(this).html(
						'<span class="woocommerce-Price-currencySymbol">' + AOCWC.currency + '</span>' + response.payload.cost_data[index].cost
					);
				}
				else {
					console.log( "this is in the second else" );
					$(this).toggle(false);
				}
				index++;
			});
		})
		.always( function() {
			
		});
	}

	AddCostsToOrderHandler.prototype.displayErrors = function( e, errors ) {
		let debug = AOCWC.debug;

		if ( errors.length > 0 ) {
			$.each( errors, function( index, value ) {

				if ( value.code == 10 ) {
					if ( debug ) {
						console.log( 'setting label error on index: ' + value.index );
						console.log( $('#aoc_label_' + value.index ) );
					}
					$('#aoc_label_' + value.index ).addClass('aoc-error');
				}
				if ( value.code == 11 ) {
					if ( debug ) {
						console.log( 'setting cost error on index: ' + value.index );
						console.log( $('#aoc_cost_' + value.index ) );
					}
					$('#aoc_cost_' + value.index ).addClass('aoc-error');
				}
			});
		}
	}

	new AddCostsToOrderHandler();
});

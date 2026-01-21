/**
 * Checkout JavaScript functionality
 * Handles product selection, coupon application, and checkout interactions
 */

jQuery(document).ready(function($) {
	'use strict';

	// Check if a product is already selected (from cart) on page load
	var $preSelectedRadio = $('input[name="product_selection"]:checked');
	if ($preSelectedRadio.length > 0) {
		console.log('Product already selected from cart, showing details');
		var productId = $preSelectedRadio.data('product-id');
		var productName = $preSelectedRadio.closest('.product-row').find('.product-name').text().trim();

		// Update selected product name in header
		$('.selected-product-name').text(productName);

		// For simple products, we don't need to populate plan tabs
		// populatePlanTabs(productId);

		// Show the card immediately (no animation on page load)
		$('#plan-details-card').show();
	}
	// Auto-select Elite account (or first product) if no product in cart
	else if (typeof checkout_vars !== 'undefined' && checkout_vars.has_product_in_cart === 'false') {
		console.log('No product in cart, products will be auto-added by PHP');
		// Products are now auto-added by PHP function, no need for JavaScript fallback
	}

	// Listen for checkout updates to unblock and ensure button visibility
	$(document.body).on('updated_checkout', function() {
		$('.woocommerce-checkout-review-order').unblock();
	});

	// Ensure button visibility when payment method changes
	$(document.body).on('payment_method_selected', function() {
		//ensurePlaceOrderButtonVisible();
	});

	// Handle coupon application
	$('.coupon-section button[name="apply_coupon"]').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();

		// Remove any existing error messages
		$('.coupon-error-message').remove();
		$('.coupon-error-wrapper').remove();
		$('.coupon-input').removeClass('has-error');

		var $input = $('.coupon-section #coupon_code');
		var couponCode = $input.val();

		console.log('Raw coupon code value:', couponCode);
		console.log('Value type:', typeof couponCode);
		console.log('Value length:', couponCode ? couponCode.length : 0);
		console.log('Input element:', $input);
		console.log('Number of inputs found:', $('.coupon-section #coupon_code').length);

		// Trim whitespace
		if (couponCode) {
			couponCode = couponCode.trim();
		}

		console.log('Trimmed coupon code:', couponCode);
		console.log('Trimmed length:', couponCode ? couponCode.length : 0);

		if (!couponCode || couponCode === '' || couponCode.length === 0) {
			// Show error message below input
			$('.coupon-input').addClass('has-error');
			$('.coupon-input').after('<span class="coupon-error-wrapper"><em class="coupon-error-message">Please enter a coupon code.</em></span>');
			console.log('Validation failed: empty coupon code');
			return;
		}

		console.log('Validation passed, proceeding with AJAX...');

		// Show loading
		$('.woocommerce-checkout-review-order').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		// Use WooCommerce's native AJAX for applying coupons
		var data = {
			security: wc_checkout_params.apply_coupon_nonce,
			coupon_code: couponCode
		};

		$.ajax({
			type: 'POST',
			url: wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ),
			data: data,
			dataType: 'html',
			success: function(response) {
				console.log('Coupon response received:', response);

				$('.woocommerce-checkout-review-order').unblock();

				// Check if response contains success message or "already applied" message
				var $response = $(response);
				var hasSuccess = $response.find('.wc-block-components-notice-banner.is-success').length > 0 ||
				                 response.indexOf('is-success') > -1 ||
				                 response.indexOf('applied successfully') > -1 ||
				                 response.indexOf('already applied') > -1;
				var hasError = $response.find('.wc-block-components-notice-banner.is-error').length > 0 ||
				               response.indexOf('is-error') > -1 ||
				               response.indexOf('does not exist') > -1;

				// If it says "already applied", treat it as success
				var alreadyApplied = response.indexOf('already applied') > -1;

				console.log('Has success:', hasSuccess, 'Has error:', hasError, 'Already applied:', alreadyApplied);

				if ((hasSuccess && !hasError) || alreadyApplied) {
					// Clear the input
					$('.coupon-section #coupon_code').val('');

					// Remove error styling
					$('.coupon-input').removeClass('has-error');
					$('.coupon-error-message').remove();

					// Show success message
					var successMsg = alreadyApplied ? '✓ Coupon already applied!' : '✓ Coupon applied successfully!';
					$('.coupon-input').after('<p class="coupon-success-message">' + successMsg + '</p>');

					// Remove success message after 3 seconds
					setTimeout(function() {
						$('.coupon-success-message').fadeOut(300, function() {
							$(this).remove();
						});
					}, 3000);

					// Trigger checkout update to refresh totals
					$('body').trigger('update_checkout', { update_shipping_method: false });

					console.log('Coupon applied successfully');
				} else {
					// Extract error message from response
					var errorMsg = 'Invalid coupon code.';

					if (hasError) {
						var $errorBanner = $response.find('.wc-block-components-notice-banner.is-error .wc-block-components-notice-banner__content');
						if ($errorBanner.length > 0) {
							errorMsg = $errorBanner.text().trim();
						}
					}

					// Show error message below input
					$('.coupon-input').addClass('has-error');
					$('.coupon-input').after('<span class="coupon-error-wrapper"><em class="coupon-error-message">' + errorMsg + '</em></span>');
					console.log('Coupon error:', errorMsg);
				}
			},
			error: function(xhr, status, error) {
				$('.woocommerce-checkout-review-order').unblock();
				console.error('Coupon AJAX error:', error);

				// Show error message below input
				$('.coupon-input').addClass('has-error');
				$('.coupon-input').after('<span class="coupon-error-wrapper"><em class="coupon-error-message">Error applying coupon. Please try again.</em></span>');
			}
		});
	});

	// Clear coupon error when user starts typing
	$(document).on('input', '.coupon-section #coupon_code', function() {
		$('.coupon-error-message').remove();
		$('.coupon-error-wrapper').remove();
		$('.coupon-input').removeClass('has-error');
	});

	// Handle payment method selection
	$('input[name="payment_method"]').on('change', function() {
		$('.payment-method-description').hide();
		$(this).closest('.payment-method').find('.payment-method-description').show();
	});

	// Terms and conditions validation
	$('form.checkout').on('checkout_place_order', function() {
		var termsChecked = $('#terms').is(':checked');

		if (!termsChecked) {
			// Scroll to terms section
			$('.terms-and-conditions-wrapper').addClass('terms-error');

			// Add error message if not already present
			if (!$('.terms-error-message').length) {
				$('.terms-and-conditions-wrapper').append('<p class="terms-error-message">* Please accept the terms and conditions to continue.</p>');
			}

			// Scroll to the error
			$('html, body').animate({
				scrollTop: $('.terms-and-conditions-wrapper').offset().top - 100
			}, 500);

			return false;
		}

		// Remove error styling if checked
		$('.terms-and-conditions-wrapper').removeClass('terms-error');
		$('.terms-error-message').remove();

		return true;
	});

	// Remove error when checkbox is checked
	$('#terms').on('change', function() {
		if ($(this).is(':checked')) {
			$('.terms-and-conditions-wrapper').removeClass('terms-error');
			$('.terms-error-message').remove();
		}
	});

	// Handle product selection
	$('input[name="product_selection"]').on('change', function() {
		var $radio = $(this);
		var productId = $radio.data('product-id');
		var productName = $radio.closest('.product-row').find('.product-name').text().trim();

		console.log('Product selection changed to:', productId, productName);

		// Update visual selection
		$('.product-row').removeClass('selected');
		$radio.closest('.product-row').addClass('selected');

		// Update selected product name in header
		$('.selected-product-name').text(productName);

		// Show loading on order summary
		$('.woocommerce-checkout-review-order').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		// AJAX call to update cart with new simple product
		$.ajax({
			type: 'POST',
			url: wc_checkout_params.ajax_url,
			data: {
				action: 'switch_product_plan',
				product_id: productId,
				security: wc_checkout_params.update_order_review_nonce
			},
			success: function(response) {
				console.log('Product switch response:', response);
				if (response.success) {
					// Trigger WooCommerce update checkout to refresh order summary
					$('body').trigger('update_checkout');
					console.log('Order summary updated for new product');
				} else {
					console.error('Failed to switch product:', response.data);
					alert('Error switching product: ' + response.data);
					$('.woocommerce-checkout-review-order').unblock();
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX error:', error);
				alert('Error: ' + error);
				$('.woocommerce-checkout-review-order').unblock();
			}
		});
	});

	// Make entire product card clickable
	$('.product-row').on('click', function(e) {
		// Don't trigger if clicking directly on the radio button (it will handle itself)
		if ($(e.target).is('input[type="radio"]')) {
			return;
		}

		// Find and trigger the radio button in this row
		var $radio = $(this).find('input[name="product_selection"]');
		if ($radio.length && !$radio.is(':checked')) {
			$radio.prop('checked', true).trigger('change');
		}
	});
});
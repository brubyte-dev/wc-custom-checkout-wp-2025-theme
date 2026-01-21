<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

// Define order button text
$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="checkout-container">
			<div class="checkout-wrapper">

				<!-- First Column -->
				<div class="checkout-column checkout-left">

					<!-- Billing Details -->
					<div class="checkout-section billing-details">
						<h3><?php esc_html_e( 'Billing Details', 'woocommerce' ); ?></h3>
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>

										<!-- Order Summary -->
					<div class="checkout-section order-summary">
						<h3><?php esc_html_e( 'Order Summary', 'woocommerce' ); ?></h3>

						<!-- Coupon Code -->
						<div class="coupon-section">
							<label for="coupon_code"><?php esc_html_e( 'Have a discount coupon?', 'woocommerce' ); ?></label>
							<div class="coupon-input">
								<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
								<button type="button" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
							</div>
						</div>

						<div class="order-summary-content">
							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action( 'woocommerce_checkout_order_review' ); ?>
							</div>
						</div>
					</div>
									<!-- Product Selection -->
					<div class="checkout-section product-selection">
						<h3><?php esc_html_e( 'Select Account', 'woocommerce' ); ?></h3>
						<div class="product-rows">
							<?php
							// Get available simple products
							$args = array(
								'post_type' => 'product',
								'posts_per_page' => -1,
								'tax_query' => array(
									array(
										'taxonomy' => 'product_type',
										'field'    => 'slug',
										'terms'    => 'simple',
									),
								),
							);

						$simple_products = get_posts( $args );
						$row_count = 0;
						
						// Collect all variations data for JavaScript
						$all_variations_data = array();
						
						// Track if any product is in cart and find Elite account
						$has_product_in_cart = false;
						$elite_product_id = null;
						$first_product_id = null;

						foreach ( $simple_products as $product_post ) :
								$product = wc_get_product( $product_post->ID );
								if ( ! $product || ! $product->is_type( 'simple' ) ) {
									continue;
								}

							$row_count++;
							if ( $row_count > 3 ) break; // Limit to 3 rows

							// Check if this product is currently in cart
							$current_product_id = $product->get_id();
							$is_current_product = false;
							$current_plan_type = '';
							
						// Track first product ID for fallback
						if ( $first_product_id === null ) {
							$first_product_id = $current_product_id;
						}
						
						// Check if this is the Elite account by slug
						$product_slug = $product_post->post_name;
						if ( $elite_product_id === null && $product_slug === 'elite' ) {
							$elite_product_id = $current_product_id;
						}
						
						// Check if this product is currently in cart (simplified for simple products)
						foreach ( WC()->cart->get_cart() as $cart_item ) {
							if ( $cart_item['product_id'] == $current_product_id ) {
								$is_current_product = true;
								$has_product_in_cart = true;
								break;
							}
						}
								
								// For simple products, no variations data needed
								$all_variations_data[ $current_product_id ] = array();
							?>
							<div class="product-row <?php echo $is_current_product ? 'selected' : ''; ?>" data-product-id="<?php echo esc_attr( $current_product_id ); ?>">
								<div class="product-info">
									<h4 class="product-name"><?php echo esc_html( $product->get_name() ); ?></h4>
									<div class="product-price">
										<?php echo $product->get_price_html(); ?>
									</div>
								</div>
								<div class="product-selector">
									<input type="radio" 
										   id="product_<?php echo esc_attr( $current_product_id ); ?>" 
										   name="product_selection" 
										   value="<?php echo esc_attr( $current_product_id ); ?>"
										   data-product-id="<?php echo esc_attr( $current_product_id ); ?>"
										   <?php echo $is_current_product ? 'checked' : ''; ?> />
									<label for="product_<?php echo esc_attr( $current_product_id ); ?>"></label>
								</div>
							</div>
						<?php endforeach; ?>
						</div>
					</div>
					
					<!-- Payment Methods -->
					<div id="payment-methods-wrapper">
						<div class="checkout-section payment-methods">
							<?php if ( WC()->cart->get_total( 'edit' ) > 0 ) : ?>
								<h3><?php esc_html_e( 'Payment Methods', 'woocommerce' ); ?></h3>
							<?php endif; ?>
							<?php woocommerce_checkout_payment(); ?>
						</div>
					</div>
					<script type="text/javascript">
						var productVariationsData = <?php echo json_encode( $all_variations_data ); ?>;
						<?php
						// Determine default product to select if cart is empty
						$default_product_id = null;
						if ( ! $has_product_in_cart ) {
							// Prefer Elite account, otherwise first product
							$default_product_id = $elite_product_id !== null ? $elite_product_id : $first_product_id;
						}
						?>
						var defaultProductId = <?php echo $default_product_id !== null ? json_encode( $default_product_id ) : 'null'; ?>;
						var hasProductInCart = <?php echo $has_product_in_cart ? 'true' : 'false'; ?>;
					</script>
				</div>


				<!-- Second Column -->
				<!-- <div class="checkout-column checkout-right">
	

					
				</div> -->

			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

	<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

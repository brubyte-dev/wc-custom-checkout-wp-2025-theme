<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Enqueues custom checkout styles.
if ( ! function_exists( 'twentytwentyfive_enqueue_checkout_styles' ) ) :
	/**
	 * Enqueues custom checkout.css on checkout pages.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_checkout_styles() {
		// Only load on checkout page
		if ( is_checkout() || is_cart() ) {
			wp_enqueue_style(
				'twentytwentyfive-checkout',
				get_parent_theme_file_uri( 'assets/css/checkout.css' ),
				array( 'twentytwentyfive-style' ), // Load after main theme styles
				wp_get_theme()->get( 'Version' ),
				'all'
			);
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_checkout_styles', 20 ); // Priority 20 to load after other styles

// Add inline style to ensure checkout CSS loads with high priority
if ( ! function_exists( 'twentytwentyfive_checkout_inline_style' ) ) :
	/**
	 * Adds inline critical CSS for checkout page to ensure immediate loading.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_checkout_inline_style() {
		if ( is_checkout() || is_cart() ) {
			$critical_css = '
				body.woocommerce-checkout {
					background: #f5f7fa !important;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
				}
				.checkout-container {
					max-width: 1400px;
					margin: 0 auto;
					padding: 40px 20px;
				}
				.checkout-section {
					//background: #ffffff !important;
					border-radius: 16px !important;
					padding: 32px !important;
					box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
					margin-bottom: 24px !important;
				}
			';
			wp_add_inline_style( 'twentytwentyfive-checkout', $critical_css );
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_checkout_inline_style', 21 ); // Priority 21 to load after checkout styles

// Enqueues custom checkout scripts.
if ( ! function_exists( 'twentytwentyfive_enqueue_checkout_scripts' ) ) :
	/**
	 * Enqueues custom checkout.js on checkout pages.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_checkout_scripts() {
		// Only load on checkout page
		if ( is_checkout() ) {
			wp_enqueue_script(
				'twentytwentyfive-checkout',
				get_parent_theme_file_uri( 'assets/js/checkout.js' ),
				array( 'jquery' ), // Dependencies
				wp_get_theme()->get( 'Version' ),
				true // Load in footer
			);

			// Localize script to pass PHP variables to JavaScript
			wp_localize_script( 'twentytwentyfive-checkout', 'checkout_vars', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'has_product_in_cart' => ! WC()->cart->is_empty() ? 'true' : 'false',
			) );
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_checkout_scripts', 22 ); // Priority 22 to load after other scripts

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

/*** custom code */

/**
 * CUSTOM PAYMENT TEMPLATE WITHOUT PLACE ORDER BUTTON
 */
function custom_checkout_payment_template() {
    if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->payment_gateways() ) {
        return;
    }

    if ( WC()->cart->needs_payment() ) {
        $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

        if ( ! empty( $available_gateways ) ) {
            $first_gateway = true;
            // Payment Methods
            echo '<div class="payment-methods-list">';
            foreach ( $available_gateways as $gateway ) {
                // Ensure at least one payment method is selected
                if ( $first_gateway && ! $gateway->chosen ) {
                    $gateway->chosen = true;
                    $first_gateway = false;
                }
                ?>
                <div class="payment-method">
                    <input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> />
                    <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                        <?php echo $gateway->get_title(); ?>
                        <?php echo $gateway->get_icon(); ?>
                    </label>
                    <?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
                        <div class="payment-method-description" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
                            <?php $gateway->payment_fields(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            echo '</div>';
        }
    }
}

/**
 * REPLACE DEFAULT PAYMENT ACTION WITH CUSTOM ONE
 */
remove_action( 'woocommerce_checkout_payment', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_payment', 'custom_checkout_payment_template', 20 );

/**
 * REMOVE PAYMENT FROM ORDER REVIEW (so it only shows in second column)
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

/**
 * SAVE TERMS AND CONDITIONS ACCEPTANCE TO ORDER META
 */
add_action( 'woocommerce_checkout_create_order', 'save_terms_acceptance_to_order', 10, 2 );

function save_terms_acceptance_to_order( $order, $data ) {
    if ( isset( $_POST['terms'] ) && $_POST['terms'] ) {
        $order->update_meta_data( '_terms_accepted', 'yes' );
        $order->update_meta_data( '_terms_accepted_date', current_time( 'mysql' ) );
        $order->update_meta_data( '_terms_accepted_ip', $_SERVER['REMOTE_ADDR'] );
    } else {
        $order->update_meta_data( '_terms_accepted', 'no' );
    }
}

/**
 * DISPLAY TERMS ACCEPTANCE IN ORDER ADMIN
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'display_terms_acceptance_in_admin', 10, 1 );

function display_terms_acceptance_in_admin( $order ) {
    $terms_accepted = $order->get_meta( '_terms_accepted' );
    $terms_date = $order->get_meta( '_terms_accepted_date' );
    $terms_ip = $order->get_meta( '_terms_accepted_ip' );
    
    echo '<div class="order-terms-acceptance" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid ' . ( $terms_accepted === 'yes' ? '#38b2ac' : '#e53e3e' ) . ';">';
    echo '<h3 style="margin-top: 0;">Terms & Conditions</h3>';
    
    if ( $terms_accepted === 'yes' ) {
        echo '<p style="color: #38b2ac; font-weight: 600; margin: 5px 0;">✓ Accepted</p>';
        if ( $terms_date ) {
            echo '<p style="margin: 5px 0; font-size: 13px;"><strong>Date:</strong> ' . esc_html( $terms_date ) . '</p>';
        }
        if ( $terms_ip ) {
            echo '<p style="margin: 5px 0; font-size: 13px;"><strong>IP Address:</strong> ' . esc_html( $terms_ip ) . '</p>';
        }
    } else {
        echo '<p style="color: #e53e3e; font-weight: 600; margin: 5px 0;">✗ Not Accepted</p>';
    }
    
    echo '</div>';
}

/**
 * 1️⃣ FORCE CLASSIC CHECKOUT (DISABLE BLOCK CHECKOUT)
 */
add_filter( 'woocommerce_is_checkout_block_enabled', '__return_false' );
add_filter( 'woocommerce_is_cart_block_enabled', '__return_false' );

/**
 * 2️⃣ REDIRECT ADD TO CART → CHECKOUT
 */
add_filter( 'woocommerce_add_to_cart_redirect', function () {
    return wc_get_checkout_url();
});

/**
 * 3️⃣ ALLOW ONLY ONE PRODUCT IN CART (AUTO-REPLACE)
 */
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed, $product_id, $quantity ) {

    if ( ! WC()->cart->is_empty() ) {
        WC()->cart->empty_cart();
    }

    return $passed;

}, 10, 3 );

/**
 * 4️⃣ LOCK QUANTITY TO 1 (NO MULTIPLE PURCHASES)
 */
add_filter( 'woocommerce_is_sold_individually', '__return_true' );

/**
 * 5️⃣ REMOVE SHIPPING COMPLETELY (VIRTUAL PRODUCTS)
 */
add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

/**
 * 6️⃣ HIDE SHIPPING FIELDS (SAFETY NET)
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {

    unset( $fields['shipping'] );

    return $fields;
});

/**
 * 7️⃣ OPTIONAL: REMOVE COMPANY FIELD (CLEANER CHECKOUT)
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {

    unset( $fields['billing']['billing_company'] );

    return $fields;
});

/**
 * 8️⃣ OPTIONAL: MAKE PHONE NOT REQUIRED
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {

    if ( isset( $fields['billing']['billing_phone'] ) ) {
        $fields['billing']['billing_phone']['required'] = false;
    }

    return $fields;
});

/**
 * 9️⃣ OPTIONAL: AUTO-COMPLETE ORDERS (DIGITAL ONLY)
 */
add_filter( 'woocommerce_payment_complete_order_status', function () {
    return 'completed';
});

/**
 * AJAX HANDLER FOR SWITCHING PRODUCTS (SIMPLE PRODUCTS VERSION)
 */
add_action( 'wp_ajax_switch_product_plan', 'switch_product_plan_callback' );
add_action( 'wp_ajax_nopriv_switch_product_plan', 'switch_product_plan_callback' );

function switch_product_plan_callback() {
    // Verify nonce
    if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'update-order-review' ) ) {
        wp_send_json_error( 'Security check failed' );
        return;
    }

    if ( ! isset( $_POST['product_id'] ) ) {
        wp_send_json_error( 'Missing product ID' );
        return;
    }

    $product_id = intval( $_POST['product_id'] );

    $cart = WC()->cart;
    if ( ! $cart ) {
        wp_send_json_error( 'Cart not available' );
        return;
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'simple' ) ) {
        wp_send_json_error( 'Invalid product' );
        return;
    }

    // Clear current cart and add the new simple product
    $cart->empty_cart();
    $cart->add_to_cart( $product_id, 1 );

    $cart->calculate_totals();

    // Set session data to trigger refresh
    WC()->session->set( 'refresh_totals', true );
    WC()->session->set( 'cart_totals_refresh', true );

    wp_send_json_success( array(
        'message' => 'Product switched successfully',
        'product_name' => $product->get_name(),
        'cart_hash' => $cart->get_cart_hash(),
        'cart_contents_count' => $cart->get_cart_contents_count(),
        'cart_total' => $cart->get_cart_total(),
    ) );
}

/**
 * CUSTOM FRAGMENT UPDATE FOR ORDER REVIEW
 */
add_filter( 'woocommerce_update_order_review_fragments', 'custom_order_review_fragments' );

function custom_order_review_fragments( $fragments ) {
    // Update the order review table
    ob_start();
    ?>
    <table class="shop_table woocommerce-checkout-review-order-table">
        <thead>
            <tr>
                <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
                <th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            do_action( 'woocommerce_review_order_before_cart_contents' );

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    ?>
                    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                        <td class="product-name">
                            <?php 
                            // Product name with "Account" label inline
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) );
                            echo ' <small style="color: var(--color-custom-yellow);" class="variation-plan-type">Account</small>';
                            ?>
                        </td>
                        <td class="product-total">
                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                        </td>
                    </tr>
                    <?php
                }
            }

            do_action( 'woocommerce_review_order_after_cart_contents' );
            ?>
        </tbody>
        <tfoot>
            <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
            
            <tr class="cart-subtotal">
                <th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
                <td><?php wc_cart_totals_subtotal_html(); ?></td>
            </tr>

            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                    <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                    <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

            <tr class="order-total">
                <th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
                <td><?php wc_cart_totals_order_total_html(); ?></td>
            </tr>

            <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
        </tfoot>
    </table>
    <?php
    $fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();
    
    return $fragments;
}

/**
 * Auto-add product to cart based on URL parameter or default to Elite
 * Clears old product if user changes product_slug to different product
 */
function auto_add_product_to_cart_on_checkout() {
    // Only run on checkout page
    if ( ! is_checkout() ) {
        return;
    }

    // Get product slug from URL parameter or default to elite
    $product_slug = isset( $_GET['product_slug'] ) ? sanitize_text_field( $_GET['product_slug'] ) : 'elite';

    // Get the product by slug
    $product_post = get_page_by_path( $product_slug, OBJECT, 'product' );

    if ( ! $product_post ) {
        return;
    }

    $product = wc_get_product( $product_post->ID );
    if ( ! $product || ! $product->is_type( 'simple' ) ) {
        return;
    }

    // Check if cart already has this product
    $cart_has_product = false;
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['product_id'] === $product->get_id() ) {
            $cart_has_product = true;
            break;
        }
    }

    // If cart has a different product, clear it first
    if ( ! WC()->cart->is_empty() && ! $cart_has_product ) {
        WC()->cart->empty_cart();
    }

    // Add the product to cart if not already there
    if ( ! $cart_has_product ) {
        WC()->cart->add_to_cart( $product->get_id(), 1 );
    }
}
add_action( 'template_redirect', 'auto_add_product_to_cart_on_checkout', 5 );

/**
 * 🔟 DEBUG MARKER (REMOVE LATER)
 */
add_action( 'wp_footer', function () {
    echo '<!-- WooCommerce prop firm filters active -->';
});
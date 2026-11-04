<?php
/**
 * Plugin Name: ALMORA Best Sellers
 * Description: Hand-picked best sellers. The option "almora_bestsellers_ids" (ordered list of product IDs) is the single source. Shortcode [almora_bestsellers] shows only these products in a grid (page /best-sellers/). Every product card (shop, categories, search, related, Elementor home rails) gets a star badge, the product page gets a small "Best Seller" chip. Elementor loop widgets with Query ID "almora_bestsellers" show exactly these products in this order (home page Best Sellers rail).
 */

defined( 'ABSPATH' ) || exit;

/* Ordered product IDs picked by the owner. */
function almora_bs_ids() {
	static $ids = null;
	if ( null === $ids ) {
		$o   = get_option( 'almora_bestsellers_ids', array() );
		$ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $o ) ) ) );
	}
	return $ids;
}

function almora_bs_is( $id ) {
	return in_array( (int) $id, almora_bs_ids(), true );
}

/* magicoon Light "star" (support-and-reviews/star.svg), path only */
function almora_bs_star_path() {
	return 'M18.542 8.271C18.954 7.87 19.101 7.281 18.924 6.734C18.746 6.186 18.281 5.794 17.71 5.712L13.228 5.064C13.065 5.04 12.923 4.939 12.851 4.791L10.909 0.874C10.641 0.335 10.101 0 9.499 0C8.897 0 8.356 0.335 8.089 0.874L6.147 4.792C6.075 4.939 5.933 5.041 5.77 5.065L1.288 5.713C0.717 5.796 0.252 6.187 0.074 6.735C-0.103 7.282 0.044 7.871 0.456 8.272L3.697 11.417C3.815 11.532 3.869 11.697 3.841 11.859L3.1 16.16C2.998 16.752 3.238 17.34 3.724 17.695C4.211 18.05 4.847 18.098 5.381 17.817L9.266 15.784C9.412 15.707 9.588 15.708 9.735 15.784L13.623 17.819C13.854 17.94 14.104 18 14.353 18C14.679 18 15.002 17.898 15.278 17.697C15.764 17.343 16.003 16.756 15.901 16.164L15.16 11.861C15.132 11.698 15.185 11.533 15.305 11.418L18.542 8.271ZM14.171 12.028L14.912 16.332C14.95 16.549 14.865 16.757 14.686 16.887C14.505 17.019 14.279 17.034 14.082 16.932L10.194 14.897C9.976 14.783 9.736 14.726 9.497 14.726C9.258 14.726 9.018 14.783 8.8 14.897L4.915 16.93C4.716 17.034 4.489 17.017 4.31 16.885C4.13 16.754 4.045 16.546 4.083 16.328L4.824 12.027C4.907 11.538 4.745 11.041 4.39 10.698L1.149 7.553C1.01 7.418 0.962 7.227 1.022 7.042C1.083 6.857 1.234 6.729 1.427 6.701L5.909 6.053C6.398 5.982 6.821 5.676 7.04 5.234L8.982 1.317C9.179 0.919 9.812 0.919 10.009 1.317L11.951 5.233C12.17 5.675 12.593 5.982 13.082 6.053L17.564 6.701C17.757 6.729 17.909 6.857 17.969 7.042C18.029 7.227 17.981 7.417 17.841 7.553L14.602 10.698C14.25 11.042 14.088 11.539 14.171 12.028Z';
}

function almora_bs_star_svg() {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path transform="translate(2.502 3.501)" d="' . almora_bs_star_path() . '"/></svg>';
}

/* Marker class on every product post (shop loop, Elementor loop items, single product wrapper). */
add_filter( 'post_class', function ( $classes, $extra, $post_id ) {
	if ( almora_bs_is( $post_id ) ) {
		$classes[] = 'alm-bestseller';
	}
	return $classes;
}, 10, 3 );

add_action( 'wp_head', function () {
	if ( ! almora_bs_ids() ) {
		return;
	}
	$star = 'data:image/svg+xml,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fff"><path transform="translate(2.502 3.501)" d="' . almora_bs_star_path() . '"/></svg>' );
	?>
<style id="almora-bestsellers">
ul.products li.product.alm-bestseller>.product-inner>.product-thumbnail::after,.e-loop-item.alm-bestseller .alm-card__media::after{content:"";position:absolute;z-index:2;top:8px;inset-inline-end:8px;width:34px;height:34px;border-radius:50%;background:#202020 url("<?php echo $star; // phpcs:ignore WordPress.Security.EscapeOutput ?>") center/22px 22px no-repeat;pointer-events:none}
.alm-bs-chip{display:inline-flex;align-items:center;gap:6px;margin:0 0 12px;padding:4px 12px 4px 10px;border-radius:100px;background:#202020;color:#fff;font-size:13px;font-weight:500;line-height:1.4;letter-spacing:.04em}
.alm-bs-chip svg{width:16px;height:16px;flex:none}
.alm-bs ul.products{justify-content:center}
.alm-bs-intro{margin:0 0 24px;font-size:18px;color:#202020}
@media(max-width:767px){body.page-best-sellers{overflow-x:clip}.alm-bs,.alm-bs-intro{padding-left:5px;padding-right:5px}.alm-bs-intro{font-size:16px}ul.products li.product.alm-bestseller>.product-inner>.product-thumbnail::after,.e-loop-item.alm-bestseller .alm-card__media::after{top:6px;inset-inline-end:6px;width:30px;height:30px;background-size:20px 20px}}
</style>
	<?php
}, 31 );

/* Small chip above the title on the product page (also in the quick view). */
add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( $product && almora_bs_is( $product->get_id() ) ) {
		echo '<div class="alm-bs-chip">' . almora_bs_star_svg() . '<span>Best Seller</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 2 );

/* Shortcode: only the picked products, in the picked order, in the theme's normal product grid. */
function almora_bs_render() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}
	$ids = almora_bs_ids();
	if ( ! $ids ) {
		return '';
	}
	$q = new WP_Query( array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $ids ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );

	global $product;
	$keep_product = $product;
	wc_setup_loop( array( 'columns' => 4, 'name' => 'almora_bestsellers' ) );
	ob_start();
	woocommerce_product_loop_start();
	while ( $q->have_posts() ) {
		$q->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	$html = ob_get_clean();
	wp_reset_postdata();
	wc_reset_loop();
	$product = $keep_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	return '<div class="alm-bs">' . $html . '</div>';
}
add_shortcode( 'almora_bestsellers', 'almora_bs_render' );

/* Elementor loop widgets with Query ID "almora_bestsellers" show exactly the picked products, in order.
   (Same reading of the widget's Query ID as almora-loop-ordering.php; the product loops do not fire elementor/query/{id}.) */
add_filter( 'elementor/query/query_args', function ( $args, $widget ) {
	if ( is_object( $widget ) && method_exists( $widget, 'get_settings' ) && 'almora_bestsellers' === $widget->get_settings( 'post_query_query_id' ) ) {
		$ids = almora_bs_ids();
		if ( $ids ) {
			$args['post__in']       = $ids;
			$args['orderby']        = 'post__in';
			$args['posts_per_page'] = count( $ids );
			unset( $args['post__not_in'] );
		}
	}
	return $args;
}, 30, 2 );

<?php
/**
 * Plugin Name: ALMORA product review cards
 * Description: On product pages each review is a card and the avatar is the product photo instead of the default gravatar silhouette.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'pre_get_avatar', function ( $avatar, $id_or_email, $args ) {
	if ( ! ( $id_or_email instanceof WP_Comment ) || 'review' !== $id_or_email->comment_type ) {
		return $avatar;
	}
	$product = wc_get_product( $id_or_email->comment_post_ID );
	if ( ! $product || ! $product->get_image_id() ) {
		return $avatar;
	}
	$size = 60;
	return wp_get_attachment_image( $product->get_image_id(), array( 300, 300 ), false, array( 'class' => 'avatar almora-review-product-img', 'alt' => esc_attr( $product->get_name() ), 'loading' => 'lazy' ) );
}, 10, 3 );

add_action( 'wp_head', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
<style id="almora-review-cards">
.woocommerce-Reviews .commentlist,#comments .commentlist{list-style:none;margin:0;padding:0;display:grid;gap:16px}
#comments .commentlist li.review{margin:0;padding:0;list-style:none}
#comments .commentlist li.review .comment_container{display:flex;flex-direction:row;align-items:flex-start;gap:16px;background:#fff;border:1px solid #e6e6e6;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
#comments .commentlist li.review .comment_container>img.avatar{position:static;float:none;flex:0 0 130px;width:130px;height:130px;max-width:none;object-fit:cover;border-radius:12px;border:1px solid #eee;background:#f6f6f6;margin:0}
#comments .commentlist li.review .comment-text{flex:1 1 auto;min-width:0;margin:0!important;padding:0!important;border:0!important}
#comments .commentlist li.review .comment-text .star-rating{margin:0 0 8px}
#comments .commentlist li.review .comment-text p.meta{margin:0 0 8px}
#comments .commentlist li.review .comment-text .description p{margin:0}
@media(max-width:600px){#comments .commentlist li.review .comment_container{padding:16px;gap:12px}#comments .commentlist li.review .comment_container>img.avatar{flex-basis:92px;width:92px;height:92px}}
</style>
	<?php
}, 99 );
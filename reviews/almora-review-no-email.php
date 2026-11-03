<?php
/**
 * Plugin Name: ALMORA review form without email
 * Description: Product review form asks only for rating, review text and name (no email). Reviews are published right away unless they contain a link. Blog comments keep the normal name + email rule.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* the form: drop the email field and the email note, clean labels */
add_filter( 'comment_form_fields', function ( $fields ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $fields;
	}
	unset( $fields['email'], $fields['url'] );
	if ( isset( $fields['author'] ) ) {
		$fields['author'] = preg_replace( '/(<label[^>]*>)[^&<]*/u', '${1}שם', $fields['author'], 1 );
	}
	if ( isset( $fields['cookies'] ) ) {
		$fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" /> <label for="wp-comment-cookies-consent">לשמור את השם שלי בדפדפן הזה לפעם הבאה</label></p>';
	}
	return $fields;
} );

add_filter( 'woocommerce_product_review_comment_form_args', function ( $args ) {
	$args['comment_notes_before'] = '';
	$args['title_reply']          = 'כתיבת ביקורת';
	$args['label_submit']         = 'שליחת ביקורת';
	return $args;
}, 99 );

/* gender-neutral wording for the theme's review button and title */
add_filter( 'gettext', function ( $translation ) {
	static $map = array( 'כתוב ביקורת' => 'כתיבת ביקורת', 'כתוב ביקורת מוצר' => 'כתיבת ביקורת', 'שלח ביקורת' => 'שליחת ביקורת' );
	return isset( $map[ $translation ] ) ? $map[ $translation ] : $translation;
} );

/* the submit: email is not required on products, the name still is */
add_filter( 'pre_option_require_name_email', function ( $pre ) {
	if ( isset( $_POST['comment_post_ID'] ) && 'product' === get_post_type( (int) $_POST['comment_post_ID'] ) ) {
		return '0';
	}
	return $pre;
} );

add_filter( 'preprocess_comment', function ( $data ) {
	if ( ! is_user_logged_in() && 'product' === get_post_type( (int) $data['comment_post_ID'] ) && '' === trim( (string) $data['comment_author'] ) ) {
		wp_die( 'נא למלא שם.', 'חסר שם', array( 'response' => 200, 'back_link' => true ) );
	}
	return $data;
}, 5 );
/* the same person may send several different reviews in a row: no 15-second flood block on products. The same text twice (a double click) is still blocked by WordPress. */
add_filter( 'wp_is_comment_flood', function ( $is_flood ) {
	if ( isset( $_POST['comment_post_ID'] ) && 'product' === get_post_type( (int) $_POST['comment_post_ID'] ) ) {
		return false;
	}
	return $is_flood;
}, 20 );

/* product reviews are published right away; a review with a link is held for approval (the usual spam sign). Spam and blocked words keep WordPress's own result. */
add_filter( 'pre_comment_approved', function ( $approved, $data ) {
	if ( 0 !== $approved && '0' !== $approved ) {
		return $approved;
	}
	if ( 'product' !== get_post_type( (int) $data['comment_post_ID'] ) ) {
		return $approved;
	}
	if ( preg_match( '#https?://|www\.|\.(com|co\.il|net|org|ru)\b#i', (string) $data['comment_content'] ) ) {
		return $approved;
	}
	return 1;
}, 20, 2 );

<?php
/**
 * Plugin Name: ALMORA review form without email
 * Description: Product review form asks only for rating, review text and name (no email). Guest reviews without email are always held for moderation (WordPress auto-approve needs an email). Blog comments keep the normal name + email rule.
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
	return $args;
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
/* the same person may send several reviews in a row: no duplicate block, no 15-second flood block on products */
add_filter( 'duplicate_comment_id', function ( $dupe_id, $commentdata ) {
	return 'product' === get_post_type( (int) $commentdata['comment_post_ID'] ) ? false : $dupe_id;
}, 10, 2 );

add_filter( 'wp_is_comment_flood', function ( $is_flood ) {
	if ( isset( $_POST['comment_post_ID'] ) && 'product' === get_post_type( (int) $_POST['comment_post_ID'] ) ) {
		return false;
	}
	return $is_flood;
}, 20 );
<?php
/**
 * Plugin Name: ALMORA reviews
 * Description: Elementor widget "ALMORA Reviews": the store's WooCommerce reviews with average, count, rating / sort / category filters and a photo per review (comment meta almora_review_photo). Sample reviews are stored as pending reviews with the meta almora_sample, so they never appear on product pages or in ratings; the widget shows them only when "Show sample reviews" is on.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Category order of the filter row (only categories that have reviews are shown). */
function almora_reviews_category_order() {
	return array( 'שרשראות', 'טבעות', 'עגילים', 'צמידים', 'תליונים', 'סטים' );
}

/** Reviews to show: approved store reviews, plus the pending sample reviews when asked. */
function almora_reviews_collect( $include_samples, $limit = 60 ) {
	$args = array(
		'post_type' => 'product',
		'type'      => 'review',
		'status'    => 'approve',
		'number'    => $limit,
		'orderby'   => 'comment_date_gmt',
		'order'     => 'DESC',
	);
	$comments = get_comments( $args );
	if ( $include_samples ) {
		$samples  = get_comments( array_merge( $args, array( 'status' => 'hold', 'meta_key' => 'almora_sample', 'meta_value' => '1' ) ) );
		$comments = array_merge( $comments, $samples );
	}

	$order   = almora_reviews_category_order();
	$reviews = array();
	foreach ( $comments as $c ) {
		$product = wc_get_product( $c->comment_post_ID );
		if ( ! $product || 'publish' !== $product->get_status() ) {
			continue;
		}
		$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
		$cat  = '';
		foreach ( $order as $name ) {
			if ( in_array( $name, (array) $cats, true ) ) {
				$cat = $name;
				break;
			}
		}
		$photo_id = (int) get_comment_meta( $c->comment_ID, 'almora_review_photo', true );
		$thumb    = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
		$reviews[] = array(
			'id'       => (int) $c->comment_ID,
			'name'     => $c->comment_author,
			'rating'   => max( 1, min( 5, (int) get_comment_meta( $c->comment_ID, 'rating', true ) ) ),
			'verified' => ( '1' !== (string) get_comment_meta( $c->comment_ID, 'almora_sample', true ) || current_user_can( 'edit_posts' ) ) && (bool) get_comment_meta( $c->comment_ID, 'verified', true ), // a sample review shows the tag only to the logged-in site team (test view), never to shoppers
			'text'     => wp_strip_all_tags( $c->comment_content ),
			'time'     => strtotime( $c->comment_date_gmt ),
			'date'     => mysql2date( 'j.n.Y', $c->comment_date ),
			'photo'    => $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium_large' ) : wp_get_attachment_image_url( $product->get_image_id(), 'medium_large' ),
			'srcset'   => (string) wp_get_attachment_image_srcset( $photo_id ? $photo_id : $product->get_image_id(), 'medium_large' ),
			'hasPhoto' => (bool) $photo_id,
			'product'  => array( 'name' => $product->get_name(), 'url' => get_permalink( $product->get_id() ), 'img' => $thumb ),
			'cat'      => $cat,
		);
	}
	/* "relevance": reviews with a photo first, then verified, then newest */
	usort( $reviews, function ( $a, $b ) {
		return array( $b['hasPhoto'], $b['verified'], $b['time'] ) <=> array( $a['hasPhoto'], $a['verified'], $a['time'] );
	} );
	foreach ( $reviews as $i => $r ) {
		$reviews[ $i ]['rel'] = $i;
	}
	return $reviews;
}

/** magicoon icons (Light; the star filled for ratings) */
function almora_reviews_icon( $name ) {
	$paths = array(
		'star-fill' => '<path transform="translate(2.502 3.501)" d="M18.542 8.271C18.954 7.87 19.101 7.281 18.924 6.734C18.746 6.186 18.281 5.794 17.71 5.712L13.228 5.064C13.065 5.04 12.923 4.939 12.851 4.791L10.909 0.874C10.641 0.335 10.101 0 9.499 0C8.897 0 8.356 0.335 8.089 0.874L6.147 4.792C6.075 4.939 5.933 5.041 5.77 5.065L1.288 5.713C0.717 5.796 0.252 6.187 0.074 6.735C-0.103 7.282 0.044 7.871 0.456 8.272L3.697 11.417C3.815 11.532 3.869 11.697 3.841 11.859L3.1 16.16C2.998 16.752 3.238 17.34 3.724 17.695C4.211 18.05 4.847 18.098 5.381 17.817L9.266 15.784C9.412 15.707 9.588 15.708 9.735 15.784L13.623 17.819C13.854 17.94 14.104 18 14.353 18C14.679 18 15.002 17.898 15.278 17.697C15.764 17.343 16.003 16.756 15.901 16.164L15.16 11.861C15.132 11.698 15.185 11.533 15.305 11.418L18.542 8.271Z"/>',
		'star'      => '<path transform="translate(2.502 3.501)" d="M18.542 8.271C18.954 7.87 19.101 7.281 18.924 6.734C18.746 6.186 18.281 5.794 17.71 5.712L13.228 5.064C13.065 5.04 12.923 4.939 12.851 4.791L10.909 0.874C10.641 0.335 10.101 0 9.499 0C8.897 0 8.356 0.335 8.089 0.874L6.147 4.792C6.075 4.939 5.933 5.041 5.77 5.065L1.288 5.713C0.717 5.796 0.252 6.187 0.074 6.735C-0.103 7.282 0.044 7.871 0.456 8.272L3.697 11.417C3.815 11.532 3.869 11.697 3.841 11.859L3.1 16.16C2.998 16.752 3.238 17.34 3.724 17.695C4.211 18.05 4.847 18.098 5.381 17.817L9.266 15.784C9.412 15.707 9.588 15.708 9.735 15.784L13.623 17.819C13.854 17.94 14.104 18 14.353 18C14.679 18 15.002 17.898 15.278 17.697C15.764 17.343 16.003 16.756 15.901 16.164L15.16 11.861C15.132 11.698 15.185 11.533 15.305 11.418L18.542 8.271ZM14.171 12.028L14.912 16.332C14.95 16.549 14.865 16.757 14.686 16.887C14.505 17.019 14.279 17.034 14.082 16.932L10.194 14.897C9.976 14.783 9.736 14.726 9.497 14.726C9.258 14.726 9.018 14.783 8.8 14.897L4.915 16.93C4.716 17.034 4.489 17.017 4.31 16.885C4.13 16.754 4.045 16.546 4.083 16.328L4.824 12.027C4.907 11.538 4.745 11.041 4.39 10.698L1.149 7.553C1.01 7.418 0.962 7.227 1.022 7.042C1.083 6.857 1.234 6.729 1.427 6.701L5.909 6.053C6.398 5.982 6.821 5.676 7.04 5.234L8.982 1.317C9.179 0.919 9.812 0.919 10.009 1.317L11.951 5.233C12.17 5.675 12.593 5.982 13.082 6.053L17.564 6.701C17.757 6.729 17.909 6.857 17.969 7.042C18.029 7.227 17.981 7.417 17.841 7.553L14.602 10.698C14.25 11.042 14.088 11.539 14.171 12.028Z"/>',
		'filter'    => '<path transform="translate(3.5 2.5)" d="M15 0L2 0C0.897 0 0 0.897 0 2L0 4.257C0 4.791 0.208 5.293 0.586 5.671L5.707 10.792C5.895 10.981 6 11.232 6 11.499L6 15.499C6 15.656 6.074 15.805 6.2 15.899L10.2 18.899C10.288 18.965 10.393 18.999 10.5 18.999C10.576 18.999 10.653 18.982 10.724 18.946C10.893 18.861 11 18.688 11 18.499L11 11.499C11 11.232 11.104 10.981 11.293 10.792L16.414 5.671C16.792 5.293 17 4.791 17 4.257L17 2C17 0.897 16.103 0 15 0ZM16 4.257C16 4.524 15.896 4.775 15.707 4.964L10.586 10.085C10.208 10.463 10 10.965 10 11.499L10 17.499L7 15.249L7 11.5C7 10.966 6.792 10.464 6.414 10.086L1.293 4.965C1.105 4.776 1 4.525 1 4.258L1 2C1 1.448 1.448 1 2 1L15 1C15.552 1 16 1.448 16 2L16 4.257Z"/>',
		'sort'      => '<path transform="translate(5.498 2.533)" d="M1.741 8.3L10.262 8.3C10.947 8.3 11.551 7.916 11.839 7.297C12.124 6.682 12.03 5.977 11.592 5.458L7.577 0.697C6.792 -0.233 5.21 -0.233 4.425 0.697L0.41 5.458C-0.028 5.977 -0.122 6.682 0.163 7.297C0.452 7.916 1.057 8.3 1.741 8.3ZM1.175 6.104L5.191 1.343C5.191 1.343 5.191 1.343 5.192 1.343C5.393 1.104 5.688 0.967 6.003 0.967C6.318 0.967 6.612 1.104 6.815 1.343L10.831 6.104C11.018 6.326 11.057 6.613 10.935 6.875C10.811 7.141 10.561 7.301 10.265 7.301L1.744 7.301C1.448 7.301 1.198 7.141 1.074 6.875C0.949 6.612 0.988 6.325 1.175 6.104ZM10.263 10.634L1.742 10.634C1.057 10.634 0.453 11.017 0.165 11.636C-0.12 12.251 -0.026 12.956 0.412 13.475L4.427 18.236C4.82 18.701 5.394 18.967 6.003 18.967C6.612 18.967 7.187 18.7 7.579 18.236L11.594 13.475C12.032 12.956 12.126 12.251 11.841 11.636C11.552 11.017 10.947 10.634 10.263 10.634ZM10.829 12.83L6.813 17.591C6.813 17.591 6.813 17.591 6.812 17.591C6.41 18.069 5.594 18.069 5.19 17.591L1.174 12.83C0.987 12.608 0.948 12.32 1.07 12.058C1.194 11.792 1.444 11.633 1.74 11.633L10.261 11.633C10.557 11.633 10.807 11.792 10.931 12.058C11.055 12.321 11.016 12.609 10.829 12.83Z"/>',
		'chevron'   => '<path transform="translate(7.5 9.501)" d="M8.854 0.853L4.854 4.853C4.756 4.951 4.628 4.999 4.5 4.999C4.372 4.999 4.244 4.95 4.146 4.853L0.146 0.853C-0.049 0.658 -0.049 0.341 0.146 0.146C0.341 -0.049 0.658 -0.049 0.853 0.146L4.499 3.792L8.145 0.146C8.34 -0.049 8.657 -0.049 8.852 0.146C9.047 0.341 9.049 0.657 8.854 0.853Z"/>',
	);
	return '<svg class="alm-rv-icon alm-rv-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}

function almora_reviews_stars( $n ) {
	$out = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= almora_reviews_icon( $i <= $n ? 'star-fill' : 'star' );
	}
	return $out;
}

/** The whole section: header (title, stars, average, count, two selects), category row, grid of cards. */
function almora_reviews_render( $settings ) {
	$title    = isset( $settings['title'] ) ? $settings['title'] : 'ביקורות';
	$per_page = max( 1, (int) ( isset( $settings['per_page'] ) ? $settings['per_page'] : 10 ) );
	$show_verified = ! isset( $settings['show_verified'] ) || 'yes' === $settings['show_verified'];
	// Real reviews first. Only while the store has none, the sample reviews show, under a title that says they are samples.
	$reviews  = almora_reviews_collect( false );
	if ( empty( $reviews ) && ! empty( $settings['include_samples'] ) && 'yes' === $settings['include_samples'] ) {
		$reviews = almora_reviews_collect( true );
		$title   = ! empty( $settings['sample_title'] ) ? $settings['sample_title'] : 'ביקורות לדוגמה';
	}
	if ( empty( $reviews ) ) {
		return; // nothing to show: the section hides itself
	}
	$count    = count( $reviews );
	$avg      = $count ? array_sum( wp_list_pluck( $reviews, 'rating' ) ) / $count : 0;
	$cats     = array_values( array_filter( almora_reviews_category_order(), function ( $c ) use ( $reviews ) {
		return in_array( $c, wp_list_pluck( $reviews, 'cat' ), true );
	} ) );

	ob_start();
	?>
	<div class="alm-rv" data-per-page="<?php echo esc_attr( $per_page ); ?>">
		<div class="alm-rv__top">
			<div class="alm-rv__summary">
				<h2 class="alm-rv__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $count ) : ?>
				<div class="alm-rv__score">
					<span class="alm-rv__stars" role="img" aria-label="<?php echo esc_attr( number_format( $avg, 1 ) . ' מתוך 5' ); ?>"><?php echo almora_reviews_stars( (int) round( $avg ) ); // phpcs:ignore ?></span>
					<b><?php echo esc_html( number_format( $avg, 1 ) ); ?></b>
					<span class="alm-rv__count">(<?php echo esc_html( $count ); ?> ביקורות)</span>
				</div>
				<?php endif; ?>
			</div>
			<div class="alm-rv__filters">
				<label class="alm-rv__field"><?php echo almora_reviews_icon( 'filter' ); // phpcs:ignore ?>
					<select class="alm-rv__rating" aria-label="סינון לפי דירוג">
						<option value="0">כל הדירוגים</option><option value="5">5 כוכבים</option><option value="4">4 כוכבים</option><option value="3">3 כוכבים</option><option value="2">2 כוכבים</option><option value="1">כוכב אחד</option>
					</select><?php echo almora_reviews_icon( 'chevron' ); // phpcs:ignore ?></label>
				<label class="alm-rv__field"><?php echo almora_reviews_icon( 'sort' ); // phpcs:ignore ?>
					<select class="alm-rv__sort" aria-label="מיון">
						<option value="rel">רלוונטיות</option><option value="new">החדשות ביותר</option><option value="high">דירוג גבוה</option><option value="low">דירוג נמוך</option>
					</select><?php echo almora_reviews_icon( 'chevron' ); // phpcs:ignore ?></label>
			</div>
		</div>
		<?php if ( $cats ) : ?>
		<div class="alm-rv__cats" role="group" aria-label="סינון לפי קטגוריה">
			<button type="button" data-cat="" aria-pressed="true">הכל</button>
			<?php foreach ( $cats as $c ) : ?><button type="button" data-cat="<?php echo esc_attr( $c ); ?>" aria-pressed="false"><?php echo esc_html( $c ); ?></button><?php endforeach; ?>
		</div>
		<?php endif; ?>
		<div class="alm-rv__grid">
			<?php foreach ( $reviews as $r ) : ?>
			<article class="alm-rv__card" data-rating="<?php echo esc_attr( $r['rating'] ); ?>" data-time="<?php echo esc_attr( $r['time'] ); ?>" data-rel="<?php echo esc_attr( $r['rel'] ); ?>" data-cat="<?php echo esc_attr( $r['cat'] ); ?>">
				<div class="alm-rv__photo"><img src="<?php echo esc_url( $r['photo'] ); ?>"<?php if ( ! empty( $r['srcset'] ) ) : ?> srcset="<?php echo esc_attr( $r['srcset'] ); ?>" sizes="(max-width: 767px) 92px, (max-width: 1200px) 30vw, 18vw"<?php endif; ?> alt="" loading="lazy"></div>
				<div class="alm-rv__body">
					<div class="alm-rv__head">
						<span class="alm-rv__name"><?php echo esc_html( $r['name'] ); ?></span>
						<?php if ( $show_verified && $r['verified'] ) : ?><span class="alm-rv__verified">מאומת</span><?php endif; ?>
						<span class="alm-rv__date"><?php echo esc_html( $r['date'] ); ?></span>
					</div>
					<div class="alm-rv__card-stars" role="img" aria-label="<?php echo esc_attr( $r['rating'] . ' מתוך 5' ); ?>"><?php echo almora_reviews_stars( $r['rating'] ); // phpcs:ignore ?></div>
					<p class="alm-rv__text"><?php echo esc_html( $r['text'] ); ?></p>
					<a class="alm-rv__product" href="<?php echo esc_url( $r['product']['url'] ); ?>"><img src="<?php echo esc_url( $r['product']['img'] ); ?>" alt="" loading="lazy"><span><?php echo esc_html( $r['product']['name'] ); ?></span></a>
				</div>
			</article>
			<?php endforeach; ?>
			<p class="alm-rv__empty" hidden>לא נמצאו ביקורות לסינון הזה.</p>
		</div>
	</div>
	<?php
	almora_reviews_script();
	return ob_get_clean();
}

/** Filters, sort and "per page" run in the browser on the cards above (printed once per page). */
function almora_reviews_script() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	?>
	<script>
	(function () {
		document.querySelectorAll('.alm-rv').forEach(function (root) {
			var grid = root.querySelector('.alm-rv__grid');
			var cards = Array.prototype.slice.call(grid.querySelectorAll('.alm-rv__card'));
			var empty = grid.querySelector('.alm-rv__empty');
			var rating = root.querySelector('.alm-rv__rating');
			var sort = root.querySelector('.alm-rv__sort');
			var perPage = parseInt(root.getAttribute('data-per-page'), 10) || 10;
			var cat = '';
			function list() {
				var min = +rating.value;
				var out = cards.filter(function (c) {
					return (!min || +c.dataset.rating === min) && (!cat || c.dataset.cat === cat);
				});
				var by = sort.value;
				out.sort(function (a, b) {
					if (by === 'new') { return b.dataset.time - a.dataset.time; }
					if (by === 'high') { return b.dataset.rating - a.dataset.rating || a.dataset.rel - b.dataset.rel; }
					if (by === 'low') { return a.dataset.rating - b.dataset.rating || a.dataset.rel - b.dataset.rel; }
					return a.dataset.rel - b.dataset.rel;
				});
				return out.slice(0, perPage);
			}
			function render(instant) {
				var fill = function () {
					var show = list();
					cards.forEach(function (c) { c.hidden = show.indexOf(c) === -1; });
					show.forEach(function (c) { grid.insertBefore(c, empty); });
					empty.hidden = show.length > 0;
					grid.classList.remove('is-out');
				};
				if (instant) { fill(); return; }
				grid.classList.add('is-out');
				setTimeout(fill, 300);
			}
			rating.addEventListener('change', function () { render(); });
			sort.addEventListener('change', function () { render(); });
			root.querySelectorAll('.alm-rv__cats button').forEach(function (b) {
				b.addEventListener('click', function () {
					cat = b.dataset.cat;
					root.querySelectorAll('.alm-rv__cats button').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
					render();
				});
			});
			render(true);
		});
	})();
	</script>
	<?php
}

/** The Elementor widget */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	if ( ! class_exists( 'Almora_Reviews_Widget' ) ) {
		class Almora_Reviews_Widget extends \Elementor\Widget_Base {
			public function get_name() { return 'almora-reviews'; }
			public function get_title() { return 'ALMORA Reviews'; }
			public function get_icon() { return 'eicon-review'; }
			public function get_categories() { return array( 'general' ); }
			public function get_keywords() { return array( 'reviews', 'ביקורות', 'almora' ); }
			protected function register_controls() {
				$this->start_controls_section( 'content', array( 'label' => 'ביקורות' ) );
				$this->add_control( 'title', array( 'label' => 'כותרת', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'ביקורות' ) );
				$this->add_control( 'per_page', array( 'label' => 'ביקורות בעמוד', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 10, 'min' => 1, 'max' => 60 ) );
				$this->add_control( 'show_verified', array(
					'label'       => 'להציג תג "מאומת"',
					'type'        => \Elementor\Controls_Manager::SWITCHER,
					'default'     => 'yes',
					'description' => 'התג מופיע רק על ביקורות של לקוחות שקנו את המוצר באתר (ווקומרס מסמן אותן אוטומטית). ביקורות לדוגמה לא מקבלות תג.',
				) );
				$this->add_control( 'include_samples', array(
					'label'       => 'להציג ביקורות לדוגמה',
					'type'        => \Elementor\Controls_Manager::SWITCHER,
					'default'     => '',
					'description' => 'מוצגות רק כשאין באתר ביקורות אמיתיות, תחת הכותרת של ביקורות הדוגמה ובלי תג מאומת. הן שמורות כביקורות ממתינות ולא מופיעות בעמודי המוצר.',
				) );
				$this->add_control( 'sample_title', array( 'label' => 'כותרת לביקורות הדוגמה', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'ביקורות לדוגמה', 'condition' => array( 'include_samples' => 'yes' ) ) );
				$this->end_controls_section();
			}
			protected function render() {
				echo almora_reviews_render( $this->get_settings_for_display() ); // phpcs:ignore
			}
		}
	}
	$widgets_manager->register( new Almora_Reviews_Widget() );
} );

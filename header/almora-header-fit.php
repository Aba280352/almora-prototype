<?php
/**
 * Plugin Name: ALMORA header fit
 * Description: The sticky header (Elementor template 8107, container header-sticky) holds logo + menu + "Best Sellers" + tools in one row. With 12 menu items it overflowed: the logo was pushed off the edge and the button sat on the icons. This keeps the row clean: smaller menu text, a gap before the button, and the lowest-priority items hidden in the sticky bar only when the screen is narrower (they stay in the full header menu, the mobile menu and the footer).
 * Menu 114 item IDs: 9478 size guide, 7813 gold calculator, 7812 about, 8602 budget shopping.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	?>
<style id="almora-header-fit">
.elementor-element-sticky-menu .elementor-nav-menu--main .elementor-item{font-size:15px!important;padding:6px 9px!important}
.elementor-element-sticky-center{column-gap:20px!important}
@media (max-width:1799px){.elementor-element-sticky-menu li.menu-item-9478,.elementor-element-sticky-menu li.menu-item-7813{display:none!important}}
@media (max-width:1499px){.elementor-element-sticky-menu li.menu-item-7812{display:none!important}}
@media (max-width:1319px){.elementor-element-sticky-menu li.menu-item-8602{display:none!important}}
</style>
	<?php
}, 99 );

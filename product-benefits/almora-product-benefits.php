<?php
/**
 * Plugin Name: ALMORA product benefits
 * Description: Product page: the five benefits (Ecomus icon box widget) and the "secure payment" card (with the card logos) as one gap-free grid: three cards on top, two wider cards under them, then the payment card across the full width. 6-column grid so every row is filled.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
<style id="almora-product-benefits">
.single-product-extra-content{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:20px;margin-top:24px}
.single-product-extra-content>section,.single-product-extra-content .ecomus-icon-box-widget{display:contents;margin:0;padding:0}
.single-product-extra-content .ecomus-icon-box-widget .ecomus-icon-box-widget__item,.single-product-extra-content>section.widget_custom_html .textwidget>.em-row{display:flex;flex-direction:column;align-items:center;justify-content:flex-start;gap:12px;margin:0;padding:22px 10px;border:0;border-radius:12px;text-align:center;background:#f5f5f5;box-sizing:border-box;height:100%;width:100%;max-width:none;min-width:0;justify-self:stretch;flex:none}
.single-product-extra-content .ecomus-icon-box-widget .ecomus-icon-box-widget__item{grid-column:span 2}
.single-product-extra-content .ecomus-icon-box-widget .ecomus-icon-box-widget__item:nth-child(n+4){grid-column:span 3}
.single-product-extra-content>section.widget_custom_html{grid-column:1/-1;display:block;margin:0;padding:0}
.single-product-extra-content>section.widget_custom_html .textwidget{height:100%}
.single-product-extra-content>section.widget_custom_html .textwidget>.em-row{flex-wrap:nowrap;width:100%;max-width:none}.single-product-extra-content>section.widget_custom_html .em-col .em-row{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:6px;margin:0;padding:0;border:0;width:auto;height:auto;background:none}
.single-product-extra-content>section.widget_custom_html .em-col{flex:none;width:auto;max-width:100%;padding:0}
.single-product-extra-content>section.widget_custom_html .em-col:first-child{border:0;margin:0;background:none;height:auto;display:flex;flex-direction:column;align-items:center;gap:12px}
.single-product-extra-content>section.widget_custom_html .em-col:last-child{display:flex;flex-wrap:wrap;justify-content:center;gap:6px}
.single-product-extra-content>section.widget_custom_html .em-col:last-child img{height:22px;width:auto;margin:0;border-radius:4px}
.single-product-extra-content .ecomus-icon-box-widget__icon{display:inline-flex;margin:0;color:#202020}
.single-product-extra-content .ecomus-icon-box-widget__icon svg{width:32px;height:32px}
.single-product-extra-content .ecomus-icon-box-widget__text{display:flex;flex-direction:column;gap:4px;margin:0}
.single-product-extra-content .ecomus-icon-box-widget__title{font-size:16px;font-weight:600;line-height:1.2;color:#202020}
.single-product-extra-content .ecomus-icon-box-widget__desc{font-size:14px;line-height:1.3;color:rgba(32,32,32,.7)}
@media (max-width:767px){.single-product-extra-content{gap:12px}.single-product-extra-content .ecomus-icon-box-widget .ecomus-icon-box-widget__item,.single-product-extra-content>section.widget_custom_html .textwidget>.em-row{padding:16px 6px}.single-product-extra-content .ecomus-icon-box-widget__title{font-size:14px}.single-product-extra-content .ecomus-icon-box-widget__desc{font-size:12px}.single-product-extra-content>section.widget_custom_html .em-col:last-child img{height:18px}}
</style>
	<?php
}, 30 );

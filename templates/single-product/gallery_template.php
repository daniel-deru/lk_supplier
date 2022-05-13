<?php

// require dirname(plugin_dir_path(__FILE__)) . "../includes/print.php";

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) return;

global $product;
$product = new WC_Product($product->get_id());
$images = $product->get_attributes()['external_image']->get_options();

foreach ( $images as $image ) {
	if ( ! $image ) continue;

	// echo $external_image->get_gallery_single_image( $image_url ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
   echo sprintf(
        '<div data-thumb="%1$s" data-thumb-alt="" class="woocommerce-product-gallery__image"><a href="%1$s"><img width="600" height="642" src="%1$s" class="" alt="" loading="lazy" title="61S2qlMWh6L._AC_SX679_" data-caption="" data-src="%1$s" data-large_image="%1$s" data-large_image_width="679" data-large_image_height="727" /></a></div>',
        $image
    );
}
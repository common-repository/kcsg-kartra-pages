<?php
/*
 * Plugin Name: KCSG Kartra Pages
 * Plugin URI: https://www.kpowertools.com/
 * Description: Display Kartra pages on your WordPress site
 * Version: 1.0.19
 * Author: Brian Katzung, Kappa Computer Solutions, LLC <briank@kappacs.com>
 * Copyright: 2019-2022 by Brian Katzung and Kappa Computer Solutions, LLC
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: kcsg-kartra-pages
 * Domain Path: /languages
 */

if ( is_admin() ) {
    include( dirname( __FILE__ ) . '/admin.php' );
}

/*
 * Hook in our special page template.
 */
add_action( 'plugins_loaded', 'kcsg_kp_init' );

function kcsg_kp_init () {
    /*
     * Try to handle the template assignment AFTER any themes that
     * blindly assign templates without checking template overrides.
     */
    add_filter( 'theme_page_templates', 'kcsg_kp_add_template', 11 );
    add_filter( 'template_include', 'kcsg_kp_include_template', 100 );
    load_plugin_textdomain( 'kcsg-kartra-pages', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/* Include our custom template in the template list */
function kcsg_kp_add_template( $templates ) {
    return array_merge( $templates, array( 'kcsg-kartra-page.php' => __( 'KCSG Kartra Page', 'kcsg-kartra-pages' ) ) );
}

/* Return the template file path when assigned */
function kcsg_kp_include_template( $template ) {
    if ( is_singular() ) {
	$assigned = get_post_meta( get_the_ID(), '_wp_page_template', true );
	if ('kcsg-kartra-page.php' == $assigned ) {
	    return wp_normalize_path( plugin_dir_path( __FILE__ ) . '/' . $assigned );
	}
    }

    return $template;
}

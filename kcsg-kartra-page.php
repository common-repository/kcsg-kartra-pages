<?php
/*
 * KCSG Kartra Page template
 *
 * Include either the loader script in an absolutely bare-bones
 * HTML page or the cached full-page content AS IS.
 *
 * Author: Brian Katzung, Kappa Computer Solutions, LLC <briank@kappacs.com>
 * License: GPL3 or later
 * Copyright 2019-2022 by Brian Katzung and Kappa Computer Solutions, LLC
 */
$kcsg_kp_sent_head = false;

// Send headers once if falling back to blank/WordPress mode
function kcsg_kp_send_head() {
    global $kcsg_kp_sent_head;

    if ( $kcsg_kp_sent_head ) return;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( ! get_theme_support( 'title-tag' ) ) { ?>
    <title><?php wp_title(); ?></title>
<?php }
    wp_head();
?></head>
<body><?php
    $kcsg_kp_sent_head = true;
}

// Render our custom page-loader page
function kcsg_kp_loader_page( $url_info, $id ) {
    list( $url, $raw_info ) = explode( "\n", $url_info, 2 );
    $info = (array) json_decode( $raw_info );
    $gtmid = get_post_meta( $id, 'kcsg_kp_gtmid', true );

    /*
     * Escape URL for non-display, in in-line JS string (all of which
     * *ought* to have zero effect on our "safe", sanitized URL values).
     */
    $esc_url = esc_js( esc_url_raw( $url ) );
?><!doctype html>
<html>
<head>
<?php
if ( isset( $info[ 'meta' ] ) ) {
    // Use cached meta-data when available
    echo join( "\n", $info[ 'meta' ] ), "\n";
} else {
?><meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='description' content=''>
<meta name='keywords' content=''>
<meta name='robots' content=''>
<?php
}
wp_site_icon();
?><script data-cfasync='false' type='text/javascript'>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('page');
    var reloadable = true;

    window.addEventListener('message', function (event) {
	var data = event.data;
	if ('no_visitor_cookie' === data.error && reloadable) {
	    reloadable = false; // Max 1 reload
	    page.src = 'https://app.kartra.com/front/domain_validation?step=1&domain=kartra.com&url=<?php echo $esc_url; ?>';
	    return;
	}

	if (data.title) {
	    document.title = data.title;
	}

	['description', 'keywords', 'robots'].forEach((meta) => {
	    if (data[meta]) {
		document.getElementsByName(meta)[0].content = data[meta];
	    }
	  });
      }, false);

    page.src = '<?php echo $esc_url; ?>';
  }, false);
<?php
    if ( preg_match( '/^GTM-[A-Z0-9]+$/', $gtmid ) ) {
?>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $gtmid; ?>');
<?php
    }
?>
</script>
</head>
<body>
<iframe id='page' style='width: 100%; height: 100%; position: absolute; top: 0px; left: 0px; border: none;' scrolling='yes' allowfullscreen='yes'></iframe>
</body>
<?php
}

while ( have_posts() ) {
    the_post();
    $id = get_the_ID();
    $mode = get_post_meta( $id, 'kcsg_kp_page_mode', true );
    switch ( $mode ) {
    case 'script':	# Kartra Live
    case 'cache':	# Kartra Download
	/*
	 * Display either the custom page loader (script/live mode)
	 * or the cached final page HTML (cache/download mode).
	 * rawurldecode reverses our post_meta protections (see admin).
	 */
	$content = get_post_meta( $id, 'kcsg_kp_cache', true );
	if ( '' !== $content ) {
	    if ( 'LOAD ' === substr( $content, 0, 5 ) ) {
		// "Content" is cached final page URL
		kcsg_kp_loader_page( substr( $content, 5 ), $id );
	    } else {
		// Content is raw page HTML
		echo $content;
	    }
	    continue 2;
	}
	break;
    }

    // Fall back to a "Blank Slate"-ish, native WordPress page output.
    kcsg_kp_send_head();
    the_content();
}

if ( $kcsg_kp_sent_head ) {
    wp_footer();
?></body></html><?php
}

# END

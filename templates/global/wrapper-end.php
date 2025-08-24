<?php
/**
 * Single JobPress Content wrappers end
 *
 * This template can be overridden by copying it to yourtheme/jobpress/global/wrapper-end.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$template = jobpress_get_theme_slug_for_templates();

switch ( $template ) {
	case 'twentyten':
		echo '</div></div>';
		break;
	case 'twentyeleven':
		echo '</div>';
		get_sidebar( 'shop' );
		echo '</div>';
		break;
	case 'twentytwelve':
		echo '</div></div>';
		break;
	case 'twentythirteen':
		echo '</div></div>';
		break;
	case 'twentyfourteen':
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen':
		echo '</div></div>';
		break;
	case 'twentysixteen':
		echo '</main></div>';
		break;
	default:
		echo '</main></div>';
		break;
}

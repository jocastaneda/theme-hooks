<?php
/**
 * Plugin Name: Theme Hooks
 * Author: Jose Castaneda
 * Author URI: http://blog.josemcastaneda.com
 * Description: Want to see how many hooks the currently active theme creates? Now you can!
 * Version: 0.1.0
 */

add_action( 'admin_menu', 'th_admin_menu_add' );
function th_admin_menu_add() {
	add_theme_page( __( 'All theme hooks', 'domain' ), __( 'Theme Hooks', 'domain' ), 'edit_theme_options', 'theme-hooks-listing', 'th_render_page' );
}

add_action( 'th_print_hooks', 'th_get_data' );
function th_get_data() {
	# get all files
	$dir = new RecursiveDirectoryIterator( get_stylesheet_directory() );
	$iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

	# We create our list of files in the theme
	$files = array();
	foreach ( $iterator as $file ) {
		array_push( $files, $file->getPathname() );
	}

	# we collect only PHP files because we are looking for hooks and filters
	$content;
	foreach ( $files as $key => $filename ) {
		if ( substr( $filename, -4 ) == '.php' && ! is_dir( $filename ) ) {
			$content[ $filename ] = file_get_contents( $filename );
		}
	}

	# set some common variables to use later on
	$actions = 0;
	$hooks = array();
	$total = 0;
	$output = '<ol>';
	foreach ( $content as $key => $file ) {
		if ( preg_match_all( '/(do_action|apply_filters)/i', $file, $matches ) ) {
			$output .= '<li>' . sprintf( __( 'The file: %s contains <strong>%d hooks</strong>', 'theme-hooks' ), $key, count( $matches[0] ) ) . '</li>';
			$total += count( $matches[0] );
		};
	}
	$output .= '</ol>';

	printf( __( '<strong>%s</strong> has a total of <strong>%d</strong> hooks. They are as follows: %s', 'theme-hooks' ), wp_get_theme()->Name, $total, $output );
}

function th_render_page() {
?>
	<div class="wrap">
	<h1><?php _e( 'Theme Hooks', 'theme-hooks' ); ?></h1>
		<?php do_action( 'th_print_hooks' ); ?>
	</div>
<?php }

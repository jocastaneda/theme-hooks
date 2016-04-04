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

	echo '<h2>' . __( 'The theme hooks to', 'theme-hooks' ) . '</h2>';
	# Let us see what the theme is hooking to
	$hooks = 0;
	$hook_list = '<ol>';
	foreach ( $content as $key => $file ) {
		$action = preg_match_all( '/add_action/', $file, $hooked );
		$filter = preg_match_all( '/add_filter/', $file, $filtered );
		$hooks += count( $hooked[0] ) + count( $filtered[0] );
		
		if ( $hooked[0] || $filtered[0] ) {
			$hook_list .= '<li>';
			$hook_list .= sprintf( __( 'There are <strong>%d actions</strong> and <strong>%d filters</strong> applied in file: <code>%s</code>', 'theme-hooks' ), count( $hooked[0] ), count( $filtered[0] ), $key );
			$hook_list .= '</li>';
		}
		
	}
	$hook_list .= '</ol>';

	# List how many actions it uses and where
	printf( __( '<strong>%s</strong> hooks to <span class="emphasis">%d</span> actions or filters. They are:<br> %s', 'theme-hooks' ), wp_get_theme()->Name, $hooks, $hook_list );

	$total = 0;
	$actions = 0;
	$filters = 0;
	$output = '<ol>';
	foreach ( $content as $key => $file ) {
		# get how many action hooks are being created
		$actions = preg_match_all( '/do_action/i', $file, $action );
		$filters = preg_match_all( '/apply_filters/i', $file, $filter );
		$total += count( $action[0] ) + count( $filter[0] );

		if ( $action[0] || $filter[0] ) {
			$output .= '<li>';
			$output .= sprintf( __( 'There are <strong>%d actions</strong> and <strong>%d filters</strong> in the file: <code>%s</code>', 'theme-hooks' ), count( $action[0] ), count( $filter[0] ), $key );
			$output .= '</li>';
		}
	}
	$output .= '</ol>';

	echo '<h2>' . __( 'The theme creates how many hooks now?', 'theme-hooks' ) . '</h2>';
	printf( __( '<strong>%s</strong> has a total of <strong>%d</strong> hooks. They are as follows: %s', 'theme-hooks' ), wp_get_theme()->Name, $total, $output );
}

function th_render_page() {
?>
	<div class="wrap theme-hooks">
	<h1><?php _e( 'Theme Hooks', 'theme-hooks' ); ?></h1>
		<?php do_action( 'th_print_hooks' ); ?>
	</div>
<?php }

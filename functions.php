<?php
/**
 * countycooperage functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package countycooperage
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function countycooperage_setup()
{
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on countycooperage, use a find and replace
	 * to change 'countycooperage' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('countycooperage', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'countycooperage'),
		)
	);

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'countycooperage_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height' => 250,
			'width' => 250,
			'flex-width' => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'countycooperage_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function countycooperage_content_width()
{
	$GLOBALS['content_width'] = apply_filters('countycooperage_content_width', 640);
}
add_action('after_setup_theme', 'countycooperage_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function countycooperage_widgets_init()
{
	register_sidebar(
		array(
			'name' => esc_html__('Sidebar', 'countycooperage'),
			'id' => 'sidebar-1',
			'description' => esc_html__('Add widgets here.', 'countycooperage'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'countycooperage_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function countycooperage_scripts()
{
	wp_enqueue_style('countycooperage-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_style_add_data('countycooperage-style', 'rtl', 'replace');

	wp_enqueue_script('countycooperage-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'countycooperage_scripts');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * @param array $providers The collection of providers that will be used to scan the design payload
 * @return array
 */
function register_my_theme_provider(array $providers): array
{
	$providers[] = [
		'id' => 'my_theme', // The id of this custom provider. It should be unique across all providers
		'name' => 'My Theme Scanner',
		'description' => 'Scans the current active theme and child theme',
		'callback' => 'scanner_cb_my_theme_provider', // The function that will be called to get the data. Please see the next step for the implementation
		'enabled' => \WindPress\WindPress\Utils\Config::get(sprintf(
			'integration.%s.enabled',
			'my_theme' // The id of this custom provider
		), true),
	];

	return $providers;
}
add_filter('f!windpress/core/cache:compile.providers', 'register_my_theme_provider');
function scanner_cb_my_theme_provider(): array
{
	// The file with this extension will be scanned, you can add more extensions if needed
	$file_extensions = [
		'php',
		'js',
		'html',
	];

	$contents = [];
	$finder = new \WindPressDeps\Symfony\Component\Finder\Finder();

	// The current active theme
	$wpTheme = wp_get_theme();
	$themeDir = $wpTheme->get_stylesheet_directory();

	// Check if the current theme is a child theme and get the parent theme directory
	$has_parent = $wpTheme->parent() ? true : false;
	$parentThemeDir = $has_parent ? $wpTheme->parent()->get_stylesheet_directory() : null;

	// Scan the theme directory according to the file extensions
	foreach ($file_extensions as $extension) {
		$finder->files()->in($themeDir)->name('*.' . $extension);
		if ($has_parent) {
			$finder->files()->in($parentThemeDir)->name('*.' . $extension);
		}
	}

	// Get the file contents and send to the compiler
	foreach ($finder as $file) {
		$contents[] = [
			'name' => $file->getRelativePathname(),
			'content' => $file->getContents(),
		];
	}

	return $contents;
}
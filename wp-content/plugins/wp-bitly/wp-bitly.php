<?php
/**
 * WP Bitly
 * This plugin can be used to generate shortlinks for your websites posts, pages, and custom post types.
 * Extremely lightweight and easy to set up, give it your Bitly oAuth token and go!
 * ಠ_ಠ
 *
 * @package   wp-bitly
 * @author    Mark Waterous <mark@watero.us>
 * @author    Chip Bennett
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins/wp-bitly
 * @copyright 2014 Mark Waterous & Chip Bennett
 * @wordpress-plugin
 *            Plugin Name:       WP Bitly
 *            Plugin URI:        http://wordpress.org/plugins/wp-bitly
 *            Description:       WP Bitly can be used to generate shortlinks for your websites posts, pages, and custom post types. Extremely lightweight and easy to set up, give it your Bitly oAuth token and go!
 *            Version:           2.3.2
 *            Author:            <a href="http://mark.watero.us/">Mark Waterous</a> & <a href="http://www.chipbennett.net/">Chip Bennett</a>
 *            Text Domain:       wp-bitly
 *            License:           GPL-2.0+
 *            License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *            Domain Path:       /languages
 *            GitHub Plugin URI: https://github.com/mwaterous/wp-bitly
 */


if (!defined('WPINC'))
    die;


define('WPBITLY_VERSION', '2.3.2');

define('WPBITLY_DIR', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
define('WPBITLY_URL', plugins_url() . '/' . basename(dirname(__FILE__)));

define('WPBITLY_LOG', WPBITLY_DIR . '/log/debug.txt');
define('WPBITLY_ERROR', __('WP Bitly Error: No such option %1$s', 'wp-bitly'));

define('WPBITLY_BITLY_API', 'https://api-ssl.bitly.com');

/**
 * The primary controller class for everything wonderful that WP Bitly does.
 * We're not sure entirely what that means yet; if you figure it out, please
 * let us know and we'll say something snazzy about it here.
 *
 * @TODO    : Update the class phpdoc description to say something snazzy.
 * @package wp-bitly
 * @author  Mark Waterous <mark@watero.us>
 */
final class WP_Bitly {

    /**
     * @var $_instance An instance of ones own instance
     */
    private static $_instance;

    /**
     * @var array The WP Bitly configuration is stored in here
     */
    private $_options = array();


    /**
     * This creates and returns a single instance of WP_Bitly.
     * If you haven't seen a singleton before, visit any Starbucks; they're the ones sitting on expensive laptops
     * in the corner drinking a macchiato and pretending to write a book. They'll always be singletons.
     *
     * @since   2.0
     * @static
     * @uses    WP_Bitly::populate_options()     To create our options array.
     * @uses    WP_Bitly::includes_files()       To do something that sounds a lot like what it sounds like.
     * @uses    WP_Bitly::check_for_upgrade()    You run your updates, right?
     * @uses    WP_Bitly::action_filters()       To set up any necessary WordPress hooks.
     * @return  WP_Bitly
     */
    public static function get_in() {
        if (null === self::$_instance) {
            self::$_instance = new self;
            self::$_instance->populate_options();
            self::$_instance->include_files();
            self::$_instance->check_for_upgrade();
            self::$_instance->action_filters();
        }

        return self::$_instance;
    }


    /**
     * Populate WP_Bitly::$options with the configuration settings stored in 'wpbitly-options',
     * using an array of default settings as our fall back.
     *
     * @since 2.0
     */
    public function populate_options() {

        $defaults = apply_filters('wpbitly_default_options', array(
            'version'     => WPBITLY_VERSION,
            'oauth_token' => '',
            'post_types'  => array('post', 'page'),
            'authorized'  => false,
            'debug'       => false,
        ));

        $this->_options = wp_parse_args(get_option('wpbitly-options'), $defaults);

    }


    /**
     * Access to our WP_Bitly::$_options array.
     *
     * @since 2.2.5
     *
     * @param  $option string The name of the option we need to retrieve
     *
     * @return         mixed  Returns the option
     */
    public function get_option($option) {
        if (!isset($this->_options[ $option ]))
            trigger_error(sprintf(WPBITLY_ERROR, ' <code>' . $option . '</code>'), E_USER_ERROR);

        return $this->_options[ $option ];
    }


    /**
     * Sets a single WP_Bitly::$_options value on the fly
     *
     * @since 2.2.5
     *
     * @param $option string The name of the option we're setting
     * @param $value  mixed  The value, could be bool, string, array
     */
    public function set_option($option, $value) {
        if (!isset($this->_options[ $option ]))
            trigger_error(sprintf(WPBITLY_ERROR, ' <code>' . $option . '</code>'), E_USER_ERROR);

        $this->_options[ $option ] = $value;
    }


    /**
     * WP Bitly is a pretty big plugin. Without this function, we'd probably include things
     * in the wrong order, or not at all, and cold wars would erupt all over the planet.
     *
     * @since   2.0
     */
    public function include_files() {
        require_once(WPBITLY_DIR . '/includes/functions.php');
        if (is_admin())
            require_once(WPBITLY_DIR . '/includes/class.wp-bitly-admin.php');
    }


    /**
     * Simple wrapper for making sure everybody (who actually updates their plugins) is
     * current and that we don't just delete all their old data.
     *
     * @since   2.0
     */
    public function check_for_upgrade() {

        // We only have to upgrade if it's pre v2.0
        $upgrade_needed = get_option('wpbitly_options');
        if ($upgrade_needed !== false) {

            if (isset($upgrade_needed['post_types']) && is_array($upgrade_needed['post_types'])) {
                $post_types = apply_filters('wpbitly_allowed_post_types', get_post_types(array('public' => true)));

                foreach ($upgrade_needed['post_types'] as $key => $pt) {
                    if (!in_array($pt, $post_types))
                        unset($upgrade_needed['post_types'][ $key ]);
                }

                $this->set_option('post_types', $upgrade_needed['post_types']);
            }

            delete_option('wpbitly_options');

        }

    }


    /**
     * Hook any necessary WordPress actions or filters that we'll be needing in order to make
     * the plugin work its magic. This method also registers our super amazing slice of shortcode.
     *
     * @since 2.0
     * @todo  Instead of arbitrarily deactivating the Jetpack module, it might be polite to ask.
     */
    public function action_filters() {

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));

        add_action('save_post', 'wpbitly_generate_shortlink');
        add_filter('pre_get_shortlink', 'wpbitly_get_shortlink', 10, 2);

        add_action('init', array($this, 'load_plugin_textdomain'));
        add_action( 'admin_bar_menu', 'wp_admin_bar_shortlink_menu', 90 );

        add_shortcode('wpbitly', 'wpbitly_shortlink');

        if (class_exists('Jetpack')) {

            add_filter('jetpack_get_available_modules', '_bad_wpme');
            function _bad_wpme($modules) {
                unset($modules['shortlinks']);

                return $modules;
            }

        }

    }


    /**
     * Add a settings link to the plugins page so people can figure out where we are.
     *
     * @since   2.0
     *
     * @param   $links An array returned by WordPress with our plugin action links
     *
     * @return  array The slightly modified 'rray.
     */
    public function add_action_links($links) {

        return array_merge(array('settings' => '<a href="' . admin_url('options-writing.php') . '">' . __('Settings', 'wp-bitly') . '</a>'), $links);

    }


    /**
     * This would be much easier if we all spoke Esperanto (or Old Norse).
     *
     * @since   2.0
     */
    public function load_plugin_textdomain() {

        $languages = apply_filters('wpbitly_languages_dir', WPBITLY_DIR . '/languages/');
        $locale = apply_filters('plugin_locale', get_locale(), 'wp-bitly');
        $mofile = $languages . $locale . '.mo';

        if (file_exists($mofile)) {
            load_textdomain('wp-bitly', $mofile);
        } else {
            load_plugin_textdomain('wp-bitly', false, $languages);
        }

    }

}


/**
 * Call this in place of WP_Bitly::get_in()
 * It's shorthand.
 * Makes life easier.
 * In fact, the phpDocumentor block is bigger than the function itself.
 *
 * @return WP_Bitly
 */
function wpbitly() {
    return WP_Bitly::get_in(); // in.
}

wpbitly();

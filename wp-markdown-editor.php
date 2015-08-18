<?php
/*
Plugin Name: WpMarkdownEditor - Markdown Editor for WordPress
Plugin URI: https://github.com/hoducha/wp-markdown-editor
Description: WpMarkdownEditor replace the visual editor with a simple Markdown editor for your posts and pages.
Version: 1.0
Author: Ha Ho
Author URI: http://hoducha.com
License: GPLv2 or later
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('PLUGIN_VERSION', '0.1.0');
define('MINIMUM_WP_VERSION', '3.1');

class WpMarkdownEditor
{
    private static $instance;

    private function __construct()
    {
        // Activation / Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));

        // Load markdown editor
        add_action('admin_enqueue_scripts', array($this, 'enqueue_stuffs'));
        add_action('admin_footer', array($this, 'init_editor'));

        // Remove quicktags buttons
        add_filter('quicktags_settings', array($this, 'quicktags_settings'), $editorId = 'content');

        // Modify content filters
        $this->modify_content_filters();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    function enqueue_stuffs()
    {
        // only enqueue stuff on the post editor page
        if (get_current_screen()->base !== 'post')
            return;
        wp_enqueue_script('marked-js', $this->plugin_url('/editor/marked.js'));
        wp_enqueue_script('markdown-editor', $this->plugin_url('/editor/editor.js'));
        wp_enqueue_style('style-name', $this->plugin_url('/editor/editor.css'));
    }

    function modify_content_filters()
    {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
        remove_filter('the_content', 'wptexturize');
        remove_filter('the_excerpt', 'wptexturize');
        add_filter('the_content', array($this, 'markdown_filter'));
        add_filter('the_excerpt', array($this, 'markdown_filter'));
    }

    function markdown_filter($content)
    {
        if (!class_exists('Parsedown')) {
            spl_autoload_register(function ($class) {
                require_once plugin_dir_path(__FILE__) . '/parsedown/Parsedown.php';
            });
        }

        return Parsedown::instance()->text($content);
    }

    function init_editor()
    {
        if (get_current_screen()->base !== 'post')
            return;
        echo '<script type="text/javascript">
                var editor = new Editor();
                editor.render();
            </script>';
    }

    function quicktags_settings($qtInit)
    {
        $qtInit['buttons'] = ' ';
        return $qtInit;
    }

    function plugin_url($path)
    {
        return plugins_url('wp-markdown-editor/' . $path);
    }

    function plugin_activation()
    {
        global $wpdb;
        $wpdb->query("UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = 'false' WHERE `meta_key` = 'rich_editing'");
    }

    function plugin_deactivation()
    {
        global $wpdb;
        $wpdb->query("UPDATE `" . $wpdb->prefix . "usermeta` SET `meta_value` = 'true' WHERE `meta_key` = 'rich_editing'");
    }

}

WpMarkdownEditor::getInstance();

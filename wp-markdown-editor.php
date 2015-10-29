<?php
/**
 * Plugin Name: WP Markdown Editor
 * Plugin URI: https://github.com/hoducha/wp-markdown-editor
 * Description: WP Markdown Editor replaces the default editor with a WYSIWYG Markdown Editor for your posts and pages.
 * Version: 1.0.1
 * Author: Ha Ho
 * Website: http://www.hoducha.com
 * License: GPLv2 or later
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('PLUGIN_VERSION', '1.0.1');
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
        wp_enqueue_script('to-markdown-js', $this->plugin_url('/vendor/domchristie/to-markdown/dist/to-markdown.js'));
        wp_enqueue_script('simplemde-js', $this->plugin_url('/vendor/NextStepWebs/simplemde-markdown-editor/dist/simplemde.min.js'));
        wp_enqueue_style('simplemde-css', $this->plugin_url('/vendor/NextStepWebs/simplemde-markdown-editor/dist/simplemde.min.css'));
    }

    function modify_content_filters()
    {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
        remove_filter('the_content', 'wptexturize');
        remove_filter('the_excerpt', 'wptexturize');

        // add_filter('the_content', array($this, 'markdown_filter'));
        // add_filter('the_excerpt', array($this, 'markdown_filter'));

        add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ), 10, 2 );
    }

    function wp_insert_post_data($data, $postarr)
    {
        $data['post_content'] = $this->markdown_filter($data['post_content']);
        return $data;
    }

    function markdown_filter($content)
    {
        if (!class_exists('Parsedown')) {
            spl_autoload_register(function ($class) {
                require_once plugin_dir_path(__FILE__) . '/vendor/erusev/parsedown/Parsedown.php';
            });
        }

        return Parsedown::instance()->text($content);
    }

    function init_editor()
    {
        if (get_current_screen()->base !== 'post')
            return;

        echo '<script type="text/javascript">
                // Init the editor
                var simplemde = new SimpleMDE({
                    initialValue: toMarkdown(document.getElementById("content").value),
                    spellChecker: false
                });

                // Override the toggleFullScreen to change the zIndex of the editor
                var original_toggleFullScreen = toggleFullScreen;
                var toggleFullScreen = function(editor) {
                    original_toggleFullScreen(editor);

                    var cm = editor.codemirror;
                    var wrap = cm.getWrapperElement();
                    if(/fullscreen/.test(wrap.previousSibling.className)) {
                        document.getElementById("wp-content-editor-container").style.zIndex = 999999;
                    } else {
                        document.getElementById("wp-content-editor-container").style.zIndex = 1;
                    }
                }

                // Re-bind the click event of the fullscreen button
                var fullscreenButton = document.getElementsByClassName("fa-arrows-alt");
                fullscreenButton[0].onclick = function() {
                    toggleFullScreen(simplemde);
                }

                if ( typeof jQuery !== "undefined" ) {
                    jQuery(document).ready(function(){
                        // Remove the quicktags-toolbar
                        document.getElementById("ed_toolbar").style.display = "none";

                        // Integrate with WP Media module
                        var original_wp_media_editor_insert = wp.media.editor.insert;
                        wp.media.editor.insert = function( html ) {
                            original_wp_media_editor_insert(html);
                            simplemde.codemirror.replaceSelection(toMarkdown(html));
                        }
                    });
                }
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

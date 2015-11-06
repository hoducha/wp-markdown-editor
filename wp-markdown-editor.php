<?php
/**
 * Plugin Name: WP Markdown Editor
 * Plugin URI: https://github.com/hoducha/wp-markdown-editor
 * Description: WP Markdown Editor replaces the default editor with a WYSIWYG Markdown Editor for your posts and pages.
 * Version: 2.0.0
 * Author: Ha Ho
 * Website: http://www.hoducha.com
 * License: GPLv2 or later
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('PLUGIN_VERSION', '2.0');
define('MINIMUM_WP_VERSION', '3.1');

include_once dirname( __FILE__ ) . '/jetpack/require-lib.php';
include dirname( __FILE__ ) . '/jetpack/markdown/easy-markdown.php';

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

        // Load Jetpack Markdown module
        $this->load_jetpack_markdown_module();
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
        wp_enqueue_style('custom-css', $this->plugin_url('/style.css'));
    }

    function load_jetpack_markdown_module()
    {
        // If the module is active, let's make this active for posting, period.
        // Comments will still be optional.
        add_filter( 'pre_option_' . WPCom_Markdown::POST_OPTION, '__return_true' );
        function jetpack_markdown_posting_always_on() {
            global $wp_settings_fields;
            if ( isset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] ) ) {
                unset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] );
            }
        }
        add_action( 'admin_init', 'jetpack_markdown_posting_always_on', 11 );

        function jetpack_markdown_load_textdomain() {
            load_plugin_textdomain( 'jetpack', false, dirname( plugin_basename( __FILE__ ) ) . '/jetpack/languages/' );
        }
        add_action( 'plugins_loaded', 'jetpack_markdown_load_textdomain' );

        function jetpack_markdown_settings_link($actions) {
            return array_merge(
                array( 'settings' => sprintf( '<a href="%s">%s</a>', 'options-discussion.php#' . WPCom_Markdown::COMMENT_OPTION, __( 'Settings', 'jetpack' ) ) ),
                $actions
            );
            return $actions;
        }
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jetpack_markdown_settings_link' );
    }

    function init_editor()
    {
        if (get_current_screen()->base !== 'post')
            return;

        echo '<script type="text/javascript">
                // Init the editor
                var simplemde = new SimpleMDE({
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
                            simplemde.codemirror.replaceSelection(html);
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

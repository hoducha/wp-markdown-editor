=== WP Markdown Editor ===
Contributors: hoducha
Donate link: http://hoducha.com/donate
Tags: markdown, md, editor, wysiwyg, preview, simplemde, jetpack, post, posting, writing, publishing
Requires at least: 3.0.1
Tested up to: 4.3.1
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A beautiful visual Markdown editor for Wordpress

== Description ==

WP Markdown Editor replaces the default editor with SimpleMDE editor - a beautiful and intuitive Markdown editor.
The plugin uses the Markdown module from [Jetpack](http://jetpack.me) for parsing and saving content.

If you are not familiar with Markdown, Markdown is used by writers and bloggers who want a quick and easy way to write
rich text, without having to take their hands off the keyboard, and without learning a lot of complicated codes and shortcuts.
Refer to the [Markdown Quick Reference](http://hoducha.com/markdown-guide.html) page for help.

Check out the [Screenshots](https://wordpress.org/plugins/wp-markdown-editor/screenshots/ "Screenshots") to see how it looks.

The plugin is open source on [GitHub](https://github.com/hoducha/wp-markdown-editor).

> If you like the plugin, feel free to [donate](http://hoducha.com/donate) or rate it (on the right side of this page). Thanks!

== Installation ==

1. Copy the plugin directory `wp-markdown-editor` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Note: You don't need to install Jetpack to use this plugin.

== Screenshots ==

1. Main screen
2. Fullscreen side by side

== Frequently Asked Questions ==

= Can I embed HTML in the post? =
Yes, you can. Markdown is a simple markup language so its features are limited. But sometimes you want do fancy things
to your post, in that case you can write HTML in your post.

= Can I insert shortcodes in the post? =
Yes, you can. WP Markdown Editor uses the Markdown module from Jetpack and it supports shortcodes.

= How do I use Markdown syntax? =
Please refer to this resource: [Markdown Quick Reference](http://hoducha.com/markdown-guide.html).

= How do I convert an existing post to Markdown? =
You existing posts will display as HTML in the WP Markdown Editor. You can still update your post in HTML or you can
convert your posts to markdown using [to-markdown](http://domchristie.github.io/to-markdown) or other available tools if you want.

= Do I have to install Jetpack to use this plugin? =
No, you don't.

= Can I install both Jetpack and WP Markdown Editor on my site? =
Yes, you can. But the order of activation should be Jetpack then WP Markdown Editor.

== Changelog ==

= 2.0.3 =
* Detect if the Jetpack Markdown module is activated
* Code refactoring

= 2.0.2 =
* Upgrade SimpleMDE to v1.8.1

= 2.0.1 =
* Fixed some bugs

= 2.0.0 =
* Using Markdown module from Jetpack for parsing and saving content
* Added changelog and FAQ

= 1.0.2 =
* Integrate with WP Media module
* Added icon and banner

= 1.0.1 =
* Save the content as HTML
* Remove quicktags-toolbar
* Change zIndex if toggle fullscreen

= 1.0.0 =
* Initial version

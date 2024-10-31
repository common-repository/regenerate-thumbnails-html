=== Regenerate Thumbnails HTML ===
Contributors: TigrouMeow
Tags: regenerate, thumbnail, image, html, resize, content, size, sizes, media, image width, image sizes
Requires at least: 4.2.0
Tested up to: 4.7.2
Stable tag: 0.0.2
License: GPLv2 or later

Update the HTML of the images contained in the post from one image size to another. Useful when switching between sizes and themes.

== Description ==

When you insert images in WordPress, it's always a specific image size. It can be an image size such as Medium, Large or something else set by your theme or another plugin. When you update your media sizes settings (or switch theme), you need to regenerate those images (with Regenerate Thumbnails, for example). But what about your posts content? They will be still using the old images, which aren't actually part of the new metadata, and little by little your WordPress will become more and more broken. This plugin fixes this by regenerating the HTML for your thumbnails / images in your content and keeps everything clean. Once this is done, it's a good idea to run a tool like Media Cleaner (https://wordpress.org/plugins/media-cleaner/) on your install to detect the useless files.

***Be careful!***. Before using this plugin, backup your database! It doesn't modify your files, only database.

== Installation ==

1. Unzip and upload regenerate-thumbnails-html folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to menu Meow Apps > Regenerate Thumbnails HTML and follow the steps.

== Frequently Asked Questions ==

No questions yet :)

== Screenshots ==

1. Regenerate Thumbnails HTML's Dashboard

== Changelog ==

= 0.0.2 =
* First release.

=== Plugin Name ===
Contributors: Simon Lord & Elran Oded
Donate link: http://www.psdcovers.com/adjustly-collapse/
Tags: Collapsible, Slide, Javascript, jQuery, Accordion, Automatic, Condense, Tuck Away, Squish, Reveal
Requires at least: 3
Tested up to: 3.3.1
Stable tag: 1.0.0

Move over, make room: expand and collapse content in an SEO friendly way. Great for contributors or theme designers.

== Description ==

Developed internally for our Adjustly theme, this plugin allows authors to link 2 html elements together as <b>trigger</b> and <b>target</b>.  When the trigger is clicked the target will immediately expand to reveal its content.
<h4>Try the Demo</h4>

If you're interested in seeing what a default installation of Adjustly Collapse has to offer, have a look at some samples on our site.

<a href="http://www.psdcovers.com/adjustly-collapse/">http://www.psdcovers.com/adjustly-collapse/</a>


<h4>Usage</h4>

The basic structure without any CSS bells and whistles looks like this:

<code>
<a class="aj-collapse" rel="myslidingcontent">trigger</a>
<div class="aj-hidden" id="myslidingcontent">target: this content is hidden by default</div>
</code>
	
In the example above, the <b>trigger</b> is an href element and the <b>target</b> is a div element.  Note that the target can be any element you choose.

<h4>rel="[id]"</h4>
	
Each trigger and target pair must contain a common label so that the trigger knows which target to expand or collapse. The trigger <b>rel</b> tag must be the same name as the target's <b>id</b> tag.  If you plan on having multiple collapsible elements in a page, post or theme then you will need to ensure that the <b>rel</b> and <b>id</b> tags are always unique for each trigger/target combo.
<h4>Class Names</h4>

1. The trigger must always have the following class name: <b>class="aj-collapse"</b>
1. The target can have either of 2 classes: <b>class="aj-hidden"</b> will make the target collapsed by default while <b>class="aj-visible"</b> will display the content normally but allow the viewer to collapse it.

<h4>Notes</h4>

The trigger and target do not have to be next to each other.  The target can be at the opposite end of the article or you can place it within the trigger. Theme developers can use it to make widgets collapsible.


== Frequently Asked Questions ==

= Can I use my existing WordPress theme? =

Of course! The plugin is quite useful but isn't dependant on any functionality found in any given version of Wordpress.  But this is our first plugin and therefore has only been tested with the most recent release of WP which is why we can only claim 3.3.1 compatibility.

= Will this affect my SEO? =

No.  The content is clearly visible to search engines and very friendly.

= I am new to Social, do you have a Facebook page? =

Yes we do, but as our brand identity... <a href='https://www.facebook.com/PSDCovers'>PSDCovers</a>. Please like!

= How about Twitter? =

Yes! <a href='https://twitter.com/psdcovers'>@psdcovers</a> does the twitter.  please follow!

= How does one use the shortcode, exactly? =

This plugin does not use shortcodes.


== Installation ==

1. Upload `adjustly-collapse` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Leave a comment at http://www.psdcovers.com/adjustly-collapse/
=== Usernoise modal feedback / contact form ===
Contributors: karevn
Tags: feedback, contact, form, contact form, email, ajax, custom, admin, button, russian, dashboard, lightbox, ipad, plugin, jquery, comments
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 2.0.3

Modal contact / feedback form designed "the Apple way".

== Description ==

Usernoise is a "just works" modal contact / feedback form. 
You will not need to change even a line of code in your site.

= Demo =
Usernoise demo is available at [karevn.com](http://karevn.com) - a "Feedback" button at the left.

= Go Pro =
__[Usernoise Pro](http://codecanyon.net/item/usernoise-pro-advanced-modal-feedback-debug/1420436?ref=karevn)__ adds even more features, like:

* Public feedback comments
* CSS customization for every piece of public interface.
* Advanced debug info gathered along with feedback
* Ability to use custom Feedback buttons without code modifications.
* Second email for admin notifications.


= Features =
* Adds a customizable "Feedback" button and modal form to your site.
* Bullet-proof visual design will work with any site template.
* Spam-proof. Usernoise is designed in such a way that most of spam bots can't see its form.
* Admin notifications, feedback archive available at admin area.
* Modal window can be controlled by Javascript API.
* Lots, lots of options.
* Highest performance possible. From 2.0 release, Usernoise only adds a small link to your site's HTML.

= Compatibility =
* IE7+, Firefox, Chrome, Safari, Opera, Safari on iPad supported.
* Usernoise can be disabled on mobile devices.
* WordPress 3.1 is required.


= Translations =
If you need Usernoise in your language which is not supported currently - you can help Usernoise by 
translating it. Don't worry, translating is extremely easy - just [download a localization template](http://plugins.svn.wordpress.org/usernoise/languages/usernoise.pot), edit it with your favorite text editor and send to email karev.n (at) gmail dot com. Your help will be really appreciated.

__Available translations__

* Deutsch - by Alexander from [http://motday.tk/]
* Brazilian - by Ricardo Silva
* Bulgarian - by Димитър Митев
* Chineze - by Song Zou
* Dutch - by Reggie Biemans
* Finnish - by Teijo Kulmala
* French - by Brad Coudray
* Italian - by Omar D. Molteni aka CyberAngel
* Lithuanian - by Justinas Jakūnas
* Hungarian - by Bálint
* Persian - by Morteza Nazari
* Polish - by Konrad Sztumski
* Portuguese - by rasilva
* Russian - by Nikolay Karev
* Spanish - by Dario Doidos
* Swedish - by  Mattias Thurfjell
* Ukrainian - by Vladyslav Cherednichenko


= Support =
Having problems or need support? Feel free to email me - karev.n (at) gmail dot com or [open a support topic](http://wordpress.org/tags/usernoise?forum_id=10#postform).

== Installation ==

1. Upload `usernoise` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings - Usernoise in admin area of your site, set it up to your needs.

== Frequently Asked Questions ==

1. **Usernoise button does not show after installation** - <strong>Check if your theme has `<?php wp_head(); ?>`</strong> call at `header.php`. Also check if some of your plugins install their own jQuery versions. Feel free to contact me for help.
2. **I have problems with notification email delivery** - It is a generic WordPress problem with outgoing emails. You can use [All In One Email plugin](http://codecanyon.net/item/all-in-one-email-for-wordpress/1290390?ref=karevn) to fix it and have HTML emails instead of plain text.
3. **Can I open a feedback window using some other button?** - yes, you just need to upgrade to [Usernoise Pro](http://codecanyon.net/item/usernoise-pro-advanced-modal-feedback-debug/1420436?ref=karevn).
4. **When I fill the feedback form and click "Submit feedback" button - the loader spins, and nothing happens** - most probably, you have `define('FORCE_SSL_ADMIN', true);` in your `wp-config.php`, also check if you have Site Url option at the general settings page equal to actual site url (www prefix matters!).
4. **Is Usernoise spam-proof?** -- Yes, it almost is. Let me know if you are receiving a lot of spam with Usernoise - I'll try to help.
5. **I need Usernoise in my language** - [Download a localization template](http://plugins.svn.wordpress.org/usernoise/languages/usernoise.pot), edit it with your favorite text editor, and send me to `karev.n (at) gmail dot com`. I will include your translation and credits into the next Usernoise release. It takes 2-3 days usually.
7. **Can I add some custom fields to Usernoise form?** - Yes, you can, but it requires some coding skills. Use `un_feedback_form_body` action and `un_validate_feedback` filter.
8. **Usernoise is missing /some feature/** - Feel free to contact me! I really appreciate new ideas.

== Screenshots ==

1. Usernoise form on Nikolay Karev`s site.
2. Admin bar with "New usernoise records" notification.
3. Admin interface

== Changelog ==

= 2.0.3 = 
* Persian language added - thanks to Morteza Nazari
* "Submit" button font size increased
* Updated Lithuanian translation

= 2.0.2 =
* Comment bug fixed
* Lithuanian translation added, thanks to Justinas Jakūnas

= 2.0.1 = 
* A fix for installations with custom url(s).
* Redirection from feedback items at admin improved.
* A fix for "Enable / Disable" usernoise option.
* Russian translation updated, .pot file updated.

= 2.0.0 =
* Better compatibility with IE8 when the button is placed at the left or at the right
* iFrame instead of inline div design should dramatically increase compatibility and make it 
bullet-proof
* Redirect back to feedback list when an item is saved.
* Updated Russian localization.
* .pot file updated
* Added Polish translation, thanks to Konrad Sztumski

= 1.0.5 =
* Minor Javascript API extension
* CSS compatibility fix for [Avisio theme](http://themeforest.net/item/avisio-business-and-portfolio/113278?ref=karevn)

= 1.0.4 =
* Workaround for Windows hosting problem
* Better info about Pro without throwing to the third party site.

= 1.0.3 =
* Upgrade ad only shown on Usernoise pages.

= 1.0.2 =
* 'Usernoise is disabled' notice removed for non-admin users at admin area.
* Broken theme detector added.
* Updated to latest Plugin Options Framework and HTML Helpers libraries.

= 1.0.1 =
* Bulgarian translation updated, thanks to Димитър Митев
* Broken Turkish translation removed
* Typo in "Your feedback has been sent" fixed
* Admin notices removed
* Plugin options framework updated
* Final cleanup before Pro version release

= 1.0 =
* CSS compatibility with PressWork
* Added Bulgarian translation, thanks to Димитър Митев.
* Added Hungarian translation, thanks to Bálint.
* Added Norwegian translation, thanks to Arnfinn Anda.
* Updated .po files - dear translator, please update your translations.
* Settings page totally rewritten using Plugin Options Framework.
* HTML Helpers updated to latest version.

= 0.5.1 =
* Chineze localization added, thanks to Song Zou
* Theme compatibility imporoved: Usernoise is loaded in header by default, but an option to load it in footer added. Loading in footer makes page loading a bit faster, but can be broken easily.
* Removed ads from admin.

= 0.5 =
* Swedish localization added, thanks to Mattias Thurfjell
* Multilinguality-friendliness. If you'll blank titles/texts at the admin area - default values will be loaded from localization specific to current WP language.
* Minor event system change.
* CSS compatibility fixed - reset code for height, text-shadow and box-shadow added
* Brazilian localization added, thanks to Ricardo Amaral

= 0.4.6.1 = 
* Usernoise auto-disables during update to 0.4.6 - fixed.
* Feedback button CSS compatibility fix.

= 0.4.6 =
* Finnish localization added
* Reverted to 0.4.4-like "Feedback" button position
* CSS compatibility improved for "close" button
* A bit of invalid HTML fixed
* Finnish localization updated
* A bug preventing Usernoise from loading with custom entry point files and DOING_AJAX defined fixed.

= 0.4.5 =
* Italian localization added
* Code cleanup and minor optimizations

= 0.4.4 =
* Compatibility fix should resolve incompatibility with themes having .left, .right classes defined.

= 0.4.3 =
* Ukrainian localization added, thanks to Vladyslav Cherednichenko.
* Security fix - dashboard widget should be only visible to editor and administrator users.

= 0.4.2.3 =
* Better compatibility with old jQuery versions.

= 0.4.2.1 =
* Hotfix - "can't view submitted feedback" problem resolved.

= 0.4.2 =
* Deutsch localization added, thanks to Alexander from [http://motday.tk/]
* Duplicated close buttons bug fixed.
* Theme compatibility CSS fixes.
* Conflicts with third party plugins / themes resolved by adding prefixed API.

= 0.4.1 = 
* Slight refactoring... as usually
* Compatibility with non-standard installs where plugins are not at wp-content
* Minor CSS compatibility fixes
* Spanish localization added, thanks to Dario Doidos
* Turkish localization added, thanks to Emre Aydin
* Added an option allowing to hide Usernoise button on mobile devices
* Direct link to usernoise settings added to admin bar
* Admin bar items not shown when Usernoise features are not available to the current user

= 0.4 =
* Post type changed to `un_feedback` to avoid conflicts.
* Added an option not to show feedback button border.
* Added a shortcut settings button at the feedback form. 
* Security hardening - now only administrators and editors can manage feedback.
* Feedback button removed from HTML code to improve compatibility
* Simplified localization loading

= 0.3.1 = 
* Admin area fix - feedback types were not shown in Firefox
* Submit feedback button compatibility with themes improved.
* feedback_type_slug WP_Query option added
* Portuguese localization added, thanks to rasilva
* Feedback button z-index increased for sake of compatibility with themes

= 0.3 =
* "Email feedback author" feature added.
* IE8 with left-positioned button fixed.
* JS code refactored totally, became much more extensible.
* Feedback form is loaded with separate ajax request now.
* Usernoise frontend code is not loaded at the backend now.

= 0.2.3 = 
* Minor optimization
* Compatibility with older jQuery versions < 1.4.1
* CSS code refactored
* Added "un_feedback_form_body" action to allow adding new fields.

= 0.2.2 =
* Dashboard widget added
* IE9 compatibility fix
* "Feedback button position" option added
* French localization added, thanks to Brad Coudray

= 0.2.1 =
* Added admin notifications settings
* Duplicated usernoise settings section link in admin UI to make it cleaner
* Bugfix: usernoise did not load jquery, relying on loading it by WP theme
* Feedback form style updated a bit
* Dutch localization added, thanks to Reggie Biemans
* Localization bugs fixed

= 0.2 =
* Customizable feedback button color
* Customizable form font
* Localization-ready
* Russian localization
* Added an option not to show email field
* Feedback is ordered by date descending by default
* Hooks added to single feedback item page
* Some bugfixes
* Added some hooks to get prepared for Usernoise Pro

= 0.1.1 =
* Minor bugfixes

= 0.1 = 
* Initial release
=== Simply Poll ===

Contributors: wolfiezero, olliea95, Fubra
Donate link: http://wolfiezero.com/donate/
Tags: poll, results, polls, polling, survey, simple, easy, quiz
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.4.1

Simply, it adds polling functionality to your WordPress site



== Description ==

Creating polls is now easy! With this plugin you can easily create a poll, add it to a page or post and users can instantly vote. Allows the creation of unlimited polls with up to 10 answers each. Theming is also possible by altering the files in `view/client` (though hope to improve theming greatly in future updates).



== Installation ==

1. Download and unzip
2. Upload to your `wp-content/plugins` folder
3. Activate from the dashboard
4. Go to the new Polls menu page



== Screenshots ==

1. Simply Poll's question screen with the default theme
2. Results once question has been answered



== Upgrade Notice ==

Please take careful note of the Changelog notes as file names and variable might have changed.



== Frequently Asked Questions ==

= I have an issue with Simply Poll plugin, where do I go to report the issue? =

There are a number of places, the most popular being on the [WordPress.org forum](http://wordpress.org/tags/simply-poll?forum_id=10), you can also report an issue on [Github page](https://github.com/WolfieZero/simply-poll/issues/) or [contact myself directly](http://wolfiezero.com/contact/).


= I'm also having issues with Simply Poll but would like to solve it myself, is there an easy way to debug? =

Sure is; go into the `config.php` file at the root of the Simply Poll plugin folder and changed the value of `SP_DEBUG` to `true`. This will log all background interactions to the `log` file in the root of Simply Poll plugin folder. This is done using the [Logger](https://github.com/WolfieZero/logger) class.


= Is there a bleeding edge version of Simply Poll? =
Yes there is! It's on [Github](https://github.com/WolfieZero/simply-poll/).



== Changelog ==

= 1.4.1 =
* Fixed issues from SVN update

= 1.4 =

* Fixed a number of bugs posted in the forum
* Added [Logger](https://github.com/WolfieZero/logger) to allow easier debugging
* Improved AJAX request (no longer using `wp-load.php`)
	* This has fixed PHP warnings to do with headers being sent already 
* Improved PHP and JS docs
* Improved names in CSS for less issues with native style-sheets
	* Also moved the location of the CSS files to their relevant theme folders
* Renamed the `page` folder to `view`
* Renamed the `user` folder to `client`

= 1.3.4 =

* Added translation support
* Polls now appear where short code is in content

= 1.3.3 =

* Fixed database creation issues and display SQL query to use if an error

= 1.3.2 =

* Fixed repo missing `poll-submit.php`

= 1.3.1 =

* Repo fixed
* Minor CSS changes

= 1.3 =

* Added plugin URI
* Added file 'poll-submit.php' to replace `poll-results.php`
* Added `wp_enqueue_script('jquery');` when shortcode is used
* Added [jQuery Validation Plugin](https://github.com/jzaefferer/jquery-validation) for admin
* Added [jqPlot](http://www.jqplot.com/) for admin results
* Added admin CSS
* Added default poll CSS
* Added page/user/poll-results.php to allow custom styling of poll results
* Added support for none JS clients
* Removed `"ENGINE = MYISAM'` from the database install
* Removed `"CHARSET = UTF-8"` from database install and now using `get_bloginfo('charset')`
* Updated file `poll-submit.php` to a better structure
* Updated the `SP_URL` constant to combat x-domain issues
* Updated the admin interface
* Fixed issue where cookie is set but poll option not selected
* Fixed issue where poll results wouldn't display after submit
* Improved code layout
* Improved the poll add/edit script

= 1.2 = 
* Skipped, 1.3 update was more significant

= 1.1 =

* Fix the limiting

= 1.0 =

* Initial release



== Roadmap ==

* Add export/import options
* Make the admin more "WordPress", or at least better looking!
* Remove limitation on poll numbers and not have the default as 10
* Add options to change strings "Vote" and "Total Votes"
* Make theming easier
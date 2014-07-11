=== A Year Before ===
Contributors: wuerzblog
Donate link: http://flattr.com/thing/313825/Wordpress-Plugin-A-Year-Before
Tags: date, posts, history, widget, time
Requires at least: 2.8.0
Tested up to: 3.9
Stable tag: 0.9.4

"A Year Before" shows a list of articles, which were written a certain time ago. So you can show in a history, what happend in your blog in the past

== Description ==

With "A Year Before" you can show the titles of the articles which were written a certain time ago. So you can show in a "historical corner", what happend in your blog e.g. 30 days, 6 months or a year before. You also can use the "anniversary-mode", which will display all the posts through the years, which were written on this day and month.

You can use it as a wordpress-widget or put it in your theme as a php-function with parameters.

== Installation ==

= Using widgets in wordpress =

1. Download the plugin and put the directory "a-year-before" in the plugin-folder of your wordpress-installation.
2. Then activate the plugin.
3. Go "Themes/Widgets" and pull the widget in the sidebar. Ready to go! Configure it, if you want.

=  Not using widgets in wordpress =

1. Download the plugin and put the file ayb_posts.php in the plugin-folder of your  Wordpress-installation.
2. Then activate the plugin.
3. In your template - e.g. the sidebar - you can insert the following PHP-code:

		<?php if (function_exists("ayb_posts")) { ?>
		<div class="einjahr">
		<h2>Vor einem Jahr</h2>
		  <ul>
			 <?php ayb_posts('day=7'); ?>
		  </ul>
		</div>
		<?php } ?>

== Configuration ==

= Using the widget =

Just click on the configuration-button of the widget an use the selfexplaining popup-dialog.
Use the output-pattern to define the look of the found articles. Possible patterns are:

* %title% The title of the article
* %link% The link to the article
* %date% The date of the article
* %excerpt% The excerpt of the article. You can define the number of displayed characters bei appending the number like %excerpt80%, which will display 80 characters.

= Not using the widget =

You can call the plugin with a PHP-statement in a Wordpress-Theme and pass some parameters in this scheme
parameter1=value1&parameter2=value2&parameter3=value3 ...

You can use the following parameters

* day : the number of days ago you want to show the articles.
* month : the number of month ago you want to show the articles.
* year : the number of years ago you want to show the articles.
* before : piece of HTML to insert before the title of the articles. Default `<li>`
* after: piece of HTML to insert after the title of the articles. Default `</li>`
* range: number of days the plugin will search back in the future (relative to the values of day, month and year above) for an article. Meant as a "round about this day"-feature. Default 0
* showdate: shows the date (showdate=1) before every title or not (showdate=0)
* dateformat : dateformat as used by PHP. Default ist the german shortform "d.m.y"
* notfound: the text the plugin will output, if no article is found on the defined date.
* anniversary: if set to 1, the plugin will display all articles ever blogged with the same number of day and month. The parameters "day", "month", "year" and "range" will be ignored if used.
* private: show private posts? 0: only public posts 1: private and public posts 2: only private posts. Default 0
* showpages: show pages? 0: No, 1: Yes. Default 0
* posts_max: number of articles to be shown. 0 means 'all'. Default 0

= Examples =

`ayb_posts("day=30&before=&after=<br />&showdate=0");`
Shows the titles of the articles written 30 days ago without showing the date. The articles will not been showed as a HTML-list but simply seperated by a linebreak `<br />`.

`ayb_posts("month=6&day=14&notfound=Nothing blogged on this day.");`
The titles of the articles written half a year and two weeks before, also showing the date . If there was no article written on that day, the output will be »Nothing blogged on this day.«

`ayb_posts("range=14&dateformat=y-m-d");`
Looks up a year back for written articles. If none are found, the plugin will check the next 14 days in the future. If a article is found on some of this days, all articles of this day will be listed with a "year-month-day"-format.

`ayb_posts("anniversary=1");`
Shows the title of all posts, which were posted on the same day in the same month, independend of the year. E.g. on chistmas day you will see all posts, which are posted on december 24th since the blog was started.

== Styling ==

If you like CSS, you can style the date with the class `ayb_date`, the link of the article with the class `ayb_link` and the notfound-message by using the class `ayb_notfound`.

== Uninstall ==
Deactivate the plugin, then select "delete" in the plugin-panel. The files *and* the options of this plugin will be deleted. Thank you for using "a year before". ;-)

== Upgrade Notice ==

= 0.9.4 =
Fix (again): Sometimes other post-types (like menu-items) were shown.

== Changelog ==

= 0.9.4 =
Fix (again): Again sometimes other post-types (like menu-items) were shown.

= 0.9.3 =
Fix: Error with PHP 5.3 when called by PHP-statement. Thanks to www.hisky.de for reporting the error

= 0.9.2 =

* Fix: Sometimes other post-types than posts and pages were shown

= 0.9.1 =

* Fix possible display-error in  plugin-list

= 0.9 =

* set number of articles to be listet
* various fixes
* changed url to my blog - http://herrthees.de is my new blog for programming, design, journalism, etc

= 0.8 =

* show pages or not
* public, private or both articles can be shown
* widget output can be edited with patterns %link%, %title%, %date%, %excerpt% (thanks to Marcus from themenfreund.de for the excerpt-idea)
* fixed bugs, i.e. timezone-bug
* use of wordpress's widget-class (plugin works now wordpress 2.8+ only)
* OOP-programming

= 0.7beta11 =

* added uninstall-feature for wordpress 2.7+. if the plugin is deleted, the options of this plugin will be deleted in the wordpress-database.

= 0.7beta10 =

* fix: check if functions are already declared (for multiple use of the plugin)

= 0.7beta9 =

* unknown changes

= 0.7beta8 =

* kicked out debug-messages ... =:-)

= 0.7beta7 =

* bit of cleanup
* make date-calculation gmt-sensitive. the wordpress-timezone-option is used now. should fix problems, where articles, written a few hours (the timezone-difference) before or after midnight are not displayed correctly. 
* some minor bugfixes

= 0.7beta4 =

* adding anniversary-mode. if anniversary=0, all articles matching the current day and the current month will be show, regardless of which year (beside the actual year of course). all settings of day, month, year and range will be ignored.

= 0.7beta3 =

* using the wordpress timezone-offset

= 0.7beta2 =

*  fixed: plugin generated invalid XHTML in some cases

= 0.7beta1 =

* added range-parameter. you can use e.g. year=1&range=7 if you want to look back one year for articles. if no article is found on this day, the plugin will look up to 7 days back and lists the articles of the day the first article was found.

= 0.6.1 =

* Fix for sidebars not named 'sidebar'

= 0.6 =

* Minor clean-up

= 0.6beta4 =

* Fixed finding localization files

= 0.6beta3 =

* Localization
* Added german language-file

= 0.6beta2 =

* Make sure the non-widget-use of the plugin

= 0.6beta1 =

* 'Widgetize' the plugin

= 0.5.3 =

* XHTML-Bugfix (unnecessary span)
* Bugfix PHP 5 Error with empty function-parameter

= 0.5.2 =

* Bugfix for more tolerant date-values (e.g. day > 364). Thanks to AlohaDan for hinting and testing.

= 0.5.1 =

* Adjustment for MySQL-versions older than MySQL 4.1.1

= 0.5 =

* First public beta

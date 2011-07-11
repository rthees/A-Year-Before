<?php
/*
 Plugin Name: A Year Before
 Version: 0.8alpha2
 Plugin URI: http://wuerzblog.de/2006/12/27/wordpress-plugin-a-year-before/
 Author: Ralf Thees
 Author URI: http://wuerzblog.de/
 Description: Gibt die Artikel an, die vor einem Jahr oder einer beliebigen Zeitspanne veröffentlicht wurden. <a href="http://flattr.com/thing/313825/Wordpress-Plugin-A-Year-Before" target="_blank">Donate/Spenden: Flattr</a>
 */

if (!defined('WP_CONTENT_URL'))
define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL'))
define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
if (!defined('WP_PLUGIN_DIR'))
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
if (!defined('WP_LANG_DIR'))
define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');

if (!class_exists('ayb_posts_class'))
{
	class ayb_posts_class extends WP_Widget
	{
		var $pattern = '<li>%date%: <a href="%link%" title="%excerpt%">%article%</a> (%date%)</li>';
		var $ayb_posts_domain = 'ayb_posts';
		var $excerpt_length=140;
			
			
		function ayb_posts_class()
		{
			load_plugin_textdomain( 'ayb_posts', false, dirname(plugin_basename(__FILE__)) .  '' );

			if (function_exists('register_uninstall_hook'))
			register_uninstall_hook(__FILE__, array(
			&$this,
                    'on_delete'
                    ));
                    $widget_ops = array(
                'classname' => 'ayb_posts',
                'description' => __('Show articles a certain periode of time before', 'ayb_posts')
                    );
                    $this->WP_Widget('ayb_posts', __('A Year Before'), $widget_ops);
		}

		function on_delete()
		{
			delete_option('ayb_posts');
		}

		function pattern_output()
		{
			$subpattern_array = array(
                '/\%title\%/',
                '/\%date\%/',
                '/\%link\%/',
				'/\%excerpt(\d*)\%/'
                );
                $var_array        = array(
                $this->ptitle,
                $this->datum,
                $this->plink,
                $this->excerpt
                );
                $r= preg_replace($subpattern_array, $var_array, $this->pattern);
                
                
                return $r;
		}

		function widget($args, $instance)
		{
			global $wpdb, $ayb_posts_domain;
			if (!function_exists('ayb_sgn'))
			{
				function ayb_sgn($number)
				{
					if ($number > 0)
					return "+";
					if ($number < 0)
					return "-";
					if ($number == 0)
					return "";
				}

				function current_time_fixed( $type, $gmt = 0 ) {
					$t =  ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
					switch ( $type ) {
						case 'mysql':
							return $t;
							break;
						case 'timestamp':
							return strtotime($t);
							break;
					}
				}

			} //!function_exists('ayb_sgn')
			$title      = $instance['title'];
			$day        = $instance['day'];
			$month      = $instance['month'];
			$year       = $instance['year'];
			$showdate   = $instance["showdate"];
			$dateformat = $instance["dateformat"];
			$notfound   = $instance["notfound"];
			$range      = $instance["range"];
			$private    = $instance["private"];
			$showpages    = $instance["showpages"];
			$anniv      = $instance["anniversary"];

			foreach ($instance as $key => $value)
			{
				switch ($key)
				{
					case "day":
						$dday = urldecode($value);
						break;
					case "month":
						$dmonth = urldecode($value);
						break;
					case "year":
						$dyear = urldecode($value);
						break;
					case "before":
						$before = urldecode($value);
						break;
					case "after":
						$after = urldecode($value);
						break;
					case "notfound":
						$notfound = htmlspecialchars(urldecode($value));
						break;
					case "showdate":
						$showdate = urldecode($value);
						break;
					case "dateformat":
						$dateformat = urldecode($value);
						break;
					case "range":
						$range = urldecode($value);
						break;
					case "anniversary":
						$anniv = urldecode($value);
						break;
				} //$key
			} //$instance as $key => $value

			$this->pattern       = empty($instance['pattern']) ? __('<li>Das war am %date%: Lies <a href="%link%" title="%excerpt%">%article%</a> (%date%)</li>', 'ayb_posts') : $instance['pattern'];
			$instance['pattern'] = $this->pattern;
			
			$ex=preg_match('/\%excerpt(\d*)\%/',$this->pattern,$matches);
			if ($matches[1]>0) $this->excerpt_length=$matches[1];

			$dateformat = empty($instance['dateformat']) ? __('Y-m-d', 'ayb_posts') : $instance['dateformat'];
			$showdate   = empty($instance['showdate']) ? '1' : $instance['showdate'];
			$notfound   = empty($instance['notfound']) ? __("No articles on this date.", 'ayb_posts') : $instance['notfound'];
			$before     = empty($instance['before']) ? '<li>' : $instance['before'];
			$after      = empty($instance['after']) ? '</li>' : $instance['after'];
			$excerpt_length      = empty($instance['excerpt_length']) ? 140 : $instance['excerpt_length'];
			$title          = empty($instance['title']) ? __('A year before', 'ayb_posts') : apply_filters('widget_title', $instance['title']);

			if ($dday == 0 && $dmonth == 0 && $dyear == 0)
			{
				$dyear = 1;
			} //$dday == 0 && $dmonth == 0 && $dyear == 0
			$ts= current_time_fixed('timestamp',0);
			//$ayb_tz     = ayb_sgn(get_option('gmt_offset') * (-1)) . get_option('gmt_offset') . " hour";
			$ayb_tz ="now";
			$ayb_tz_sec = get_option('gmt_offset') * 360000;

			$range_date1 = date("Y-m-d H:i:00", strtotime($ayb_tz, mktime(0, 0, 0, date("m",$ts) - $dmonth, date("d",$ts) - $dday, date("Y",$ts) - $dyear)));
			$range_date2 = date("Y-m-d H:i:59", strtotime($ayb_tz, mktime(23, 59, 59, date("m",$ts) - $dmonth, date("d",$ts) - $dday + $range, date("Y",$ts) - $dyear)));

				
			$month_day = date("m",$ts) . "-" . date("d",$ts);

			//$month_day= gmdate('m'). "-" . gmdate("d");
			switch ($private)
			{
				case  1: $post_status="(post_status='publish' OR post_status='private')";
				break;
				case 2: $post_status="post_status='private'";
				break;
				default: $post_status="post_status='publish'";
			}
			
		switch ($showpages)
			{
				case  0: $post_type="post_type<>'page'";
				break;
				case  1: $post_type="post_type<>''";
				break;
				default: $post_type="post_type<>'page'";
				
			}
			
			if ($anniv == 0)
			{
				$q = "SELECT ID, post_content, post_excerpt, post_title, post_date_gmt FROM $wpdb->posts WHERE $post_status AND $post_type AND post_password='' AND (post_date_gmt >= '" . $range_date1 . "' AND post_date_gmt <= '" . $range_date2 . "') ORDER BY post_date_gmt ASC";
			} //$anniv == 0
			else
			{
				$q = "SELECT ID, post_content, post_excerpt, post_title, post_date_gmt FROM $wpdb->posts WHERE $post_status AND $post_type AND  post_password='' AND   SUBSTRING(post_date,6,5) = '" . $month_day . "' AND post_date<CURDATE() ORDER BY post_date_gmt DESC";
			}

			$result    = $wpdb->get_results($q, object);
			$post_date = $post_date_gmt;
			if ($result)
			{
				$post_date    = $result[0]->post_date_gmt;
				$ts_post_date = gmmktime(0, 0, 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));
				$ts_date_old  = $ts_post_date;

				foreach ($result as $post)
				{
					$post_date = $post->post_date_gmt;
					$this->excerpt=$post->post_excerpt;
					if (empty($this->excerpt)) $this->excerpt= wp_html_excerpt(htmlspecialchars(strip_tags($post->post_content)),$this->excerpt_length)." &hellip;";	

					if ($showdate)
					{
						$post_date         = $post->post_date_gmt;
						$ts_post_date_comp = gmmktime(substr($post_date, 11, 2), substr($post_date, 14, 2), 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));
						$pdate             = '<span class="ayb_date">' . date($dateformat, $ts_post_date_comp) . "</span>";
					} //$showdate
					else
					{
						$pdate = '';
					}
					$ts_post_date = gmmktime(0, 0, 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));

					if ($ts_post_date != $ts_date_old && $range != 0)
					{
						//break;
					} //$ts_post_date != $ts_date_old && $range != 0
					else
					{
						$ts_date_old = $ts_post_date;
					}
					$this->datum  = $pdate;
					$this->plink  = get_permalink($post->ID);
					$this->ptitle = $post->post_title;
					

					$this->ayb_article_list .= $this->pattern_output();
					
				} //$result as $post
			} //$result
			else
			{
				if (!$anniv)
				{
					if ($showdate)
					{
						$pdate = '<span class="ayb_date">' . date($dateformat, gmmktime(0, 0, 0, date("m",$ts) - $dmonth, date("d",$ts) - $dday, date("Y",$ts) - $dyear)) . "</span>";
					} //$showdate
					else
					{
						$pdate = '';
					}
				} //!$anniv
				else
				{
					$pdate = '<span class="ayb_date">' . date($dateformat, gmmktime(0, 0, 0, date("m",$ts), date("d",$ts), date("Y",$ts))) . "</span>";
				}
				$this->ayb_article_list .= $before . $pdate . '<span class="ayb_notfound"> ' . $notfound . '</span>' . $after . "\r";

			}



			if ($instance["no_widget"])
			{
				echo $this->ayb_article_list;
			} //$instance["no_widget"]
			else
			{
				extract($args);

				$title = attribute_escape($instance['title']);
				echo $before_widget . $before_title . $title . $after_title;
				echo '<ul>' . $this->ayb_article_list . '</ul>';
				echo $after_widget;
				$this->ayb_article_list = '';
			}
		}

		function form($instance)
		{
			global $ayb_posts_domain;
			$defaults = array(
                'title' => __("A year before", 'ayb_posts'),
                'day' => '0',
                'month' => '0',
                'year' => '1',
                'range' => '0',
                'dateformat' => __('Y-m-d', 'ayb_posts'),
                'anniversary' => '0',
                'showdate' => '1',
				'private' => '0',
				'showpages' => '0',
                'notfound' => __('No articles on this date.','ayb_posts'),
                'pattern' => $this->pattern

			);

			$instance   = wp_parse_args((array) $instance, $defaults);
			$title      = strip_tags($instance['title']);
			$day        = strip_tags($instance['day']);
			$month      = $instance['month'];
			$year       = $instance['year'];
			$showdate   = $instance["showdate"];
			$dateformat = $instance["dateformat"];
			$notfound   = $instance["notfound"];
			$private   = $instance["private"];
			$showpages   = $instance["showpages"];
			$range      = $instance["range"];
			$anniv      = $instance["anniversary"];
			$pattern    = htmlspecialchars($instance["pattern"]);

			echo '<a href="http://flattr.com/thing/313825/Wordpress-Plugin-A-Year-Before" target="_blank"><img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>';			
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("title") . '">' . __('Title:', 'ayb_posts') . ' <input style="width: 200px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("day") . '">' . __('Days before:', 'ayb_posts') . ' <input style="width: 30px;" id="' . $this->get_field_id("day") . '" name="' . $this->get_field_name("day") . '" type="text" value="' . $day . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("month") . '">' . __('Months before:', 'ayb_posts') . ' <input style="width: 30px;" id="' . $this->get_field_id("month") . '" name="' . $this->get_field_name("month") . '" type="text" value="' . $month . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("year") . '">' . __('Years before:', 'ayb_posts') . ' <input style="width: 30px;" id="' . $this->get_field_id("year") . '" name="' . $this->get_field_name("year") . '" type="text" value="' . $year . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("range") . '">' . __('Lookup-range:', 'ayb_posts') . ' <input style="width: 30px;" id="' . $this->get_field_id("range") . '" name="' . $this->get_field_name("range") . '" type="text" value="' . $range . '" /></label></p>';
			//echo '<p style="text-align:right;"><label for="' . $this->get_field_id("showdate") . '">' . __('Show date:', 'ayb_posts') . ' <input style="width: 15px;" id="' . $this->get_field_id("showdate") . '" name="' . $this->get_field_name("showdate") . '" type="checkbox" value="1"' . (($showdate == 0) ? '' : 'checked') . ' /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("dateformat") . '">' . __('Dateformat:', 'ayb_posts') . ' <input style="width: 55px;" id="' . $this->get_field_id("dateformat") . '" name="' . $this->get_field_name("dateformat") . '" type="text" value="' . $dateformat . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("private") . '">' . __('Public or private articles?:', 'ayb_posts') . ' <select id="' . $this->get_field_id("private") . '" name="' . $this->get_field_name("private") . '" ><option value="0"'.(($private==0)?'selected':'').' >'.__('Only public','ayb_posts').'</option><option value="1"'.(($private==1)?'selected':'').'>'.__('Both','ayb_posts').'</option><option value="2"'.(($private==2)?'selected':'').'>'.__('Only private','ayb_posts').'</option></select></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("showpages") . '">' . __('Show pages?:', 'ayb_posts') . ' <select id="' . $this->get_field_id("showpages") . '" name="' . $this->get_field_name("showpages") . '" ><option value="0"'.(($showpages==0)?'selected':'').' >'.__('No','ayb_posts').'</option><option value="1"'.(($showpages==1)?'selected':'').'>'.__('Yes','ayb_posts').'</option></select></label></p>';
			
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("notfound") . '">' . __('Text, if no article found:', 'ayb_posts') . ' <input style="width: 200px;" id="' . $this->get_field_id("notfound") . '" name="' . $this->get_field_name("notfound") . '" type="text" value="' . $notfound . '" /></label></p>';
			echo '<p style="text-align:right;"><label for="' . $this->get_field_id("anniv") . '">' . __('Anniversary-Mode:', 'ayb_posts') . ' <input style="width: 15px;" id="' . $this->get_field_id("anniv") . '" name="' . $this->get_field_name("anniv") . '" type="checkbox" value="1" ' . (($anniv == 0) ? '' : 'checked') . ' /></label></p>';
			//echo '<p style="text-align:right;"><label for="' . $this->get_field_id("pattern") . '">' . __('Output-pattern:', 'ayb_posts') . ' <input style="width: 200px;" id="' . $this->get_field_id("pattern") . '" name="' . $this->get_field_name("pattern") . '" type="text" value="' . $pattern . '" /></label></p>';
			echo '<p style="text-align:right;"><label title="Use %title%, %date%, %link%, %excerpt%" for="' . $this->get_field_id("pattern") . '">' . __('Output-pattern:', 'ayb_posts') . ' <textarea style="width: 220px;" id="' . $this->get_field_id("pattern") . '" name="' . $this->get_field_name("pattern") . '" rows="4" >' . $pattern . '</textarea></label></p>';

		}


		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;

			$instance['title']       = strip_tags(stripslashes($new_instance['title']));
			$instance["day"]         = strip_tags(stripslashes($new_instance['day']));
			$instance["month"]       = strip_tags(stripslashes($new_instance['month']));
			$instance["year"]        = strip_tags(stripslashes($new_instance['year']));
			$instance["showdate"]    = strip_tags(stripslashes($new_instance['showdate']));
			$instance["dateformat"]  = strip_tags(stripslashes($new_instance['dateformat']));
			$instance["notfound"]    = strip_tags(stripslashes($new_instance['notfound']));
			$instance["range"]       = strip_tags(stripslashes($new_instance['range']));
			$instance["private"]     = strip_tags(stripslashes($new_instance['private']));
			$instance["showpages"]    = strip_tags(stripslashes($new_instance['showpages']));
			$instance["anniversary"] = strip_tags(stripslashes($new_instance['anniv']));
			$instance["pattern"]     = stripslashes($new_instance['pattern']);

			return $instance;
		}
	}
} //!class_exists('ayb_posts_class')

add_action('widgets_init', create_function('', 'return register_widget("ayb_posts_class");'));

function ayb_posts($ayb_para)
{
	$ayb_parameter = explode('&', $ayb_para);
	foreach ($ayb_parameter as $ayb_temp)
	{
		$b               = split('=', $ayb_temp);
		$instance[$b[0]] = $b[1];
	} //$ayb_parameter as $ayb_temp
	$widget_arr            = array();
	$ayb_man               = new ayb_posts_class;
	$instance["no_widget"] = true;
	$ayb_man->widget($widget_arr, $instance);
}
?>

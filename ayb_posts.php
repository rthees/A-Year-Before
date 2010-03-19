<?php
  /*
   Plugin Name: A Year Before
   Version: 0.8-alpha
   Plugin URI: http://wuerzblog.de/2006/12/27/wordpress-plugin-a-year-before/
   Author: Ralf Thees
   Author URI: http://wuerzblog.de/
   Description: Gibt die Artikel an, die vor einem Jahr oder einer beliebigen Zeitspanne veröffentlicht wurden.
   */
  
  // Pre-2.6 compatibility
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
  $ayb_posts_domain = 'ayb_posts';
  
  if (!class_exists('ayb_posts_class'))
    {
      /**
       * constructor
       */
      class ayb_posts_class extends WP_Widget
        {
          function ayb_posts_class()
            {
              if (function_exists('register_uninstall_hook'))
                  register_uninstall_hook(__FILE__, array(&$this, 'on_delete'));
              $widget_ops = array('classname' => 'ayb_posts', 'description' => __('Show articles a certain periode of time before', 'ayb_posts'));
              $this->WP_Widget('ayb_posts', __('A Year Before'), $widget_ops);
            } //function ayb_posts_class()
          
          function on_delete()
            {
              delete_option('ayb_posts');
            } //function on_delete()
          
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
                    } //function ayb_sgn($number)
                  } //if (!function_exists('ayb_sgn'))
                  $title = $instance['title'];
                  $day = $instance['day'];
                  $month = $instance['month'];
                  $year = $instance['year'];
                  $showdate = $instance["showdate"];
                  $dateformat = $instance["dateformat"];
                  $notfound = $instance["notfound"];
                  $range = $instance["range"];
                  $anniv = $instance["anniversary"];
                  
                  $before = '<li>';
                  $after = '</li>';
                  
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
                        } //switch ($key)
                    } //foreach ($instance as $key => $value)
                  
                  $title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
                  $entry_title = empty($instance['entry_title']) ? '&nbsp;' : apply_filters('widget_entry_title', $instance['entry_title']);
                  $comments_title = empty($instance['comments_title']) ? '&nbsp;' : apply_filters('widget_comments_title', $instance['comments_title']);
                  
                  if ($dday == 0 && $dmonth == 0 && $dyear == 0)
                    {
                      $dyear = 1;
                    } //if ($dday == 0 && $dmonth == 0 && $dyear == 0)
                  $ayb_tz = ayb_sgn(get_option('gmt_offset') * (-1)) . get_option('gmt_offset') . " hour";
                  $ayb_tz_sec = get_option('gmt_offset') * 360000;
                  
                  
                  $range_date1 = date("Y-m-d H:i:00", strtotime($ayb_tz, mktime(0, 0, 0, date("m") - $dmonth, date("d") - $dday, date("Y") - $dyear)));
                  $range_date2 = date("Y-m-d H:i:00", strtotime($ayb_tz, mktime(23, 59, 59, date("m") - $dmonth, date("d") - $dday + $range, date("Y") - $dyear)));
                  
                  $month_day = date("m") . "-" . date("d");
                  
                  
                  if ($anniv == 0)
                    {
                      $q = "SELECT ID, post_title, post_date_gmt FROM $wpdb->posts WHERE post_status='publish' AND post_password='' AND (post_date_gmt >= '" . $range_date1 . "' AND post_date_gmt <= '" . $range_date2 . "') ORDER BY post_date_gmt ASC";
                    } //if ($anniv == 0)
                  else
                    {
                      $q = "SELECT ID, post_title, post_date_gmt FROM $wpdb->posts WHERE post_status='publish' AND post_password='' AND   SUBSTRING(post_date,6,5) = '" . $month_day . "' AND post_date<CURDATE() ORDER BY post_date_gmt DESC";
                    } //else
                  //else
                  $result = $wpdb->get_results($q, object);
                  $post_date = $post_date_gmt;
                  echo $q;
                  
                  //Ausgabe für's Widget
                  if ($ayb_posts_is_widget)
                    {
                      echo $before_widget;
                      echo $before_title . $title . $after_title . "<ul>";
                    } //if ($ayb_posts_is_widget)                  
                  
                  if ($result)
                    {
                      $this->ayb_article_list .= "<ul>";
                      $post_date = $result[0]->post_date_gmt;
                      $ts_post_date = gmmktime(0, 0, 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));
                      $ts_date_old = $ts_post_date;
                      foreach ($result as $post)
                        {
                          $post_date = $post->post_date_gmt;
                          
                          if ($showdate)
                            {
                              $post_date = $post->post_date_gmt;
                              $ts_post_date_comp = gmmktime(substr($post_date, 11, 2), substr($post_date, 14, 2), 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));
                              $pdate = '<span class="ayb_date">' . date($dateformat, $ts_post_date_comp) . "</span> ";
                            } //if ($showdate)
                          //if ($showdate)
                          else
                            {
                              $pdate = '';
                            } //else
                          //else
                          $ts_post_date = gmmktime(0, 0, 0, substr($post_date, 5, 2), substr($post_date, 8, 2), substr($post_date, 0, 4));
                          
                          if ($ts_post_date != $ts_date_old && $range != 0)
                            {
                              break;
                            } //if ($ts_post_date != $ts_date_old && $range != 0)
                          //if ($ts_post_date != $ts_date_old && $range != 0)
                          else
                            {
                              $ts_date_old = $ts_post_date;
                            } //else
                          //else
                          $plink = get_permalink($post->ID);
                          $ptitle = $post->post_title;
                          $this->ayb_article_list .= $before . $pdate . '<a href="' . $plink . '" class="ayb_link">' . $ptitle . '</a>' . $after . "\r";
                        } //foreach ($result as $post)
                      //foreach ($result as $post)
                      } //if ($result)
                      else
                        {
                          // Not found
                          if (!$anniv)
                            {
                              if ($showdate)
                                {
                                  $pdate = '<span class="ayb_date">' . date($dateformat, gmmktime(0, 0, 0, date("m") - $dmonth, date("d") - $dday, date("Y") - $dyear)) . "</span> ";
                                } //if ($showdate)
                              //if ($showdate)
                              else
                                {
                                  $pdate = '';
                                } //else
                              //else
                              } //if (!$anniv)
                              else
                                {
                                  $pdate = '<span class="ayb_date">' . date($dateformat, gmmktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "</span> ";
                                } //else
                              //else
                              $this->ayb_article_list .= $before . $pdate . '<span class="ayb_notfound">' . $notfound . '</span>' . $after . "\r";
                            } //if (!$anniv)
                          //else
                          $this->ayb_article_list .= "</ul>";
                          if ($ayb_posts_is_widget)
                            {
                              echo "</ul>" . $after_widget;
                            } //if ($ayb_posts_is_widget)

                          
                          
                          // prints the widget
                          extract($args);
                          $title = attribute_escape($instance['title']);
                          echo $before_widget . $before_title . $title . $after_title;
                          //print_r($this->ayb_article_list);
                          echo $this->ayb_article_list;
                          echo "test";
                          echo $after_widget;
                          $this->ayb_article_list = '';
                        } //else
                      //function widget($args, $instance)
                      
                      function form($instance)
                        {
                          global $ayb_posts_domain;
                          //print_r($instance);
                          $defaults = array('title' => 'Example', 'day' => '0', 'month' => '0', 'year' => '1');
                          
                          $instance = wp_parse_args((array)$instance, $defaults);
                          //print_r($instance);
                          $title = strip_tags($instance['title']);
                          $day = strip_tags($instance['day']);
                          $month = $instance['month'];
                          $year = $instance['year'];
                          $showdate = $instance["showdate"];
                          $dateformat = $instance["dateformat"];
                          $notfound = $instance["notfound"];
                          $range = $instance["range"];
                          $anniv = $instance["anniversary"];
                          
                          //$title = htmlspecialchars($options['title'], ENT_QUOTES);
                          
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("title") . '">' . __('Title:', $ayb_posts_domain) . ' <input style="width: 200px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("day") . '">' . __('Days before:', $ayb_posts_domain) . ' <input style="width: 30px;" id="' . $this->get_field_id("day") . '" name="' . $this->get_field_name("day") . '" type="text" value="' . $day . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("month") . '">' . __('Months before:', $ayb_posts_domain) . ' <input style="width: 30px;" id="' . $this->get_field_id("month") . '" name="' . $this->get_field_name("month") . '" type="text" value="' . $month . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("year") . '">' . __('Years before:', $ayb_posts_domain) . ' <input style="width: 30px;" id="' . $this->get_field_id("year") . '" name="' . $this->get_field_name("year") . '" type="text" value="' . $year . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("range") . '">' . __('Lookup-range:', $ayb_posts_domain) . ' <input style="width: 30px;" id="' . $this->get_field_id("range") . '" name="' . $this->get_field_name("range") . '" type="text" value="' . $range . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("showdate") . '">' . __('Show date:', $ayb_posts_domain) . ' <input style="width: 15px;" id="' . $this->get_field_id("showdate") . '" name="' . $this->get_field_name("showdate") . '" type="checkbox" value="1"' . (($showdate == 0) ? '' : 'checked') . ' /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("dateformat") . '">' . __('Dateformat:', $ayb_posts_domain) . ' <input style="width: 55px;" id="' . $this->get_field_id("dateformat") . '" name="' . $this->get_field_name("dateformat") . '" type="text" value="' . $dateformat . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("notfound") . '">' . __('Text, if no article found:', $ayb_posts_domain) . ' <input style="width: 200px;" id="' . $this->get_field_id("notfound") . '" name="' . $this->get_field_name("notfound") . '" type="text" value="' . $notfound . '" /></label></p>';
                          echo '<p style="text-align:right;"><label for="' . $this->get_field_id("anniv") . '">' . __('Anniversary-Mode:', $ayb_posts_domain) . ' <input style="width: 15px;" id="' . $this->get_field_id("anniv") . '" name="' . $this->get_field_name("anniv") . '" type="checkbox" value="1" ' . (($anniv == 0) ? '' : 'checked') . ' /></label></p>';
                        } //function form($instance)

                      
                      function update($new_instance, $old_instance)
                        {
                          $instance = $old_instance;
                      
                          //print_r($instance);
                          $instance['title'] = strip_tags(stripslashes($new_instance['title']));
                          
                          $instance["day"] = strip_tags(stripslashes($new_instance['day']));
                          $instance["month"] = strip_tags(stripslashes($new_instance['month']));
                          $instance["year"] = strip_tags(stripslashes($new_instance['year']));
                          $instance["showdate"] = strip_tags(stripslashes($new_instance['showdate']));
                          $instance["dateformat"] = strip_tags(stripslashes($new_instance['dateformat']));
                          $instance["notfound"] = strip_tags(stripslashes($new_instance['notfound']));
                          $instance["range"] = strip_tags(stripslashes($new_instance['range']));
                          $instance["anniversary"] = strip_tags(stripslashes($new_instance['anniv']));
                          
                          //print_r($instance);
                          return $instance;
                        } //function update($new_instance, $old_instance)
                      } //class ayb_posts extends WP_Widget          
                    } //if ($result)
                  // END if class ayb:posts exits
                  
                  add_action('widgets_init', create_function('', 'return register_widget("ayb_posts_class");'));
?>
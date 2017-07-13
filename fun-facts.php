<?php

/*
 *
 *	Plugin Name: Fun Facts
 *	Plugin URI: http://www.joeswebtools.com/wordpress-plugins/fun-facts/
 *	Description: Adds a sidebar widget that display interesting, useless, weird and wonderful random fun facts. After activating the plugin, go to <a href="widgets.php">Appearance &rarr; Widgets</a> to install the widget and to <a href="options-general.php?page=fun-facts/fun-facts.php">Settings &rarr; Fun Facts</a> to add your own fun facts.
 *	Version: 2.0.1
 *	Author: Joe's Web Tools
 *	Author URI: http://www.joeswebtools.com/
 *
 *	Copyright (c) 2009 Joe's Web Tools. All Rights Reserved.
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *	If you are unable to comply with the terms of this license,
 *	contact the copyright holder for a commercial license.
 *
 *	We kindly ask that you keep links to Joe's Web Tools so
 *	other people can find out about this plugin.
 *
 */





/*
 *
 *	fun_facts_shortcode_handler
 *
 */

function fun_facts_shortcode_handler($atts, $content = nul) {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'fun_facts';

	// Get a fun fact
	$funfact_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
	$funfact_random = rand(0, $funfact_count - 1);
	$funfact_record = $wpdb->get_results("SELECT * FROM $table_name WHERE id={$funfact_random}");
	foreach($funfact_record as $funfacts_current) {
		$funfact_text = $funfacts_current->fun_fact;
	}

	// Create the content
	$content = '<table width="250" style="border-width: thin thin thin thin; border-style: solid solid solid solid;">';
	$content .= '<thead><tr><th><center><font face="arial" size="+1"><b>Fun Facts</b></center></font></th></tr></thead>';
	$content .= '<tbody><tr><td>';

	$content .= '<div style="text-align: justify;">' . $funfact_text . '</div>';

	$content .= '</td></tr></tbody>';
	$content .= '<tfoot><tr><td><div style="text-align: right;"><font face="arial" size="-3"><a href="http://www.joeswebtools.com/wordpress-plugins/fun-facts/" title="Fun Facts widget plugin for WordPress">Joe\'s</a></font></div></td></tr></tfoot>';
	$content .= '</table>';

	return $content;
}

add_shortcode('fun-facts', 'fun_facts_shortcode_handler');





/*
 *
 *	WP_Widget_Fun_Facts
 *
 */

class WP_Widget_Fun_Facts extends WP_Widget {

	function WP_Widget_Fun_Facts() {

		parent::WP_Widget(false, $name = 'Fun Facts');
	}

	function widget($args, $instance) {

		global $wpdb;

		// Create the table name
		$table_name = $wpdb->prefix . 'fun_facts';

		// Get a fun fact
		$funfact_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		$funfact_random = rand(0, $funfact_count - 1);
		$funfact_record = $wpdb->get_results("SELECT * FROM $table_name WHERE id={$funfact_random}");
		foreach($funfact_record as $funfacts_current) {
			$funfact_text = $funfacts_current->fun_fact;
		}

		extract($args);

		$option_title = apply_filters('widget_title', empty($instance['title']) ? 'Fun Facts' : $instance['title']);

		// Create the widget
		echo $before_widget;
		echo $before_title . $option_title . $after_title;

		echo '<div style="text-align: justify;">' . $funfact_text . '</div>';
		echo '<div style="text-align: right;"><font face="arial" size="-3"><a href="http://www.joeswebtools.com/wordpress-plugins/fun-facts/" title="Fun Facts widget plugin for WordPress">Joe\'s</a></font></div>';

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {

		return $new_instance;
	}

	function form($instance) {

		$instance = wp_parse_args((array)$instance, array('title' => 'Fun Facts'));
		$option_title = strip_tags($instance['title']);

		echo '<p>';
		echo 	'<label for="' . $this->get_field_id('title') . '">Title:</label>';
		echo 	'<input class="widefat" type="text" value="' . $option_title . '" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" />';
		echo '</p>';
	}
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Fun_Facts");'));





/*
 *
 *	fun_facts_page
 *
 */

function fun_facts_page() {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'fun_facts';

	// Update data
	if(isset($_POST['update'])) {

		// Truncate table
		$results = $wpdb->query("TRUNCATE TABLE $table_name");

		// Update
		if($funfacts_list = strip_tags(stripslashes($_POST['funfactslist']))) {

			$funfacts_array = explode("\n", $funfacts_list);
			sort($funfacts_array);

			foreach($funfacts_array as $funfacts_current) {
				$funfacts_current = trim($funfacts_current);
				if(!empty($funfacts_current)) {
					if(NULL == $wpdb->get_var("SELECT fun_fact FROM $table_name WHERE fun_fact='" . $wpdb->escape($funfacts_current) . "'")) {
						$wpdb->query("INSERT INTO $table_name(fun_fact) VALUES('" . $wpdb->escape($funfacts_current) . "')");
					}
				}
			}
		}
	}

	// Restore defaults
	if(isset($_POST['default'])) {

		// Truncate table
		$results = $wpdb->query("TRUNCATE TABLE $table_name");

		// Get the fun facts
		$funfacts_array = file(dirname(__FILE__) . '/fun-facts.dat');
		sort($funfacts_array);

		// Import the fun facts
		foreach($funfacts_array as $funfacts_current) {
			$funfacts_current = trim($funfacts_current);
			if(!empty($funfacts_current)) {
				if(strncmp($funfacts_current, '//', 2)) {
					if(NULL == $wpdb->get_var("SELECT fun_fact FROM $table_name WHERE fun_fact='" . $wpdb->escape($funfacts_current) . "'")) {
						$wpdb->query("INSERT INTO $table_name(fun_fact) VALUES('" . $wpdb->escape($funfacts_current) . "')");
					}
				}
			}
		}
	}

	// Page wrapper start
	echo '<div class="wrap">';

	// Title
	screen_icon();
	echo '<h2>Fun Facts</h2>';

	// Options
	echo	'<div id="poststuff" class="ui-sortable">';
	echo		'<div class="postbox opened">';
	echo			'<h3>Options</h3>';
	echo			'<div class="inside">';
	echo				'<form method="post">';
	echo					'<table class="form-table">';
	echo						'<tr>';
	echo							'<th scope="row" valign="top">';
	echo								'<b>Fun Facts List</b>';
	echo							'</th>';
	echo							'<td>';
	echo								'<textarea name="funfactslist" rows="15" cols="80" wrap="off" style="overflow: auto;">';
											$record = $wpdb->get_results("SELECT * FROM $table_name");
											foreach($record as $record) {
												echo $record->fun_fact . "\r\n";
											}
	echo								'</textarea><br />';
	echo								'Only one fun fact per line, not html code.';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<td colspan="2">';
	echo								'<input type="submit" class="button-primary"  name="update" value="Save Changes" />';
	echo								'&nbsp;&nbsp;&nbsp;';	
	echo								'<input type="submit" class="button-primary"  name="default" value="Restore defaults" />';
	echo							'</td>';
	echo						'</tr>';
	echo					'</table>';
	echo				'</form>';
	echo			'</div>';
	echo		'</div>';
	echo	'</div>';

	// About
	echo	'<div id="poststuff" class="ui-sortable">';
	echo		'<div class="postbox opened">';
	echo			'<h3>About</h3>';
	echo			'<div class="inside">';
	echo				'<form method="post">';
	echo					'<table  class="form-table">';
	echo						'<tr>';
	echo							'<th scope="row" valign="top">';
	echo								'<b>Like this plugin?</b>';
	echo							'</th>';
	echo							'<td>';
	echo								'Developing, maintaining and supporting this plugin requires time. Why not do any of the following:<br />';
	echo								'&nbsp;&bull;&nbsp;&nbsp;Check out our <a href="http://www.joeswebtools.com/wordpress-plugins/">other plugins</a>.<br />';
	echo								'&nbsp;&bull;&nbsp;&nbsp;Link to the <a href="http://www.joeswebtools.com/wordpress-plugins/fun-facts/">plugin homepage</a>, so other folks can find out about it.<br />';
	echo								'&nbsp;&bull;&nbsp;&nbsp;Give this plugin a good rating on <a href="http://wordpress.org/extend/plugins/fun-facts/">WordPress.org</a>.<br />';
	echo								'&nbsp;&bull;&nbsp;&nbsp;Support further development with a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5162912">donation</a>.<br />';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<th scope="row" valign="top">';
	echo								'<b>Need support?</b>';
	echo							'</th>';
	echo							'<td>';
	echo									'If you have any problems or good ideas, please talk about them on the <a href="http://www.joeswebtools.com/wordpress-plugins/fun-facts/">plugin homepage</a>.<br />';
	echo							'</td>';
	echo						'</tr>';
	echo						'<tr>';
	echo							'<th scope="row" valign="top">';
	echo								'<b>Credits</b>';
	echo							'</th>';
	echo							'<td>';
	echo									'<a href="http://www.joeswebtools.com/wordpress-plugins/fun-facts/">Fun Facts</a> is developped by Philippe Paquet for <a href="http://www.joeswebtools.com/">Joe\'s Web Tools</a>. This plugin is released under the GNU General Public License version 2. If you are unable to comply with the terms of the GNU General Public License, contact the copyright holder for a commercial license.<br />';
	echo							'</td>';
	echo						'</tr>';
	echo					'</table>';
	echo				'</form>';
	echo			'</div>';
	echo		'</div>';
	echo	'</div>';

	// Page wrapper end
	echo '</div>';

}





/*
 *
 *	fun_facts_add_menu
 *
 */

function fun_facts_add_menu() {

	// Add the menu page
	add_submenu_page('options-general.php', 'Fun Facts', 'Fun Facts', 10, __FILE__, 'fun_facts_page');
}

add_action('admin_menu', 'fun_facts_add_menu');





/*
 *
 *	fun_facts_activate
 *
 */

function fun_facts_activate() {

	global $wpdb;

	// Create the table name
	$table_name = $wpdb->prefix . 'fun_facts';

	// Create the table if it doesn't already exist
	$results = $wpdb->query("CREATE TABLE IF NOT EXISTS $table_name(id INT(11) NOT NULL AUTO_INCREMENT, fun_fact VARCHAR(2048) DEFAULT NULL, PRIMARY KEY (id), KEY fun_fact (fun_fact));");

	// Get the fun facts
	$funfacts_array = file(dirname(__FILE__) . '/fun-facts.dat');
	sort($funfacts_array);

	// Import the fun facts
	foreach($funfacts_array as $funfacts_current) {
		$funfacts_current = trim($funfacts_current);
		if(!empty($funfacts_current)) {
			if(strncmp($funfacts_current, '//', 2)) {
				if(NULL == $wpdb->get_var("SELECT fun_fact FROM $table_name WHERE fun_fact='" . $wpdb->escape($funfacts_current) . "'")) {
					$wpdb->query("INSERT INTO $table_name(fun_fact) VALUES('" . $wpdb->escape($funfacts_current) . "')");
				}
			}
		}
	}
}

register_activation_hook(__FILE__, 'fun_facts_activate');

?>
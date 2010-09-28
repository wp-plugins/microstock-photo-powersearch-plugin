<?php
/*
Plugin Name: Microstock Powersearch Plugin
Plugin URI: http://www.stockphotofeeds.com/what-is-microstock-photography/
Description: The Microstock Powersearch Plugin makes it quick and easy to find awesome and affordable stock photographs from several microstock photography agencies.
Version: 1.0.0
Author: Bob Davies
Author URI: http://www.stockphotofeeds.com
*/
/*
Copyright (C) 2009-2010 Bob Davies (admin@picNiche.com)
Based on SEO-Tool - Keyword Density Checker by Alexander Müller, (webmaster AT keyword-statistics DOT net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class SPFMicroPower {
	var	$plugin_version,
		$plugin_dir;

	function SPFMicroPower () {
		$this->plugin_version = '1.0.0';
		$this->plugin_dir = basename (dirname (__FILE__));
		// language file
		load_plugin_textdomain ('micro-power-search', 'wp-content/plugins/' . $this->plugin_dir);
		if (is_admin ()) {
			add_action ('admin_init', array ($this, 'init_settings'));
		}
		add_action ('admin_menu', array ($this, 'mps_power_search'));
		// integrate additional options menu
		add_action ('admin_menu', array ($this, 'additional_options_menu'));
		// for saving post-specific settings
		add_action ('admin_head', array ($this, 'admin_header'));
		add_action ('admin_footer', array ($this, 'admin_footer'));
		
		//add_filter('the_content', array ($this, 'filter_mps_script_output'));
	}
	
	// get plugins configuration
	function get_plugin_configuration () {
		global $wp_version;
		if (substr ($wp_version, 0, 3) >= '2.7')
			// WP 2.7+
			$options = get_option ('mps_power_search_configuration');
		else {
			$options = array ();
			$options['version'] = get_option ('mps_version');
			$options['default_language'] = get_option ('mps_default_language');
			$options['filter_stopwords'] = get_option ('mps_filter_stopwords');
			$options['max_list_items'] = get_option ('mps_max_list_items');
			$options['automatic_update'] = get_option ('mps_automatic_update');
			$options['update_interval'] = get_option ('mps_update_interval');
			$options['2word_phrases'] = get_option ('mps_2word_phrases');
			$options['3word_phrases'] = get_option ('mps_3word_phrases');
			$options['meta_keywords_count'] = get_option ('mps_meta_keywords_count');
			$options['max_keywords_count'] = get_option ('mps_max_keywords_count');
			$options['max_keywords_length'] = get_option ('mps_keywords_length');
			$options['authors_can_change_content_language'] = get_option ('mps_authors_can_change_content_language');
			$options['authors_can_disable_stopword_filter'] = get_option ('mps_authors_can_disable_stopword_filter');
		}
		return $options;
	}

	// update plugins configuration
	function set_plugin_configuration ($options) {
		global $wp_version;
		if (substr ($wp_version, 0, 3) >= '2.7')
			update_option ('mps_power_search_configuration', $options);
		else
			foreach ($options as $key => $value)
				update_option ('mps_' . $key, $value);
	}

	// Plugin options
	function init_settings () {
		if (function_exists ('register_setting')) {
			// for WP 2.7+
			if (!get_option('mps_power_search_configuration')) {
				// set default or import configuration
				$default_configuration = array (
					'version' => get_option('mps_version') ? get_option('mps_version') : $this->plugin_version,
					'default_language' => get_option('mps_default_language') ? get_option('mps_default_language') : 'en',
					'filter_stopwords' => get_option('mps_filter_stopwords') ? get_option('mps_filter_stopwords') : 1,
					'max_list_items' => get_option('mps_max_list_items') ? get_option('mps_max_list_items') : 5,
					'automatic_update' => get_option('mps_automatic_update') ? get_option('mps_automatic_update') : 1,
					'update_interval' => get_option('mps_update_interval') ? get_option('mps_update_interval') : 30,
					'2word_phrases' => get_option('mps_2word_phrases') ? get_option('mps_2word_phrases') : 1,
					'3word_phrases' => get_option('mps_3word_phrases') ? get_option('mps_3word_phrases') : 1,
					'meta_keywords_count' => get_option('mps_meta_keywords_count') ? get_option('mps_meta_keywords_count') : 8,
					'max_keywords_count' => get_option('mps_max_keywords_count') ? get_option('mps_max_keywords_count') : 12,
					'max_keywords_length' => get_option('mps_max_keywords_length') ? get_option('mps_keywords_length') : 40,
					'authors_can_change_content_language' => get_option('mps_authors_can_change_content_language') ? get_option('mps_authors_can_change_content_language') : 1,
					'authors_can_disable_stopword_filter' => get_option('mps_authors_can_disable_stopword_filter') ? get_option('mps_authors_can_disable_stopword_filter') : 0
				);
				add_option ('mps_power_search_configuration', $default_configuration);
				// drop older configurations
				delete_option ('mps_version');
				delete_option ('mps_default_language');
				delete_option ('mps_filter_stopwords');
				delete_option ('mps_max_list_items');
				delete_option ('mps_automatic_update');
				delete_option ('mps_update_interval');
				delete_option ('mps_2word_phrases');
				delete_option ('mps_3word_phrases');
				delete_option ('mps_meta_keywords_count');
				delete_option ('mps_max_keywords_count');
				delete_option ('mps_keywords_length');
				delete_option ('mps_authors_can_change_content_language');
				delete_option ('mps_authors_can_disable_stopword_filter');
			}
			register_setting ('plugin_options', 'mps_power_search_configuration');
		}
		else {
			// and for older versions
			if (!get_option('mps_default_language')) {
				add_option ('mps_version', $this->plugin_version);
				add_option ('mps_default_language', 'en');
				add_option ('mps_filter_stopwords', 1);
				add_option ('mps_max_list_items', 10);
				add_option ('mps_automatic_update', 1);
				add_option ('mps_update_interval', 30);
				add_option ('mps_2word_phrases', 1);
				add_option ('mps_3word_phrases', 1);
				add_option ('mps_meta_keywords_count', 8);
				add_option ('mps_max_keywords_count', 12);
				add_option ('mps_keywords_length', 40);
				add_option ('mps_authors_can_change_content_language', 0);
				add_option ('mps_authors_can_disable_stopword_filter', 0);
			}
		}
	}

	function is_author_not_admin () {
		global $current_user;
		$rval = $current_user->caps['author'] == 1 && $current_user->caps['administrator'] != 1;
		return $rval;
	}

	function is_contributor () {
		global $current_user;
		return $current_user->caps['contributor'] == 1 && $current_user->caps['administrator'] != 1;
	}

	// Plugin Output
	function post_mps_power_search () {
		global $post;
		$meta = get_post_meta ($post->ID, 'mps_metadata', true);
		$options = $this->get_plugin_configuration (); ?>
		<table style="width:100%">
			<tr>
				<td style="width:25%">
					<label for="mpslang"><?php _e('Language', 'micro-power-search') ?>:</label>
					<?php if ((($this->is_author_not_admin() || $this->is_contributor()) && intval ($options['authors_can_change_content_language']) != 1)) { ?>
					<input type="hidden" name="mpslang" id="mpslang" value="<?php echo $meta['lang'] ? $meta['lang'] : $options['default_language'] ?>" />
					<select disabled="disabled" id="mpslang_view" name="mpslang_view" onchange="mps_updateTextInfo()">
					<?php } else { ?>
					<select id="mpslang" name="mpslang" onchange="mps_updateTextInfo()">
					<?php } ?>
						<option value="en" <?php echo $meta['lang'] == 'en' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'en' ? 'selected="selected"' : '' ?>>en</option>
						<option value="da" <?php echo $meta['lang'] == 'da' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'da' ? 'selected="selected"' : '' ?>>da</option>
						<option value="de" <?php echo $meta['lang'] == 'de' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'de' ? 'selected="selected"' : '' ?>>de</option>
						<option value="es" <?php echo $meta['lang'] == 'es' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'es' ? 'selected="selected"' : '' ?>>es</option>
						<option value="fr" <?php echo $meta['lang'] == 'fr' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'fr' ? 'selected="selected"' : '' ?>>fr</option>
						<option value="nl" <?php echo $meta['lang'] == 'nl' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'nl' ? 'selected="selected"' : '' ?>>nl</option>
						<option value="pl" <?php echo $meta['lang'] == 'pl' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'pl' ? 'selected="selected"' : '' ?>>pl</option>
						<option value="pt-br" <?php echo $meta['lang'] == 'pt-br' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'pt-br' ? 'selected="selected"' : '' ?>>pt-br</option>
						<option value="tr" <?php echo $meta['lang'] == 'tr' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'tr' ? 'selected="selected"' : '' ?>>tr</option>
					</select>
				</td>
				<td><div class="submit"><input class="right" type="button" name="mpsupdate" onclick="mps_updateTextInfo()" value=" <?php _e('Get Search Suggestions') ?> " title="Get a list of common terms within your post content (works best above 200 or so words)."/></div></td>
				<td style="text-align: right;">
					<input id="MicroSearchText" type="text" value="Enter Query:" onfocus="if(this.value=='Enter Query:') { this.value=''; } else { this.select(); }" onblur="if(this.value=='') { this.value='Enter Query:'; } mspsaveLastKnownSearch();" onkeypress="if(event.charCode == 13) { msprunSearch(); document.getElementById('MicroSearchText').focus(); this.select(); }" />
					<div class="submit">
						<input id="MicroSearchButton" class="right" type="button" name="mpsrunsearch" onclick="msprunSearch();" value=" <?php _e('Search') ?> " title="Search for Microstock Images"/>
					</div>
				</td>
			</tr>
		</table>
		<?php if ($options['automatic_update']) echo '<script type="text/javascript">var mps_updateInterval = window.setInterval (\'mps_updateTextInfo()\', ' . $options['update_interval'] . '000);</script>' ?>
		<hr />
		<div id="mpsstats">
			<div id="MicroImagePreview" onclick="msphidePreviewImage();">
				<div id="MicroImagePreview_Container">
					<img id="MicroImagePreview_Image"  onload="if(this.attachEvent) { /*IE7*/ this.attachEvent('ondragstart', function() { mps_setDragData(event, 'Using these images without purchasing a license is both illegal and traceable.'); }); }" ondragstart="mps_setDragData(event, 'Using these images without purchasing a license is both illegal and traceable.')" onclick="mspopenPreviewImageAgencyPage(this);" title="Click on the image or info button to visit this image on the web (in a new tab), or click on the background to remove this preview overlay." />
					<div id="MicroImagePreview_Info">
						<span id="MicroImageBuyInfoButton" class="PreviewImageButton" onclick="mspopenPreviewImageAgencyPage(this);"></span>
						<div class="PreviewImageButton" id="MicroImagePreview_InfoDescription" onclick="mspopenPreviewImageAgencyPage(this);" ></div>
						<iframe id="MicroFacebookLikeFrame" src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.stockphotofeeds.com&amp;layout=button_count&amp;show_faces=false&amp;width=54&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=25&amp;ref=spf_micro_unset" scrolling="no" frameborder="0" style="overflow:hidden;width:54px; height:25px;" allowTransparency="true"></iframe>
						<br class="clear" />
					</div>
				</div>
			</div>
			<div id="mpssearches"></div>
			<div id="MicroTools">
				<select id="MicroSearchType" onchange="mspsaveSearchType(event);">
					<option value="engine" selected="true">Best Match</option>
					<option value="new">New Images</option>
				</select>
				<select id="MicroSearchCount" title="Search Results For Each Library" onchange="mspsaveNumResults(event);">
					<option value="5" selected="true">5</option>
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="40">40</option>
				</select>
				<span id="MicroAgencies">
					<label class="simpleButton" for="MicroSearchDreamstime"><input siteid="Dreamstime" onchange="mspsaveAgencyDefaults(event);" type="checkbox" id="MicroSearchDreamstime" title="Dreamstime"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/dreamstime.ico" title="Dreamstime"></input></label>
					<label class="simpleButton" for="MicroSearchFotolia"><input siteid="Fotolia" onchange="mspsaveAgencyDefaults(event);" type="checkbox" id="MicroSearchFotolia" title="Fotolia"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/fotolia.ico" title="Fotolia"></input></label>
					<label class="simpleButton" for="MicroSearchShutterstock"><input siteid="Shutterstock" onchange="mspsaveAgencyDefaults(event);" type="checkbox" id="MicroSearchShutterstock" title="Shutterstock"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/shutterstock.ico" title="Shutterstock"></input></label>
					<label class="simpleButton" for="MicroSearch123RF"><input siteid="123RF" onchange="mspsaveAgencyDefaults(event);" type="checkbox" id="MicroSearch123RF" title="123 Royalty Free"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/123rf.png" title="123 Royalty Free"></input></label>
					<label class="simpleButton" for="MicroSearchBigstock"><input siteid="Bigstock" onchange="mspsaveAgencyDefaults(event);" type="checkbox" id="MicroSearchBigstock" title="Bigstock"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/bigstockphoto.ico" title="Bigstock"></input></label>
					<span id="MicroMoreLink" onclick="jQuery('#MicroMoreLinksBox').toggle();if(jQuery(this).text() == 'More...') { jQuery(this).text('...Less'); } else { jQuery(this).text('More...')  }">More...</span>
				</span>
			</div>
			<div id="MicroMoreLinksBox">
				<span>The Microstock Powersearch plugin is fairly simple search tool... if you would like a plugin which is fully integrated into the licensing process, try the <a href="http://www.microstockplugin.com/" target="_blank" title="Opens in a new tab">Microstock Photo wordpress plugin</a> which supports iStockphoto and Fotolia with extra tools and speeds up your image buying even more.</span>
			</div>
			<div id="MicroResults">
				<span id="MicroResultsDreamstime"></span>
				<span id="MicroResultsFotolia"></span>
				<span id="MicroResultsShutterstock"></span>
				<span id="MicroResults123RF"></span>
				<span id="MicroResultsBigstock"></span>
			</div>
			<div id="MicroOptions">
				<div id="MicroLinksPanel">
					<span style="display:none;" onclick="mspopenRequestsPanel()" id="MicroImageRequests" />Image Requests</span>
					<span onclick="mspopenMicroLink('http://www.stockphotofeeds.com');" title="Search and Subscribe to RSS feeds of the latest stock images.">StockPhotoFeeds.com</span>
					<span onclick="mspopenMicroLink('http://twitter.com/bobbigmac');" title="Follow BobBigMac, the creator of this extension on Twitter"><img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/agency/twitter.ico" /></span>
					<span title="Please recommend this extension to your friends." style="max-height: 22px;max-width:50px;height: 22px;width:50px;"><iframe class="" id="MicroFacebookLikeSPFFrame" src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.stockphotofeeds.com&amp;layout=button_count&amp;show_faces=false&amp;width=50&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=22&amp;ref=spf_micro_bar" scrolling="no" frameborder="0" style="width: 50px; overflow: hidden; height:22px;" allowTransparency="true"></iframe></span>
				</div>
				<div id="MicroStatusPanel">
					<span id="MicroStatusImage">
						<img src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/loading.gif" alt="Loading... " />
					</span>
					<span id="MicroStatusMessage">
					</span>
				</div>
			</div>
		</div>
		<?php
	}

	function post_mps_power_search_div () {
		echo '<div class="dbx-b-ox-wrapper">' .
		     '<fieldset id="mpspowersearch" class="dbx-box">' .
		     '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . 
		     __('Microstock Power', 'micro-power-search') . "</h3></div>" .   
		     '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
		$this->post_mps_power_search ();
		echo "</div></div></fieldset></div>";
	}

	function mps_power_search () {
		if (function_exists ('add_meta_box')) {
			// only works with WP 2.5+
			add_meta_box ('mpspowersearchbox', __('Microstock Power', 'micro-power-search'), array ($this, 'post_mps_power_search'), 'post', 'normal', 'core');
			add_meta_box ('mpspowersearchbox', __('Microstock Power', 'micro-power-search'), array ($this, 'post_mps_power_search'), 'page', 'normal', 'core');
		} else {
			// older versions
			add_action ('dbx_post_advanced', array ($this, 'post_mps_power_search_div'));
			add_action ('dbx_page_advanced', array ($this, 'post_mps_power_search_div'));
		}
	}

	// add pages for plugins additional options
	function additional_options_menu () {
		// Microstock Power Search
		add_submenu_page ('options-general.php', __('Microstock Power', 'micro-power-search'), __('Microstock Power', 'micro-power-search'), 'administrator', 'mps-additional-menu', array ($this, 'additional_options_mps_power_search'));
	}

	// settings for Microstock Power Search
	function additional_options_mps_power_search () {
		$options = $this->get_plugin_configuration ();
		if ($_POST['mps_update_options'] == 1) {
			if (!wp_verify_nonce ($_POST['mps_configuration_nonce'], 'mps_configuration_nonce')) {
				$err = 1;
				$errmsg = __('Nonce verification failed:', 'micro-power-search') . ' ' . __('Error while attempting to save plugin configuration!', 'micro-power-search');
			}
			else {
				$options['authors_can_change_content_language'] = $options['authors_can_disable_stopword_filter'] = $options['filter_stopwords'] = $options['automatic_update'] = $options['2word_phrases'] = $options['3word_phrases'] = 0;
				foreach ($_POST as $key => $value) {
					switch ($key) {
						case 'mps_filter_stopwords':
							$options['filter_stopwords'] = 1;
							break;
						case 'mps_automatic_update':
							$options['automatic_update'] = 1;
							break;
						case 'mps_2word_phrases':
							$options['2word_phrases'] = 1;
							break;
						case 'mps_3word_phrases':
							$options['3word_phrases'] = 1;
							break;
						case 'mps_authors_can_change_content_language':
							$options['authors_can_change_content_language'] = 1;
							break;
						case 'mps_authors_can_disable_stopword_filter':
							$options['authors_can_disable_stopword_filter'] = 0;
							break;
						case 'mps_max_list_items':
						case 'mps_update_interval':
						case 'mps_usertoken':
							if (preg_match ('/^[0-9]+$/', $value))
								$options['mps_usertoken'] = $value;
							break;
						case 'mps_min_words':
							if (preg_match ('/[0-9]+/', $value))
								$options[substr ($key, 4)] = $value;
							else
								$err = 1;
							break;
						case 'mps_default_language':
							if (preg_match ('/[a-z-]+/', $key))
								$options['default_language'] = $value;
							else
								$err = 1;
							break;
					}
				}
			}
			// show status message
			if (!isset ($err)) { 
				// update options in database if there was no input error
				$this->set_plugin_configuration ($options);
				?> <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div> <?php
			}
			else { ?> <div class="error"><p><strong><?php echo __('Options not saved', 'micro-power-search') . (isset ($errmsg) ? ' - ' . $errmsg : '!'); ?></strong></p></div> <?php }
		} ?>
		<div class="wrap"><div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php _e('Microstock Power', 'micro-power-search') ?></h2>
			<form name="mps_options" method="post" action="">
				<input type="hidden" name="mps_update_options" value="1" />
				<input type="hidden" name="mps_configuration_nonce" value="<?php echo wp_create_nonce ('mps_configuration_nonce') ?>" />
				<table class="form-table">
					<tr><td colspan="2"><span class="description"><?php echo __('With these options you can configure the Microstock Powersearch plugin.', 'micro-power-search') ?></span></td></tr>
					<tr valign="top">
						<th scope="row"><?php _e('Default language', 'micro-power-search') ?></th>
						<td>
							<select name="mps_default_language">
								<option value="en" <?php echo $options['default_language'] == 'en' ? ' selected="selected"' : '' ?>>en</option>
								<option value="da" <?php echo $options['default_language'] == 'da' ? ' selected="selected"' : '' ?>>da</option>
								<option value="de" <?php echo $options['default_language'] == 'de' ? ' selected="selected"' : '' ?>>de</option>
								<option value="es" <?php echo $options['default_language'] == 'es' ? ' selected="selected"' : '' ?>>es</option>
								<option value="fr" <?php echo $options['default_language'] == 'fr' ? ' selected="selected"' : '' ?>>fr</option>
								<option value="nl" <?php echo $options['default_language'] == 'nl' ? ' selected="selected"' : '' ?>>nl</option>
								<option value="pl" <?php echo $options['default_language'] == 'pl' ? ' selected="selected"' : '' ?>>pl</option>
								<option value="pt-br" <?php echo $options['default_language'] == 'pt-br' ? ' selected="selected"' : '' ?>>pt-br</option>
								<option value="tr" <?php echo $options['default_language'] == 'tr' ? ' selected="selected"' : '' ?>>tr</option>
							</select>
							<span class="description"><?php _e('Default language for filtering stopwords', 'micro-power-search') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Automatic Update', 'micro-power-search') ?></th>
						<td>
							<input type="checkbox" name="mps_automatic_update" value="1" <?php echo $options['automatic_update'] == 1 ? 'checked="checked"' : '' ?> /><br/>
							<span class="description"><?php _e('Should the search suggestions list refresh automatically? Turn off this option, if you have got slow JavaScript-Performance and use the button in the various edit-views instead!', 'micro-power-search') ?></span><br/>
							<?php _e('every', 'micro-power-search') ?> <select name="mps_update_interval">
								<?php for ($i = 10; $i <= 180; $i+=10) echo '<option value="' . $i . '" ' . ($options['update_interval'] == $i ? 'selected="selected"' : '') . '>' . $i . '</option>'; ?>
							</select> <?php _e('seconds', 'micro-power-search') ?>
							<span class="description"><?php _e('Number of seconds between the updates', 'micro-power-search') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Authors can', 'micro-power-search') ?></th>
						<td>
							<input type="checkbox" name="mps_authors_can_change_content_language" value="1" <?php echo $options['authors_can_change_content_language'] == 1 ? 'checked="checked"' : '' ?> /> <?php _e('change the content language', 'micro-power-search') ?><br/>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update') ?>" /></p>
			</form>
		</div> <?php
	}

	// Header for stylesheets and scripts
	function admin_header () {
		global $wp;
		?>
		<script src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/stopwords.js" type="text/javascript"></script>
		<script src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/textstat.js" type="text/javascript"></script>
		<script src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/micro_funcs.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="../wp-content/plugins/<?php echo $this->plugin_dir ?>/micro_style.css" />
		
		<style type="text/css">
		/* <![CDATA[ */
		#MicroImagePreview { background-image: url('../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/black75.png'); }
		#MicroImagePreview_Info { background-image: url('../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/black75.png'); }
		#MicroImageBuyInfoButton { background-image:url('../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/greenInfo.png'); }
		#MicroImageBuyInfoButton:hover { background-image:url('../wp-content/plugins/<?php echo $this->plugin_dir ?>/images/greenInfo_over.png'); }
		/* ]]> */
		</style>
		<?php
	}

	// Footer
	function admin_footer () {
		global $post;
		$meta = get_post_meta ($post->ID, 'mps_metadata', true);
		$options = $this->get_plugin_configuration ();?>
		<script type="text/javascript">
		/* <![CDATA[ */
		function mps_updateTextInfo (callbackFunc) {
			if (!document.getElementById ('mpslang').value)
				return;
			var lang = document.getElementById ('mpslang').value;
			
			var titlefield = document.getElementById('title');
			var textfield = document.getElementById ('content');
			if (!textfield || typeof textfield != 'object' || textfield.type != 'textarea')
				return;
			if (!typeof lang == 'string')
				return;
			if (textfield.lang)
				if (typeof stopwords[textfield.lang] == 'object')
					language = textfield.lang;
			var template = '<?php _e('Image Searches', 'micro-power-search') ?>: ' +
						'<span id="mps_search_results">' + 
						'[MULTI:10]' +
						'</span>';
			// replace template variables
			// do we have an instance of tinyMCE?
			if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor)
				// need to save the content of editor before we can read it
				tinyMCE.triggerSave();
			// get content to analyze out of the textarea and filter caption-blocks before analysis
			var t = new TextStatistics (((titlefield && titlefield.value) ? titlefield.value + ' ' : '') + textfield.value.replace(/\[caption.*caption=\"([^"]*)\"[^\]]*](.*)\[\/caption\]/ig, " $1 $2 ").replace(/\[picblock.*?\]/ig, " "), lang);
			if (template.match (/\[WORDCOUNT\]/ig))
				template = template.replace (/\[WORDCOUNT\]/ig, t.getWordCount ());
			if (template.match (/\[WORDCOUNT_FILTERED\]/ig))
				template = template.replace (/\[WORDCOUNT_FILTERED\]/ig, t.getWordCount (true));
			if (template.match (/\[WORDCOUNT_DIFFERENT\]/ig))
				template = template.replace (/\[WORDCOUNT_DIFFERENT\]/ig, t.getDifferentWordCount ());
			if (template.match (/\[WORDCOUNT_DIFFERENT_FILTERED\]/ig))
				template = template.replace (/\[WORDCOUNT_DIFFERENT_FILTERED\]/ig, t.getDifferentWordCount (true));
			if (template.match (/\[WORDCOUNT_STOPWORDS\]/ig))
				template = template.replace (/\[WORDCOUNT_STOPWORDS\]/ig, t.getStopWordCount ());
			if (template.match (/\[LANGUAGE\]/ig))
				template = template.replace (/\[LANGUAGE\]/ig, t.getLanguage ());
			if (template.match (/\[KEYWORDS:([0-9]+)\]/ig)) {
				var keycount = parseInt (RegExp.$1);
				template = template.replace (/\[KEYWORDS:[0-9]+\]/ig, t.getKeywordList (keycount));
			}
			if (template.match (/\[MULTI:([0-9]+)\]/ig)) {
				if(typeof(mps_addedImageSearches) == 'undefined')
				{
					mps_addedImageSearches = [];
				}
				var stats1 = t.getStats (1, true);
				var stats2 = t.getStats (2, true);
				var stats3 = t.getStats (3, true);
				stats3.keys = stats3.keys.concat(stats2.keys.concat(stats1.keys));
				var cleanKeys = [];
				var maxMatches = parseInt(RegExp.$1);
				maxMatches = ((maxMatches) ? maxMatches : 10) + mps_addedImageSearches.length;
				for(var currKey = 0; currKey < stats3.keys.length && currKey < maxMatches; currKey++)
				{
					var currKeyString = stats3.keys[currKey].getKey();
					var safeCurrKeyString = currKeyString.replace(' ', '_');
					if(!mps_addedImageSearches[safeCurrKeyString])
					{
						cleanKeys.push('<a id="mpsImageRequestLink_' + currKeyString.replace(' ', '_') + '" onclick="javascript:msprunSearch(\'' + currKeyString + '\');return false;">' + currKeyString + '</a>');
					}
				}
				template = template.replace(/\[MULTI:[0-9]+\]/ig, cleanKeys.join(' '));
			}
			var startString = '';
			// output of collected information
			document.getElementById ('mpssearches').innerHTML = template;
			if(callbackFunc && typeof(callbackFunc) == 'function')
			{
				callbackFunc();
			}
		}
		
		function mps_insertRandomImageAtCursor()
		{
			var currResults = jQuery('#mps_search_results a');
			if(!currResults || (currResults && currResults.length < 1))
			{
				if(!noImagesLeft)
				{
					mps_updateTextInfo(function() { mps_insertRandomImageAtCursor(); });
				}
			}
			else if(currResults)
			{
				mps_addAtCursorOnLoad = true;
				currResults[0].onclick();
			}
		}
		/* ]]> */
		</script>
		<?php
	}
}
$SPFMicroPower = new SPFMicroPower ();

?>
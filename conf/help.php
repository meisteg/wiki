<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version     $Id: help.php,v 1.10 2005/11/20 18:45:05 blindman1344 Exp $
 */

$add_to_title = 'Add Wiki Page Title to Site Title';
$add_to_title_content = 'Enabling this setting will add the current wiki page title to the site title which appears in the browser title bar.  The site title is sometimes used in themes meaning this setting would add the wiki page title to the theme as well.';

$admin_email = 'Wiki Administrator Email';
$admin_email_content = 'Enter in the email address of the Wiki administrator, if he/she is different than the overall phpWebSite administrator.  If this field is left blank, then the contact email address in the User control panel\'s Settings screen will be used.';

$allow_anon_view = 'Allow Anonymous Viewing';
$allow_anon_view_content = 'Enabling this setting will allow all visitors to view the wiki.  When disabled, only registered users can view the wiki.';

$allow_bbcode = 'BBCode Parser';
$allow_bbcode_content = 'When enabled, the page text will also be parsed by the BBCode parser instead of just the Text_Wiki parser. Keep in mind that everything you can do with BBCode can be done with wikitax.';

$allow_image_upload = 'Allow Image Uploads';
$allow_image_upload_content = 'Enabling this setting will allow all registered users to upload images.  When disabled, only registered users with admin privileges can upload images.  Anonymous visitors can never upload images.';

$allow_page_edit = 'Allow Page Edits';
$allow_page_edit_content = 'Enabling this setting will allow all registered users to edit pages.  When disabled, only registered users with admin privileges can edit pages. Anonymous visitors can never edit pages.';

$default_page = 'Default Page';
$default_page_content = 'The default page to display when no instructions are passed to the Wiki module.';

$diff = 'Compare To';
$diff_content = 'Here is where you can see what has changed between different versions of a page.<br /><br /><b>Compare To Current</b><br /> Selecting one of the "Current" links will show you what text has been changed between the corresponding page and the current version.<br /><br /><b>Compare To Previous</b><br />Selecting one of the "Previous" links will show you what text has been changed between the corresponding page and the next oldest version.';

$discussion = 'Discussion';
$discussion_content = 'If you have the comments module installed, you can enable the discussion feature.  It allows vistors to comment on individual pages.  This feature can be enabled for registered users only or for everyone who visits this site.<br /><br />Note: If you enable anonymous discussion, registered users discussion will automatically be enabled when you save the settings.';

$email_text = 'Notification Email Text';
$email_text_content = 'This is the body text of the email sent when wiki pages are edited.  HTML will be stripped out as the email will be sent as Plain Text.  You can use variables [page] and [url] to represent the name of the wiki page and the url to view the page, respectively.';

$ext_chars_support = 'Extended Character Set';
$ext_chars_support_content = 'When enabled, the extended character set will be supported for wiki page names.  For example, German umlauts would be allowed in a wiki page name.';

$ext_page_target = 'Target for External Links';
$ext_page_target_content = 'This controls where external pages will appear. "_blank" opens the new page in a new window. "_parent" is used in the situation where a frameset file is nested inside another frameset file.  A link in one of the inner frameset documents which uses "_parent" will load the new page where the inner frameset file had been.  If the current page\'s frameset file does not have any parent, then "_parent" works exactly like "_top"; the new document is loaded in the full window.  "_self" puts the new page in the same window and frame as the current page.';

$format_title = 'Format Wiki Page Title';
$format_title_content = 'Enabling this setting will format the current wiki page title before being displayed anywhere (excluding the wiki page text) by the module.  The page title in the page text will have to be formatted manually if you do not like the standard WordsSmashedTogether default. The automatic formatting by the module will add spaces to the WikiPageTitle, making it "Wiki Page Title". <br /><br />Remember, you will still have to refer to the page as WikiPageTitle in the page text, but you can change its appearance by using [WikiPageTitle Your Formatted Title Here].<br /><br />If this is confusing to you or others, it is recommended to not use this feature.';

$menu_items = 'Menu Items';
$menu_items_content = 'These settings allow you to change the look of the menu at the top of each wiki page.  It is important to note that these settings merely hide the links, but do not prevent a visitor from accessing the target page if they know the direct URL.';

$monitor_edits = 'Monitor Edits';
$monitor_edits_content = 'Enabling this setting will email a notification to the Wiki Administrator email address on every page edit.';

$orphaned_pages = 'Orphaned Pages';
$orphaned_pages_content = 'An orphaned page is a page no other page links to.';

$show_on_home = 'Show on Home Page';
$show_on_home_content = 'Enabling this setting will show the default wiki page on the home page of the web site.';

$show_modified_info = 'Show Page Modified Info';
$show_modified_info_content = 'Enabling this setting will show the "Last modified by" information on each wiki page. However, if {UPDATED_INFO} is not in the view template, the information will never show up, regardless of how this option is set.';

?>
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
 * @version     $Id: update.php,v 1.18 2006/03/05 04:34:34 blindman1344 Exp $
 */

if (!$_SESSION['OBJ_user']->isDeity()){
    header('location:index.php');
    exit();
}

if (version_compare($GLOBALS['core']->version, '0.10.0') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.10.0 or greater to install.<br />';
    $content .= 'You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

// Update Language
require_once(PHPWS_SOURCE_DIR . 'mod/language/class/Language.php');
PHPWS_Language::uninstallLanguages('wiki');
PHPWS_Language::installLanguages('wiki');

// Update Help
require_once(PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php');
CLS_help::uninstall_help('wiki');
CLS_help::setup_help('wiki');

if (version_compare($currentVersion, '0.2.0') < 0) {
    require_once PHPWS_SOURCE_DIR . 'core/File.php';
    // Check for permissions and create images directory if possible
    if (is_writable($GLOBALS['core']->home_dir . 'images/')) {
        if (!is_dir($GLOBALS['core']->home_dir . 'images/wiki')) {
            PHPWS_File::makeDir($GLOBALS['core']->home_dir . 'images/wiki');
            if (is_dir($GLOBALS['core']->home_dir . 'images/wiki')) {
                $content .= 'Wiki images directory successfully created in:<br />' . $GLOBALS['core']->home_dir .
                    'images/wiki<br /><br />';
            } else {
                $content .= 'Boost could not create the Wiki images directory in:<br />' . $GLOBALS['core']->home_dir .
                    'images/wiki<br />You will have to do this manually!<br /><br />';
            }
        }
    } else {
        $content .= 'Images directory is not writable.  Wiki images directory could not be created.<br /><br />';
    }

    $sql = 'ALTER TABLE mod_wiki_pages ADD allow_edit smallint NOT NULL default 1 AFTER version';
    $GLOBALS['core']->query($sql, TRUE);

    $sql = 'ALTER TABLE mod_wiki_settings ADD allow_page_edit smallint NOT NULL default 0 AFTER allow_anon_view,
            ADD allow_image_upload smallint NOT NULL default 0 AFTER allow_page_edit,
            ADD allow_bbcode smallint NOT NULL default 0 AFTER allow_image_upload,
            ADD ext_chars_support smallint NOT NULL default 0 AFTER allow_bbcode';
    $GLOBALS['core']->query($sql, TRUE);

    $sql = 'CREATE TABLE mod_wiki_images (
                id int NOT NULL default 0,
                owner varchar(20) default NULL,
                ip text,
                created int NOT NULL default 0,
                filename text NOT NULL,
                size int NOT NULL default 0,
                type varchar(255) NOT NULL,
                summary text NOT NULL,
                PRIMARY KEY  (id)
            )';
    $GLOBALS['core']->query($sql, TRUE);

    $content .= 'Wiki updates for version 0.2.0<br />';
    $content .= '----------------------------------<br />';
    $content .= '+ Image upload<br />';
    $content .= '+ Option to grant all registered users edit permissions<br />';
    $content .= '+ BBCode support<br />';
    $content .= '+ Extended characters support<br />';
    $content .= '+ Ability to lock pages<br />';

    $content .= '<br />Please upgrade your Text_Wiki library to version 1.0.0 or later if you have not already.<br />';
}

if (version_compare($currentVersion, '0.3.0') < 0) {
    // Reinstalling boxes in boost due to added toolbox
    PHPWS_Layout::uninstallBoxStyle('wiki');
    $_SESSION['OBJ_layout']->installModule('wiki');

    $content .= 'Wiki updates for version 0.3.0<br />';
    $content .= '----------------------------------<br />';
    $content .= '+ Version diffs<br />';
    $content .= '+ Wiki Toolbox added<br />';
    $content .= '+ "What links here" feature<br />';
    $content .= '+ Can now send notes to editors (Using Notes module)<br />';
    $content .= '+ Random page link added<br />';
    $content .= '+ Pages can now be moved<br />';

    $content .= '<br /><b>Please install the Text_Diff library (version 0.0.5 or later) if you have not already.</b><br />';
}

if (version_compare($currentVersion, '0.4.0') < 0) {
    $sql = 'CREATE TABLE mod_wiki_interwiki (
                id int NOT NULL default 0,
                owner varchar(20) default NULL,
                editor varchar(20) default NULL,
                ip text,
                label text NOT NULL,
                created int NOT NULL default 0,
                updated int NOT NULL default 0,
                url text NOT NULL,
                PRIMARY KEY  (id)
            )';
    $GLOBALS['core']->query($sql, TRUE);

    // Adding first interwiki link
    $interwiki = array();
    $interwiki['owner'] = 'install';
    $interwiki['editor'] = 'install';
    $interwiki['ip'] = '127.0.0.1';
    $interwiki['created'] = time();
    $interwiki['updated'] = time();
    $interwiki['label'] = 'Wikipedia';
    $interwiki['url'] = 'http://en.wikipedia.org/wiki/%s';
    $GLOBALS['core']->sqlInsert($interwiki, 'mod_wiki_interwiki');

    $sql = "ALTER TABLE mod_wiki_settings ADD add_to_title smallint NOT NULL default 1 AFTER ext_chars_support,
            ADD show_modified_info smallint NOT NULL default 1 AFTER add_to_title,
            ADD ext_page_target varchar(7) NOT NULL default '_blank' AFTER default_page";
    $GLOBALS['core']->query($sql, TRUE);

    $content .= 'Wiki updates for version 0.4.0<br />';
    $content .= '----------------------------------<br />';
    $content .= '+ Interwiki support<br />';
    $content .= '+ New external page target setting<br />';
    $content .= '+ Added ability to toggle adding wiki page title to site title<br />';
    $content .= '+ Added ability to toggle showing page modified info<br />';
}

if (version_compare($currentVersion, '0.5.0') < 0) {
    $sql = "ALTER TABLE mod_wiki_settings ADD format_title smallint NOT NULL default 0 AFTER add_to_title,
            ADD immutable_page smallint NOT NULL default 1 AFTER ext_page_target,
            ADD raw_text smallint NOT NULL default 0 AFTER immutable_page,
            ADD print_view smallint NOT NULL default 1 AFTER raw_text,
            ADD discussion smallint NOT NULL default 1 AFTER print_view,
            ADD discussion_anon smallint NOT NULL default 0 AFTER discussion";
    $GLOBALS['core']->query($sql, TRUE);

    $content .= 'Wiki updates for version 0.5.0<br />';
    $content .= '----------------------------------<br />';
    $content .= '+ Added option to format the wiki page title<br />';
    $content .= '+ Certain page menu items can now be hidden<br />';
    $content .= '+ Discussion feature added (Requires comments module)<br />';

    $content .= '<br /><em>The templates have changed with this release.  If your site theme overrides
                 the wiki module templates, you may have to modify your theme templates in order for this
                 version to work as expected.</em><br />';
}

if (version_compare($currentVersion, '0.6.0') < 0) {
    $content .= 'Wiki updates for version 0.6.0<br />';
    $content .= '----------------------------------<br />';
    $content .= '+ Added support for additional wiki parsers<br />';
    $content .= '+ Template support added<br />';
}

$status = 1;

?>
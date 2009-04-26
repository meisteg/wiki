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
 * $Id: install.php,v 1.14 2005/07/25 00:52:16 blindman1344 Exp $
 */

/* Make sure the user is a deity before running this script */
if (!$_SESSION['OBJ_user']->isDeity()){
    header('location:index.php');
    exit();
}

$status = 0;

if (version_compare($GLOBALS['core']->version, '0.10.0') < 0) {
    $content .= 'This module requires a phpWebSite core version of 0.10.0 or greater to install.<br />';
    $content .= 'You are currently using phpWebSite core version ' . $GLOBALS['core']->version . '.<br />';
    return;
}

@include_once('Text/Wiki.php');
@include_once('Text/Diff.php');
if (!class_exists('Text_Wiki') || !class_exists('Text_Diff')) {
    $content .= 'This module requires an additional PEAR libraries to install.<br />';
    $content .= 'See docs/INSTALL in the Wiki module directory for more information.<br />';
    return;
}

/* Import installation database and dump result into status variable */
if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/wiki/boost/install.sql', TRUE)) {
    $data = array();
    $data['owner'] = 'install';
    $data['editor'] = 'install';
    $data['ip'] = '127.0.0.1';
    $data['created'] = time();
    $data['updated'] = time();
    $data['hits'] = 0;
    $data['version'] = 0;

    $vdata = array();
    $vdata['editor'] = 'install';
    $vdata['updated'] = time();
    $vdata['version'] = 0;
    $vdata['comment'] = 'Provided by Wiki install';

    // Adding pages that ship with the module

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/frontpage.txt')) {
        $data['label'] = 'FrontPage';
        $data['pagetext'] = implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/frontpage.txt'));
        $data['allow_edit'] = 1;
        $GLOBALS['core']->sqlInsert($data, 'mod_wiki_pages');
        $vdata['page'] = $data['label'];
        $vdata['pagetext'] = $data['pagetext'];
        $GLOBALS['core']->sqlInsert($vdata, 'mod_wiki_versions');
    }

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/samplepage.txt')) {
        $data['label'] = 'SamplePage';
        $data['pagetext'] = implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/samplepage.txt'));
        $data['allow_edit'] = 0;
        $GLOBALS['core']->sqlInsert($data, 'mod_wiki_pages');
        $vdata['page'] = $data['label'];
        $vdata['pagetext'] = $data['pagetext'];
        $GLOBALS['core']->sqlInsert($vdata, 'mod_wiki_versions');
    }

    if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/boost/sandbox.txt')) {
        $data['label'] = 'WikiSandBox';
        $data['pagetext'] = implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/boost/sandbox.txt'));
        $data['allow_edit'] = 1;
        $GLOBALS['core']->sqlInsert($data, 'mod_wiki_pages');
        $vdata['page'] = $data['label'];
        $vdata['pagetext'] = $data['pagetext'];
        $GLOBALS['core']->sqlInsert($vdata, 'mod_wiki_versions');
    }

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

    $content .= 'All wiki tables successfully written.<br /><br />';

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
} else {
    $content .= 'There was a problem writing to the database!<br /><br />';
}

?>
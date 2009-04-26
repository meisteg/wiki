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
 * @version     $Id: runtime.php,v 1.16 2006/02/12 05:56:25 blindman1344 Exp $
 */

// Display on the home page if option is set
if ($GLOBALS['module'] == 'home') {
    require_once(PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiManager.php');

    $GLOBALS['CNT_wiki'] = array('title' => NULL, 'content' => NULL);
    PHPWS_WikiManager::getSettings();

    if ($_SESSION['PHPWS_WikiSettings']['show_on_home']) {
        PHPWS_WikiManager::action();
    }
}
// End of display on home page

// Import the stylesheet, if one exists
if (file_exists($_SESSION['OBJ_layout']->theme_dir . 'templates/wiki/style.css')) {
    $_SESSION['OBJ_layout']->addImport('@import url("themes/' . $_SESSION['OBJ_layout']->current_theme . '/templates/wiki/style.css");');
}
else if (file_exists(PHPWS_SOURCE_DIR . 'mod/wiki/templates/style.css')) {
    if ($GLOBALS['core']->isHub) {
        $_SESSION['OBJ_layout']->addImport('@import url("mod/wiki/templates/style.css");');
    }
    else {
        $_SESSION['OBJ_layout']->addStyle(implode('', file(PHPWS_SOURCE_DIR . 'mod/wiki/templates/style.css')));
    }
}
// End of stylesheet import

?>
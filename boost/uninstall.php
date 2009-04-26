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
 * $Id: uninstall.php,v 1.5 2006/02/12 05:56:24 blindman1344 Exp $
 */

/* Make sure the user is a deity before running this script */
if (!$_SESSION['OBJ_user']->isDeity()){
    header('location:index.php');
    exit();
}

/* Import the uninstall database file and dump the result into the status variable */
if ($status = $GLOBALS['core']->sqlImport(PHPWS_SOURCE_DIR . 'mod/wiki/boost/uninstall.sql', 1, 1)) {
    $content .= 'All wiki tables successfully removed!<br /><br />';

    require_once PHPWS_SOURCE_DIR . 'core/File.php';
    /* Check for images directory and remove if it exists */
    if (is_dir($GLOBALS['core']->home_dir . 'images/wiki')) {
        $content .= 'Removing Wiki images directory at:<br />' . $GLOBALS['core']->home_dir . 'images/wiki<br /><br />';
        PHPWS_File::rmdir($GLOBALS['core']->home_dir . 'images/wiki/');
    } else {
        $content .= 'No images directory found for removal.<br /><br />';
    }

    $_SESSION['PHPWS_WikiSettings'] = NULL;
} else {
    $content .= 'There was a problem accessing the database.<br /><br />';
}

?>
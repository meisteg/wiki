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
 * $Id: index.php,v 1.3 2006/02/12 05:56:20 blindman1344 Exp $
 */

/* Make sure core is set before executing otherwise it means someone is trying
   to access the module directory directly */
if (!isset($GLOBALS['core'])){
    header('location:../../');
    exit();
}

$GLOBALS['CNT_wiki'] = array('title' => NULL, 'content' => NULL);

PHPWS_WikiManager::getSettings();
PHPWS_WikiManager::action();

?>
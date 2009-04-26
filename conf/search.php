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
 * $Id: search.php,v 1.2 2005/01/18 17:50:42 blindman1344 Exp $
 */

$module          = 'wiki';
$search_class    = 'PHPWS_WikiManager';
$search_function = 'search';
$search_cols     = 'label, pagetext'; 
$view_string     = '&amp;page=';
$show_block      = 1;
$block_title     = 'Wiki'; 
$class_file      = 'WikiManager.php';

?>
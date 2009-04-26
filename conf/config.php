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
 * $Id: config.php,v 1.2 2006/05/06 15:02:46 blindman1344 Exp $
 */

/*****************************************************************************
 * WIKI PARSER SELECTION                                                     *
 *                                                                           *
 * Select which parser you want this module to use from the list below. Only *
 * one parser can be selected.  Keep in mind that changing this setting will *
 * cause the default pages (FrontPage, SamplePage, WikiSandBox) and any      *
 * pages already created to render incorrectly.  You will have to update     *
 * these pages to the correct wiki syntax.  Links have been provided below   *
 * to pages showing correct syntax for that parser.                          *
 *****************************************************************************/

/*
 * Text_Wiki  (Highly Recommended)
 * http://pear.php.net/package/Text_Wiki/
 *
 * Version required: Included with phpWebSite
 * Syntax: SamplePage (provided by this module)
 */
$parser = 'Text_Wiki';

/*
 * Text_Wiki_Cowiki
 * http://pear.php.net/package/Text_Wiki_Cowiki/
 *
 * Version required: Greater than 0.0.1
 * Syntax: http://cowiki.org/131.html
 */
// $parser = 'Cowiki';

/*
 * Text_Wiki_Doku
 * http://pear.php.net/package/Text_Wiki_Doku/
 *
 * Version required: Greater than 0.0.1
 * Syntax: http://wiki.splitbrain.org/wiki:syntax
 */
// $parser = 'Doku';

/*
 * Text_Wiki_Mediawiki
 * http://pear.php.net/package/Text_Wiki_Mediawiki/
 *
 * Version required: 0.1.0 or later
 * Syntax: http://meta.wikimedia.org/wiki/Help:Editing#The_wiki_markup
 */
// $parser = 'Mediawiki';

/*
 * Text_Wiki_Tiki
 * http://pear.php.net/package/Text_Wiki_Tiki/
 *
 * Version required: Greater than 0.0.1
 * Syntax: http://tikiwiki.org/tiki-index.php?page=WikiSyntax
 */
// $parser = 'Tiki';

?>
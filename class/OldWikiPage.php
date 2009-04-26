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
 * $Id: OldWikiPage.php,v 1.17 2006/02/12 05:56:25 blindman1344 Exp $
 */

class PHPWS_OldWikiPage {

    /**
     * The id of this version in the database table
     *
     * @var int
     */
    var $_id;

    /**
     * The page title
     *
     * @var string
     */
    var $_page;

    /**
     * The version of this old wiki page
     *
     * @var int
     */
    var $_version;

    /**
     * Who created this version
     *
     * @var string
     */
    var $_editor;

    /**
     * When this version was created
     *
     * @var int
     */
    var $_updated;

    /**
     * The text of this page
     *
     * @var string
     */
    var $_pagetext;

    /**
     * The comment describing this edit
     *
     * @var string
     */
    var $_comment;


    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function PHPWS_OldWikiPage($vars) {
        if(is_array($vars)) {
            foreach($vars as $key => $value) {
                $key = "_{$key}";
                $this->{$key} = $value;
            }
        } else {
            $result = $GLOBALS['core']->sqlSelect('mod_wiki_versions', 'id', $vars);
            if (sizeof($result) > 0) {
                $this->_id = $result[0]['id'];
                $this->_page = $result[0]['page'];
                $this->_version = $result[0]['version'];
                $this->_editor = $result[0]['editor'];
                $this->_updated = $result[0]['updated'];
                $this->_pagetext = $result[0]['pagetext'];
                $this->_comment = $result[0]['comment'];
            }
        }
    }

    /**
     * Menu
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _menu() {
        $restore = $_SESSION['translate']->it('Restore');
        $history = $_SESSION['translate']->it('History');
        $page = $_SESSION['translate']->it('Back to Page');

        $links = NULL;
        if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') || ($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) {
            $currentpage = new PHPWS_WikiPage($this->_page);

            if (($currentpage->getVersion() != $this->_version) && ($currentpage->_allow_edit)) {
                $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=restore&amp;id=' .
                          $this->_id . '">' . $restore . '</a>'), 'wiki', 'menu_item.tpl');
            }
        }

        $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=history&amp;page=' .
                  $this->_page . '">' . $history . '</a>'), 'wiki', 'menu_item.tpl');
        $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page=' .
                  $this->_page.'">' . $page . '</a>'), 'wiki', 'menu_item.tpl');

        return $links;
    }

    /**
     * Get pagetext
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getPagetext($transform=TRUE) {
        if ($transform) {
            return PHPWS_WikiManager::transform($this->_pagetext);
        }
        else {
            return $this->_pagetext;
        }
    }

    /**
     * Get Page Title
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListPage() {
        return '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '">' .
                PHPWS_WikiManager::formatTitle($this->_page) . '</a>';
    }

    /**
     * Get comment
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListComment() {
        require_once PHPWS_SOURCE_DIR . 'core/Text.php';
        return PHPWS_Text::parseOutput($this->_comment);
    }

    /**
     * Get version for a list
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListVersion() {
        return $this->_version;
    }

    /**
     * Get editor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListEditor() {
        if ($_SESSION['OBJ_user']->username && ($_SESSION['OBJ_user']->username != $this->_editor)) {
            if ($GLOBALS['core']->moduleExists('notes') && ($this->_editor != 'install')) {
                require_once PHPWS_SOURCE_DIR . 'mod/boost/class/Boost.php';
                $notesinfo = PHPWS_Boost::getVersionInfo('notes');
                if (version_compare($notesinfo['version'], '1.6.0') >= 0) {
                    return '<a href="index.php?module=notes&amp;NOTE_op=new_note&amp;NOTE_toUser=' . $this->_editor . '" title="' . $_SESSION['translate']->it('Send note') . '">' . $this->_editor . '</a>';
                }
            }
        }
        return $this->_editor;
    }

    /**
     * Get updated
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListUpdated() {
        return date(PHPWS_DATE_FORMAT . " " . PHPWS_TIME_FORMAT, $this->_updated);
    }

    /**
     * Get diff options
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListDiff() {
        $links = array();

        $result = $GLOBALS['core']->sqlSelect('mod_wiki_versions', 'page', $this->_page, 'version desc');
        if ($result[0]['version'] != $this->_version) {
            $links[] = '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '&amp;page_op=compare&amp;oid=' . $this->_id . '&amp;nid=' . $result[0]['id'] . '">' . $_SESSION['translate']->it('Current') . '</a>';
        }

        if ($this->_version != 0) {
            $result = $GLOBALS['core']->sqlSelect('mod_wiki_versions', array('page'=>$this->_page, 'version'=>($this->_version-1)));
            $links[] = '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '&amp;page_op=compare&amp;oid=' . $result[0]['id'] . '&amp;nid=' . $this->_id . '">' . $_SESSION['translate']->it('Previous') . '</a>';
        }
        return implode(' | ', $links);
    }

    /**
     * Get actions
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListActions() {
        $links = array();
        $links[] = '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '&amp;page_op=viewold&amp;id='.$this->_id.'">'.$_SESSION['translate']->it('View').'</a>';

        $result = $GLOBALS['core']->sqlSelect('mod_wiki_pages', 'label', $this->_page);
        if (($result[0]['version'] != $this->_version) && ($result[0]['allow_edit'])) {
            if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') || ($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) {
                $links[] = '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '&amp;page_op=restore&amp;id=' . $this->_id . '">' . $_SESSION['translate']->it('Restore') . '</a>';
            }
        }

        return implode(' | ', $links);
    }

    /**
     * Get List View
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListView() {
        $links = array();
        if ($this->_version != 0) {
            $result = $GLOBALS['core']->sqlSelect('mod_wiki_versions', array('page'=>$this->_page, 'version'=>($this->_version-1)));
            $links[] = '<a href="./index.php?module=wiki&amp;page=' . $this->_page . '&amp;page_op=compare&amp;oid=' . $result[0]['id'] . '&amp;nid=' . $this->_id . '">' . $_SESSION['translate']->it('Diff') . '</a>';
        }
        $links[] = '<a href="./index.php?module=wiki&amp;page_op=history&amp;page=' . $this->_page . '">' . $_SESSION['translate']->it('History') . '</a>';
        return implode(' | ', $links);
    }

    /**
     * View
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _view() {
        $tags = array();

        if (isset($_SESSION['WIKI_message'])) {
            $tags['MESSAGE'] = $_SESSION['WIKI_message'];
            $_SESSION['WIKI_message'] = NULL;
        }

        $tags['MENU'] = $this->_menu();
        $tags['PAGETEXT'] = $this->getPagetext();
        if ($_SESSION['PHPWS_WikiSettings']['show_modified_info']) {
            $tags['UPDATED_INFO'] = $_SESSION['translate']->it('Last modified [var1] by [var2]', $this->getListUpdated(), $this->getListEditor());
        }

        if ($_SESSION['PHPWS_WikiSettings']['add_to_title']) {
            PHPWS_Layout::addPageTitle(PHPWS_WikiManager::formatTitle($this->_page));
        }

        return PHPWS_Template::processTemplate($tags, 'wiki', 'view.tpl');
    }

    /**
     * Restore this version to the current version
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _restore() {
        $currentpage = new PHPWS_WikiPage($this->_page);

        if ((!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) || !$currentpage->_allow_edit) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_OldWikiPage::_restore()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        // Update current version
        $currentpage->setPagetext($this->_pagetext);
        $currentpage->_incVersion();
        $currentpage->commit();

        // Store version in versions table
        $data = array();
        $data['version'] = $currentpage->getVersion();
        $data['editor'] = $currentpage->getEditor();
        $data['updated'] = time();
        $data['pagetext'] = $this->_pagetext;
        $data['page'] = $currentpage->getLabel();
        $data['comment'] = '[' . $_SESSION['translate']->it('Restored from version') . ' ' . $this->_version . ']';
        $GLOBALS['core']->sqlInsert($data, 'mod_wiki_versions');

        $_SESSION['WIKI_message'] = $_SESSION['translate']->it('Page Restored');
        $_REQUEST['page_op'] = 'view';
        $_REQUEST['page'] = $currentpage->getLabel();
        PHPWS_WikiManager::action();
    }

    /**
     * Action
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action() {
        $title = NULL;
        $content = NULL;

        switch($_REQUEST['oldpage_op']) {
            case 'restore':
                $this->_restore();
                break;

            default:
                $title .= PHPWS_WikiManager::formatTitle($this->_page);
                $content .= $this->_view();
        }

        if (isset($title)) {
            $GLOBALS['CNT_wiki']['title'] .= $title;
        }
        if (isset($content)) {
            $GLOBALS['CNT_wiki']['content'] .= $content;
        }
    }
}

?>
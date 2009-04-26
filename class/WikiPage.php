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
 * $Id: WikiPage.php,v 1.52 2006/03/06 00:29:52 blindman1344 Exp $
 */

require_once PHPWS_SOURCE_DIR . 'core/Item.php';

class PHPWS_WikiPage extends PHPWS_Item {

    /**
     * Stores the main content for this item
     *
     * @var string
     */
    var $_pagetext = null;

    /**
     * Counts the number of times this page has been viewed
     *
     * @var int
     */
    var $_hits = 0;

    /**
     * Counts the number of times this page has been edited
     *
     * @var int
     */
    var $_version = -1;

    /**
     * Flag 1/0 to lock page from edits
     *
     * @var smallint
     */
    var $_allow_edit = 1;


    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function PHPWS_WikiPage($page = null) {
        $this->setTable('mod_wiki_pages');
        $this->addExclude(array('_hidden', '_approved'));

        if(isset($page)) {
            $sql = "SELECT id FROM mod_wiki_pages WHERE label='" . $page . "'";
            $result = $GLOBALS['core']->getCol($sql, TRUE);
            if (sizeof($result) > 0) {
                $this->setId($result[0]);
                $this->init();
            }
            else {
                $this->setLabel($page);
            }
        }
    }

    /**
     * Menu
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _menu() {
        $id = $this->getId();
        if (!isset($id)) {
            return NULL;
        }

        $edit = $_SESSION['translate']->it('Edit');
        $move = $_SESSION['translate']->it('Move');
        $immutable = $_SESSION['translate']->it('Immutable Page');
        $delete = $_SESSION['translate']->it('Delete');
        $locked = $_SESSION['translate']->it('Locked');
        $unlocked = $_SESSION['translate']->it('Unlocked');
        $discussion = $_SESSION['translate']->it('Discussion');
        $rawtext = $_SESSION['translate']->it('Raw Text');
        $print = $_SESSION['translate']->it('Print View');
        $history = $_SESSION['translate']->it('History');

        $links = NULL;
        if (($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') || ($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) && $this->_allow_edit) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=edit&amp;page=' .
                      $this->getLabel() . '">' . $edit . '</a>'), 'wiki', 'menu_item.tpl');
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=move&amp;page=' .
                      $this->getLabel() . '">' . $move . '</a>'), 'wiki', 'menu_item.tpl');
        }
        else if ($_SESSION['PHPWS_WikiSettings']['immutable_page']) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>$immutable), 'wiki', 'menu_item.tpl');
        }

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'delete_page')) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=delete&amp;page=' .
                      $this->getLabel() . '">' . $delete . '</a>'), 'wiki', 'menu_item.tpl');
        }

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'toggle_lock')) {
            if ($this->_allow_edit) {
                $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=togglelock&amp;page=' .
                          $this->getLabel() . '">' . $unlocked . '</a>'), 'wiki', 'menu_item.tpl');
            }
            else {
                $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=togglelock&amp;page=' .
                          $this->getLabel() . '">' . $locked . '</a>'), 'wiki', 'menu_item.tpl');
            }
        }

        if ($GLOBALS['core']->moduleExists('comments') && $_SESSION['PHPWS_WikiSettings']['discussion']) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=discussion&amp;page=' .
                      $this->getLabel() . '">' . $discussion . '</a>'), 'wiki', 'menu_item.tpl');
        }

        if ($_SESSION['PHPWS_WikiSettings']['raw_text']) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=raw&amp;page=' .
                      $this->getLabel() . '&amp;lay_quiet=1">' . $rawtext . '</a>'), 'wiki', 'menu_item.tpl');
        }
        if ($_SESSION['PHPWS_WikiSettings']['print_view']) {
            $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=print&amp;page=' .
                      $this->getLabel() . '&amp;lay_quiet=1" onclick="window.open(this.href, \'_blank\'); return false;">' .
                      $print . '</a>'), 'wiki', 'menu_item.tpl');
        }
        $links .= PHPWS_Template::processTemplate(array('LINK'=>'<a href="./index.php?module=wiki&amp;page_op=history&amp;page=' .
                  $this->getLabel() . '">' . $history . '</a>'), 'wiki', 'menu_item.tpl');

        return $links;
    }

    /**
     * Set Pagetext
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function setPagetext($pagetext) {
        require_once PHPWS_SOURCE_DIR . 'core/Text.php';

        if (is_string($pagetext)) {
            if (strlen($pagetext) > 0) {
                $this->_pagetext = PHPWS_Text::parseInput($pagetext);
                return true;
            } else {
                $message = $_SESSION['translate']->it('You must provide some page text!');
                return new PHPWS_Error('wiki', 'PHPWS_WikiPage::save', $message);
            }
        } else {
            $message = $_SESSION['translate']->it('Page text must be a string!');
            return new PHPWS_Error('wiki', 'PHPWS_WikiPage::save', $message);
        }
    }

    /**
     * Get pagetext
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getPagetext() {
        return PHPWS_WikiManager::transform($this->_pagetext);
    }

    /**
     * Get hits
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getHits() {
        return number_format($this->_hits);
    }

    /**
     * Get version
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getVersion() {
        return $this->_version;
    }

    /**
     * View
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _view() {
        $tags = array();

        $id = $this->getId();
        if(isset($id)) {
            $tags['PAGETEXT'] = $this->getPagetext();

            if ($_SESSION['PHPWS_WikiSettings']['show_modified_info']) {
                $editor = $this->getEditor();
                if ($_SESSION['OBJ_user']->username && ($_SESSION['OBJ_user']->username != $editor)) {
                    if ($GLOBALS['core']->moduleExists('notes') && ($editor != 'install')) {
                        require_once PHPWS_SOURCE_DIR . 'mod/boost/class/Boost.php';
                        $notesinfo = PHPWS_Boost::getVersionInfo('notes');
                        if (version_compare($notesinfo['version'], '1.6.0') >= 0) {
                            $editor = '<a href="index.php?module=notes&amp;NOTE_op=new_note&amp;NOTE_toUser=' . $editor . '" title="' . $_SESSION['translate']->it('Send note') . '">' . $editor . '</a>';
                        }
                    }
                }
                $tags['UPDATED_INFO'] = $_SESSION['translate']->it('Last modified [var1] by [var2]', $this->getUpdated(), $editor);
            }

            if (isset($_REQUEST['module']) && $_REQUEST['module']=='wiki' && $_SESSION['PHPWS_WikiSettings']['add_to_title']) {
                PHPWS_Layout::addPageTitle(PHPWS_WikiManager::formatTitle($this->getLabel()));
            }

            // Display Whats Related only if in viewing mode
            if (isset($_REQUEST['page_op']) && ($_REQUEST['page_op'] == 'view')) {
                $_SESSION['OBJ_fatcat']->whatsRelated($id);
            }
        }
        else if(isset($this->_pagetext)) {
            $tags['PAGETEXT'] = $this->getPagetext();
        }
        else {
            $tags['PAGETEXT'] = $_SESSION['translate']->it('This page does not exist yet.');
            if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') || ($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) {
                $tags['PAGETEXT'] .= ' <a href="./index.php?module=wiki&amp;page_op=edit&amp;page=' . $this->getLabel() . '">';
                $tags['PAGETEXT'] .= $_SESSION['translate']->it('Create new empty page') . '</a>';
            }
        }

        // For print view only
        if (isset($_REQUEST['page_op']) && ($_REQUEST['page_op'] == 'print')) {
            $tags['PAGENAME'] = PHPWS_WikiManager::formatTitle($this->getLabel());
        }

        // Display the menu and message if in view mode
        if (isset($_REQUEST['page_op']) && (($_REQUEST['page_op'] == 'view') || ($_REQUEST['page_op'] == 'togglelock'))) {
            $tags['MENU'] = $this->_menu();

            if (isset($_SESSION['WIKI_message'])) {
                $tags['MESSAGE'] = $_SESSION['WIKI_message'];
                $_SESSION['WIKI_message'] = NULL;
            }
        }

        return PHPWS_Template::processTemplate($tags, 'wiki', 'view.tpl');
    }

    /**
     * Edit
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _edit() {
        if ((!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) || !$this->_allow_edit) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_edit()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';

        $id = $this->getId();

        $form = new EZForm('wiki_edit');
        $form->add('module', 'hidden', 'wiki');
        $form->add('page_op', 'hidden', 'save');

        if (isset($id)) {
            $form->add('page_id', 'hidden', $id);
        }
        else {
            $label = $this->getLabel();
            $form->add('page_label', 'hidden', $label);
        }

        $form->add('pagetext', 'textarea', $this->_pagetext);
        $form->setCols('pagetext', 70);
        $form->setRows('pagetext', 25);

        $form->add('comment', 'text');
        $form->setSize('comment', 50);

        // Needed for preview case
        if (isset($_POST['comment'])) {
            $form->setValue('comment', stripslashes($_POST['comment']));
        }

        $form->add('save', 'submit', $_SESSION['translate']->it('Save'));
        $form->add('preview', 'submit', $_SESSION['translate']->it('Preview'));
        $form->add('cancel', 'submit', $_SESSION['translate']->it('Cancel'));

        $tags = $form->getTemplate();
        $tags['PAGETEXT_LABEL'] = $_SESSION['translate']->it('Page Text');
        $tags['COMMENT_LABEL'] = $_SESSION['translate']->it('Optional comment about this edit');
        $tags['FATCAT_LABEL'] = $_SESSION['translate']->it('Category');
        $tags['FATCAT'] = $_SESSION['OBJ_fatcat']->showSelect($id);

        // Display box to add page link to menu - only if page is not new
        if (isset($id) && $GLOBALS['core']->moduleExists('menuman') && $_SESSION['OBJ_user']->allow_access('menuman', 'add_item')) {
            $_SESSION['OBJ_menuman']->add_module_item('wiki', '&amp;page=' . $this->getLabel(), './index.php?module=wiki&page_op=edit&page=' . $this->getLabel());
        }

        $GLOBALS['CNT_wiki']['title'] .= $_SESSION['translate']->it('Edit') . ' ' . PHPWS_WikiManager::formatTitle($this->getLabel());
        return PHPWS_Template::processTemplate($tags, 'wiki', 'edit.tpl');
    }

    /**
     * Save
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _save() {
        require_once PHPWS_SOURCE_DIR . 'core/Error.php';

        if ((!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) || !$this->_allow_edit) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_save()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        // This should prevent overwriting of pages with multiple windows/tabs
        if (isset($_POST['page_id'])) {
            $this->setId($_POST['page_id']);
            $this->init();
        }
        else {
            $this->_id = NULL;
            $this->_version = -1;
            $this->_hits = 0;
            $this->setLabel($_POST['page_label']);
        }

        if(isset($_POST['cancel'])) {
            $_SESSION['WIKI_message'] = $_SESSION['translate']->it('Edit Cancelled!');

            $_REQUEST['page'] = $this->getLabel();
            $_REQUEST['page_op'] = 'view';

            PHPWS_WikiManager::action();
            return FALSE;
        }

        $error = $this->setPagetext($_POST['pagetext']);
        if (PHPWS_Error::isError($error)) {
            $error->message('CNT_wiki');
            return $this->_edit();
        }

        if(isset($_POST['preview'])) {
            return $this->_edit() . $this->_view();
        }

        $this->_incVersion();
        $error = $this->commit();
        $_SESSION['OBJ_fatcat']->saveSelect($this->getLabel(),'index.php?module=wiki&amp;page=' . $this->getLabel(), $this->getId());

        require_once PHPWS_SOURCE_DIR . 'core/Text.php';

        $vdata = array();
        $vdata['editor'] = $this->getEditor();
        $vdata['updated'] = time();
        $vdata['version'] = $this->getVersion();
        if(isset($_POST['comment'])) $vdata['comment'] = PHPWS_Text::parseInput($_POST['comment']);
        $vdata['page'] = $this->getLabel();
        $vdata['pagetext'] = $this->_pagetext;
        $GLOBALS['core']->sqlInsert($vdata, 'mod_wiki_versions');

        $_SESSION['WIKI_message'] = $_SESSION['translate']->it('Wiki Page Saved!');

        $_REQUEST['page'] = $this->getLabel();
        $_REQUEST['page_op'] = 'view';

        PHPWS_WikiManager::sendEmail();
        PHPWS_WikiManager::action();
    }

    /**
     * Delete
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _delete() {
        if (!$_SESSION['OBJ_user']->allow_access('wiki', 'delete_page')) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['yes'])) {
            $_SESSION['WIKI_message'] = $_SESSION['translate']->it('[var1] deleted!', PHPWS_WikiManager::formatTitle($this->getLabel()));
            $GLOBALS['core']->sqlDelete('mod_wiki_versions', 'page', $this->getLabel());
            PHPWS_Fatcat::purge($this->getId(), 'wiki');
            $this->kill();

            $_REQUEST['page'] = NULL;
            $_REQUEST['page_op'] = NULL;

            PHPWS_WikiManager::action();
        } else if (isset($_REQUEST['no'])) {
            $_SESSION['WIKI_message'] = $_SESSION['translate']->it('[var1] was not deleted!', PHPWS_WikiManager::formatTitle($this->getLabel()));

            $_REQUEST['page_op'] = 'view';

            PHPWS_WikiManager::action();
        } else {
            $tags = array();

            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this wiki page?');

            $tags['YES'] = '<a href="index.php?module=wiki&amp;page_op=delete&amp;yes=1&amp;page=' . $this->getLabel() . '">' .
            $_SESSION['translate']->it('Yes') . '</a>';

            $tags['NO'] = '<a href="index.php?module=wiki&amp;page_op=delete&amp;no=1&amp;page=' . $this->getLabel() . '">' .
            $_SESSION['translate']->it('No') .'</a>';

            $tags['WIKIPAGE'] = $this->_view();

            $GLOBALS['CNT_wiki']['title'] .= $_SESSION['translate']->it('Delete') . ' ' . PHPWS_WikiManager::formatTitle($this->getLabel());
            return PHPWS_Template::processTemplate($tags, 'wiki', 'confirm.tpl');
        }
    }

    /**
     * Increment Hits Counter
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _incHits() {
        $id = $this->getId();
        if($id != NULL) {
            $this->_hits += 1;
            return $GLOBALS['core']->sqlUpdate(array('hits'=>$this->_hits), 'mod_wiki_pages', 'id', $id);
        }
        else {
            return NULL;
        }
    }

    /**
     * Increment Version
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _incVersion() {
        $this->_version += 1;
    }

    /**
     * Display the history of this page
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _history() {
        require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/OldWikiPage.php';
        require_once PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php';
        require_once PHPWS_SOURCE_DIR . 'core/List.php';

        if ($_SESSION['PHPWS_WikiSettings']['add_to_title']) {
            PHPWS_Layout::addPageTitle(PHPWS_WikiManager::formatTitle($this->getLabel()));
        }

        $list =& new PHPWS_List;
        $listSettings = array('limit'   => 20,
                              'section' => true,
                              'limits'  => array(10,20,50),
                              'back'    => '&#60;&#60;',
                              'forward' => '&#62;&#62;',
                              'anchor'  => false);

        $list->setModule('wiki');
        $list->setClass('PHPWS_OldWikiPage');
        $list->setTable('mod_wiki_versions');
        $list->setDbColumns(array('id', 'page', 'version', 'editor', 'updated', 'comment'));
        $list->setListColumns(array('version', 'editor', 'updated', 'comment', 'diff', 'actions'));
        $list->setName('history');
        $list->setOp('page_op=history&amp;page='.$this->getLabel());
        $list->anchorOn();
        $list->setPaging($listSettings);
        $list->setOrder('version desc');
        $list->setWhere("page='".$this->getLabel()."'");
        $list->setExtraListTags(array('TITLE' => $_SESSION['translate']->it('Revision History'),
                                      'UPDATED_LABEL' => $_SESSION['translate']->it('Updated'),
                                      'EDITOR_LABEL' => $_SESSION['translate']->it('Editor'),
                                      'VERSION_LABEL' => $_SESSION['translate']->it('Version'),
                                      'COMMENT_LABEL' => $_SESSION['translate']->it('Comment'),
                                      'DIFF_LABEL' => $_SESSION['translate']->it('Compare To'),
                                      'DIFF_HELP' => CLS_help::show_link('wiki', 'diff'),
                                      'ACTIONS_LABEL' => $_SESSION['translate']->it('Actions')));

        $backLink = '<a href="./index.php?module=wiki&amp;page='.$this->getLabel().'">'.$_SESSION['translate']->it('Back to Page').'</a>';
        return $backLink . $list->getList();
    }

    /**
     * Toggles whether page is locked or not
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _toggleLock() {
        if (!$_SESSION['OBJ_user']->allow_access('wiki', 'toggle_lock')) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_toggleLock()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if ($this->_allow_edit) {
            $this->_allow_edit = 0;
        }
        else {
            $this->_allow_edit = 1;
        }

        $this->commit();
    }

    /**
     * Displays what pages link to this page
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _whatLinksHere() {
        $tags = array();
        $tags['BACK_PAGE'] = '<a href="./index.php?module=wiki&amp;page=' . $this->getLabel() . '">' . $_SESSION['translate']->it('Back to Page') . '</a>';
        $tags['TITLE'] = $_SESSION['translate']->it('The following pages link to here');
        $tags['LINKS'] = NULL;

        $sql = "SELECT label FROM mod_wiki_pages WHERE (pagetext LIKE '%" . $this->getLabel() . "%') AND (label != '" . $this->getLabel() . "') ORDER BY label";
        $result = $GLOBALS['core']->getAll($sql, TRUE);

        if ($result != NULL) {
            foreach ($result as $row) {
                $link = '<a href="./index.php?module=wiki&amp;page=' . $row['label'] . '">' . PHPWS_WikiManager::formatTitle($row['label']) . '</a>';
                $tags['LINKS'] .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'whatlinkshere/link.tpl');
            }
        }
        else {
            $tags['MESSAGE'] = $_SESSION['translate']->it('None');
        }

        return PHPWS_Template::processTemplate($tags, 'wiki', 'whatlinkshere/page.tpl');
    }

    /**
     * Displays the move form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _move() {
        if ((!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) || !$this->_allow_edit) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_move()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';

        $GLOBALS['CNT_wiki']['title'] .= $_SESSION['translate']->it('Move') . ' ' . PHPWS_WikiManager::formatTitle($this->getLabel());

        $form = new EZForm('wiki_move');
        $form->add('module', 'hidden', 'wiki');
        $form->add('page_op', 'hidden', 'do_move');
        $form->add('page', 'hidden', $this->getLabel());
        $form->add('newpage', 'text');
        $form->setSize('newpage', 40);
        $form->add('move', 'submit', $_SESSION['translate']->it('Move'));

        $tags = $form->getTemplate();
        $tags['BACK_PAGE'] = '<a href="./index.php?module=wiki&amp;page=' . $_REQUEST['page'] . '">' . $_SESSION['translate']->it('Back to Page') . '</a>';
        $tags['MESSAGE'] = $_SESSION['translate']->it('Using the form below will rename a page, moving all of its history to the new name. The old title will become a redirect page to the new title. Links to the old page title will not be changed. You are responsible for making sure that links continue to point where they are supposed to go. Note that the page will not be moved if there is already a page at the new title.');
        $tags['NEWPAGE_LABEL'] = $_SESSION['translate']->it('New title');

        return PHPWS_Template::processTemplate($tags, 'wiki', 'move.tpl');
    }

    /**
     * Performs the wiki page move
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _doMove() {
        if ((!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) || !$this->_allow_edit) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_doMove()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if(strlen($_POST['newpage']) == 0) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Please supply a new page title');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_doMove', $message);
            $error->message('CNT_wiki');
            return $this->_move();
        }

        $result = $GLOBALS['core']->sqlSelect('mod_wiki_pages', 'label', $_POST['newpage']);
        if ($result != NULL) {
            require_once PHPWS_SOURCE_DIR . 'core/Error.php';
            $message = $_SESSION['translate']->it('Page with that name already exists!');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiPage::_doMove', $message);
            $error->message('CNT_wiki');
            return $this->_move();
        }

        $this->setLabel($_POST['newpage']);
        $GLOBALS['core']->sqlUpdate(array('label'=>$this->getLabel()), 'mod_wiki_pages', 'label', $_POST['page']);
        $GLOBALS['core']->sqlUpdate(array('page'=>$this->getLabel()), 'mod_wiki_versions', 'page', $_POST['page']);
        $GLOBALS['core']->sqlUpdate(array('title'=>$this->getLabel(),'link'=>'index.php?module=wiki&amp;page=' . $this->getLabel()), 'mod_fatcat_elements', array('module_id'=>$this->getId(), 'module_title'=>'wiki'));

        // Create redirect page
        $redirect =& new PHPWS_WikiPage($_POST['page']);
        $redirect->setPagetext($_SESSION['translate']->it('This page has moved to [var1].  Please modify links to point to the new location.', $this->getLabel()));
        $redirect->_incVersion();
        $redirect->commit();

        $vdata = array();
        $vdata['editor'] = $redirect->getEditor();
        $vdata['updated'] = time();
        $vdata['version'] = $redirect->getVersion();
        $vdata['comment'] = $_SESSION['translate']->it('Moved page to [var1].', $this->getLabel());
        $vdata['page'] = $redirect->getLabel();
        $vdata['pagetext'] = $redirect->_pagetext;
        $GLOBALS['core']->sqlInsert($vdata, 'mod_wiki_versions');

        $_SESSION['WIKI_message'] = $_SESSION['translate']->it('Wiki Page Moved!');

        // View the freshly moved page
        $_REQUEST['page'] = $this->getLabel();
        $_REQUEST['page_op'] = 'view';
        PHPWS_WikiManager::action();
    }

    /**
     * Displays the discussion page
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _discussion() {
        if ($GLOBALS['core']->moduleExists('comments') && $_SESSION['PHPWS_WikiSettings']['discussion']) {
            $discussion = PHPWS_WikiManager::formatTitle($this->getLabel()) . ' ' . $_SESSION['translate']->it('Discussion');
            $back = '<a href="./index.php?module=wiki&amp;page=' . $_REQUEST['page'] . '">' . $_SESSION['translate']->it('Back to Page') . '</a>';

            $_SESSION['PHPWS_CommentManager']->listCurrentComments('wiki', $this->getId(), $_SESSION['PHPWS_WikiSettings']['discussion_anon']);
            $GLOBALS['CNT_comments']['title'] = str_replace($_SESSION['translate']->it('Comments'), $discussion, $GLOBALS['CNT_comments']['title']);
            $GLOBALS['CNT_comments']['content'] = $back . '<br />' . $GLOBALS['CNT_comments']['content'];
        }
    }

    /**
     * Action
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action() {
        $title = NULL;
        $content = NULL;

        switch($_REQUEST['page_op']) {
            case 'edit':
                $content .= $this->_edit();
                break;

            case 'save':
                $content .= $this->_save();
                break;

            case 'delete':
                $content .= $this->_delete();
                break;

            case 'raw':
                Header('Content-type: text/plain');
                echo $this->_pagetext;
                exit();
                break;

            case 'print':
                echo $this->_view();
                break;

            case 'history':
                $title .= PHPWS_WikiManager::formatTitle($this->getLabel());
                $content .= $this->_history();
                break;

            case 'viewold':
                require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/OldWikiPage.php';
                $oldpage =& new PHPWS_OldWikiPage($_REQUEST['id']);
                $_REQUEST['oldpage_op'] = 'view';
                $_SESSION['WIKI_message'] = $_SESSION['translate']->it('Revision as of [var1]', $oldpage->getListUpdated());
                $oldpage->action();
                break;

            case 'restore':
                require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/OldWikiPage.php';
                $oldpage =& new PHPWS_OldWikiPage($_REQUEST['id']);
                $_REQUEST['oldpage_op'] = 'restore';
                $oldpage->action();
                break;

            case 'compare':
                require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiDiff.php';
                $wikiDiff =& new PHPWS_WikiDiff();
                $title .= PHPWS_WikiManager::formatTitle($this->getLabel());
                $content .= $wikiDiff->diff($_REQUEST['oid'], $_REQUEST['nid']);
                break;

            case 'whatlinkshere':
                $title .= PHPWS_WikiManager::formatTitle($this->getLabel());
                $content .= $this->_whatLinksHere();
                break;

            case 'move':
                $content .= $this->_move();
                break;

            case 'do_move':
                $content .= $this->_doMove();
                break;

            case 'discussion':
                $content .= $this->_discussion();
                break;

            case 'togglelock':
                $this->_toggleLock();
            default:
                $this->_incHits();
                $title .= PHPWS_WikiManager::formatTitle($this->getLabel());
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

class PHPWS_WikiPageList extends PHPWS_WikiPage {

    function PHPWS_WikiPageList($vars) {
        $this->setVars($vars);
    }

    function getListLabel() {
        $label = $this->getLabel();

        return '<a href="./index.php?module=wiki&amp;page=' . $label . '">' . PHPWS_WikiManager::formatTitle($label) . '</a>';
    }

    function getListUpdated() {
        return $this->getUpdated();
    }

    function getListHits() {
        return $this->getHits();
    }

    function getListVersion() {
        return $this->getVersion();
    }

    function getListOrphaned() {
        $sql = "SELECT label FROM mod_wiki_pages WHERE (pagetext LIKE '%" . $this->getLabel() . "%') AND (id != " . $this->getId() . ')';
        $result = $GLOBALS['core']->getOne($sql, TRUE);

        if($result) {
            return '<a href="./index.php?module=wiki&amp;page=' . $this->getLabel() . '&amp;page_op=whatlinkshere">' . $_SESSION['translate']->it('No') . '</a>';
        }
        else {
            return $_SESSION['translate']->it('Yes');
        }
    }

    function getListActions() {
        $actions = array();

        $view   = $_SESSION['translate']->it('View');
        $edit   = $_SESSION['translate']->it('Edit');
        $delete = $_SESSION['translate']->it('Delete');

        $actions[] =  '<a href="./index.php?module=wiki&amp;page=' . $this->getLabel() . '">' . $view . '</a>';

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page')) {
            $actions[] = '<a href="./index.php?module=wiki&amp;page_op=edit&amp;page=' . $this->getLabel() . '">' . $edit . '</a>';
        }

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'delete_page')) {
            $actions[] = '<a href="./index.php?module=wiki&amp;page_op=delete&amp;page=' . $this->getLabel() . '">' . $delete . '</a>';
        }

        return implode(' | ', $actions);
    }
}

?>
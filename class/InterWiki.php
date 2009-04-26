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
 * $Id: InterWiki.php,v 1.2 2006/02/12 05:56:25 blindman1344 Exp $
 */

require_once PHPWS_SOURCE_DIR . 'core/Item.php';

class PHPWS_InterWiki extends PHPWS_Item {

    /**
     * Stores the url of the site
     *
     * @var string
     */
    var $_url = NULL;

    /**
     * Holds reference to list object
     *
     * @var reference
     */
    var $_list = null;


    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function PHPWS_InterWiki($interwiki = null) {
        $this->setTable('mod_wiki_interwiki');
        $this->addExclude(array('_hidden', '_approved', '_list'));

        if(isset($interwiki)) {
            if(is_numeric($interwiki)) {
                $this->setId($interwiki);
                $this->init();
            } elseif(is_array($interwiki)) {
                $this->init($interwiki);
            }
        }
    }// END FUNC PHPWS_InterWiki

    /**
     * Set URL
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function setUrl($url) {
        if (is_string($url) && (strlen($url) > 0)) {
            require_once PHPWS_SOURCE_DIR . 'core/Text.php';
            $this->_url = PHPWS_Text::parseInput($url, 'none');
        }
        else {
            $message = $_SESSION['translate']->it('URL must be set!');
            return new PHPWS_Error('wiki', 'PHPWS_InterWiki::setUrl()', $message);
        }
    }// END FUNC setUrl

    /**
     * Get URL
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListurl() {
        return $this->_url;
    }// END FUNC getListurl

    /**
     * Get List Label
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListlabel() {
        return $this->getLabel();
    }// END FUNC getListlabel

    /**
     * Get updated
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListupdated() {
        return date(PHPWS_DATE_FORMAT . " " . PHPWS_TIME_FORMAT, $this->_updated);
    }// END FUNC getListupdated

    /**
     * Get actions
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListactions() {
        $retVal = '<a href="./index.php?module=wiki&amp;op=editinterwiki&amp;id=' . $this->getId() . '">' . $_SESSION['translate']->it('Edit') . '</a>';
        $retVal .= ' | <a href="./index.php?module=wiki&amp;op=deleteinterwiki&amp;id=' . $this->getId() . '">' . $_SESSION['translate']->it('Delete') . '</a>';

        return $retVal;
    }// END FUNC getListactions

    /**
     * Add interwiki link form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _add() {
        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';

        $form = new EZForm('new_interwiki_url');
        $form->add('module', 'hidden', 'wiki');
        $form->add('op', 'hidden', 'addinterwiki');

        $form->add('label', 'text');
        $form->setSize('label', 35);

        $form->add('url', 'text');
        $form->setSize('url', 50);

        $form->add('save', 'submit', $_SESSION['translate']->it('Add'));

        $tags = $form->getTemplate();
        $tags['LABEL_LABEL'] = $_SESSION['translate']->it('Site Name');
        $tags['URL_LABEL'] = $_SESSION['translate']->it('URL');
        $tags['URL_NOTE'] = $_SESSION['translate']->it('Use %s in the URL string to represent the page name');
        $tags['TOP_LABEL'] = $_SESSION['translate']->it('Add new interwiki link');

        return $tags;
    }// END FUNC _add

    /**
     * Edit interwiki link form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _edit() {
        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';

        $form = new EZForm('edit_interwiki_url');
        $form->add('module', 'hidden', 'wiki');
        $form->add('op', 'hidden', 'saveinterwiki');
        $form->add('id', 'hidden', $this->getId());

        $form->add('label', 'text', $this->_label);
        $form->setSize('label', 35);

        $form->add('url', 'text', $this->_url);
        $form->setSize('url', 50);

        $form->add('save', 'submit', $_SESSION['translate']->it('Edit'));

        $tags = $form->getTemplate();
        $tags['LABEL_LABEL'] = $_SESSION['translate']->it('Site Name');
        $tags['URL_LABEL'] = $_SESSION['translate']->it('URL');
        $tags['URL_NOTE'] = $_SESSION['translate']->it('Use %s in the URL string to represent the page name');
        $tags['TOP_LABEL'] = $_SESSION['translate']->it('Edit interwiki link');

        return $tags;
    }// END FUNC _edit

    /**
     * Save
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _save() {
        require_once PHPWS_SOURCE_DIR . 'core/Error.php';

        $error = $this->setLabel($_POST['label']);
        if (PHPWS_Error::isError($error)) {
            $error->message('CNT_wiki');
            return;
        }

        $error = $this->setUrl($_POST['url']);
        if (PHPWS_Error::isError($error)) {
            $error->message('CNT_wiki');
            return;
        }

        $this->commit();
        return $_SESSION['translate']->it('Link Saved!');
    }// END FUNC _save

    /**
     * Delete
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _delete() {
        if (isset($_REQUEST['yes'])) {
            $this->kill();
            return $_SESSION['translate']->it('Interwiki link deleted!');
        } else if (isset($_REQUEST['no'])) {
            return $_SESSION['translate']->it('Interwiki link was not deleted!');
        } else {
            $tags = array();
            $tags['TOP_LABEL'] = $_SESSION['translate']->it('Are you sure you want to delete this interwiki link?');

            $tags['LABEL_LABEL'] = $_SESSION['translate']->it('Site Name');
            $tags['URL_LABEL'] = $_SESSION['translate']->it('URL');
            $tags['LABEL'] = $this->_label;
            $tags['URL'] = $this->_url;

            $tags['YES'] = '<a href="index.php?module=wiki&amp;op=dodeleteinterwiki&amp;yes=1&amp;id=' . $this->getId() . '">' . $_SESSION['translate']->it('Yes') . '</a>';
            $tags['NO'] = '<a href="index.php?module=wiki&amp;op=dodeleteinterwiki&amp;no=1&amp;id=' . $this->getId() . '">' . $_SESSION['translate']->it('No') .'</a>';

            return $tags;
        }
    }// END FUNC _delete

    /**
     * Interwiki Setup
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function setup() {
        if (!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') && !($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_InterWiki::setup()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if ($_REQUEST['op'] == 'editinterwiki') {
            $tags = $this->_edit();
        }
        else if ($_REQUEST['op'] == 'deleteinterwiki') {
            $tags = $this->_delete();
        }
        else {
            $tags = $this->_add();
        }

        if (($_REQUEST['op'] == 'addinterwiki') || ($_REQUEST['op'] == 'saveinterwiki')) {
            $tags['MESSAGE'] = $this->_save();
        }
        else if ($_REQUEST['op'] == 'dodeleteinterwiki') {
            $tags['MESSAGE'] = $this->_delete();
        }

        $tags['BACK'] = '<a href="./index.php?module=wiki">' . $_SESSION['translate']->it('Back to Wiki') . '</a>';

        if(!isset($this->_list)) {
            $this->_list =& new PHPWS_List;
        }

        $listSettings = array('limit'   => 10,
                              'section' => true,
                              'limits'  => array(5,10,20,50),
                              'back'    => '&#60;&#60;',
                              'forward' => '&#62;&#62;',
                              'anchor'  => false);

        $this->_list->setModule('wiki');
        $this->_list->setClass('PHPWS_InterWiki');
        $this->_list->setTable('mod_wiki_interwiki');
        $this->_list->setDbColumns(array('id', 'label', 'url', 'updated'));
        $this->_list->setListColumns(array('label', 'url', 'updated', 'actions'));
        $this->_list->setName('interwiki');
        $this->_list->setOp('op=interwikisetup');
        $this->_list->anchorOn();
        $this->_list->setPaging($listSettings);
        $this->_list->setOrder('label');
        $this->_list->setExtraListTags(array('TITLE' => $_SESSION['translate']->it('Site list'),
                                             'USAGE' => $_SESSION['translate']->it('To link to an interwiki site, use [var1].', 'WikiName:PageName'),
                                             'LABEL_LABEL' => $_SESSION['translate']->it('Site Name'),
                                             'URL_LABEL' => $_SESSION['translate']->it('URL'),
                                             'UPDATED_LABEL' => $_SESSION['translate']->it('Updated'),
                                             'ACTIONS_LABEL' => $_SESSION['translate']->it('Actions')));

        $tags['INTERWIKI_LIST'] = $this->_list->getList();

        $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Interwiki Setup');
        $GLOBALS['CNT_wiki']['content'] .= PHPWS_Template::processTemplate($tags, 'wiki', 'interwiki/setup.tpl');
    }// END FUNC setup
}

?>
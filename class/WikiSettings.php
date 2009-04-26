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
 * $Id: WikiSettings.php,v 1.1 2006/02/12 06:23:10 blindman1344 Exp $
 */

class PHPWS_WikiSettings {

    /**
     * Settings Administration
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function admin() {
        if (!$_SESSION['OBJ_user']->allow_access('wiki', 'edit_settings')) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiSettings::_admin()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'mod/help/class/CLS_help.php';
        require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiPage.php';
        require_once PHPWS_SOURCE_DIR . 'core/List.php';

        if (isset($_POST['op']) && ($_POST['op'] == 'savesettings')) {
            $message = PHPWS_WikiSettings::_save();
        }

        $tabs = 1;
        $form = new EZform('wiki_settings');

        $form->add('show_on_home', 'checkbox');
        $form->setMatch('show_on_home', $_SESSION['PHPWS_WikiSettings']['show_on_home']);
        $form->setTab('show_on_home', $tabs);
        $tabs++;

        $form->add('allow_anon_view', 'checkbox');
        $form->setMatch('allow_anon_view', $_SESSION['PHPWS_WikiSettings']['allow_anon_view']);
        $form->setTab('allow_anon_view', $tabs);
        $tabs++;

        $form->add('allow_page_edit', 'checkbox');
        $form->setMatch('allow_page_edit', $_SESSION['PHPWS_WikiSettings']['allow_page_edit']);
        $form->setTab('allow_page_edit', $tabs);
        $tabs++;

        $form->add('allow_image_upload', 'checkbox');
        $form->setMatch('allow_image_upload', $_SESSION['PHPWS_WikiSettings']['allow_image_upload']);
        $form->setTab('allow_image_upload', $tabs);
        $tabs++;

        $form->add('allow_bbcode', 'checkbox');
        $form->setMatch('allow_bbcode', $_SESSION['PHPWS_WikiSettings']['allow_bbcode']);
        $form->setTab('allow_bbcode', $tabs);
        $tabs++;

        $form->add('ext_chars_support', 'checkbox');
        $form->setMatch('ext_chars_support', $_SESSION['PHPWS_WikiSettings']['ext_chars_support']);
        $form->setTab('ext_chars_support', $tabs);
        $tabs++;

        $form->add('add_to_title', 'checkbox');
        $form->setMatch('add_to_title', $_SESSION['PHPWS_WikiSettings']['add_to_title']);
        $form->setTab('add_to_title', $tabs);
        $tabs++;

        $form->add('format_title', 'checkbox');
        $form->setMatch('format_title', $_SESSION['PHPWS_WikiSettings']['format_title']);
        $form->setTab('format_title', $tabs);
        $tabs++;

        $form->add('show_modified_info', 'checkbox');
        $form->setMatch('show_modified_info', $_SESSION['PHPWS_WikiSettings']['show_modified_info']);
        $form->setTab('show_modified_info', $tabs);
        $tabs++;

        $form->add('monitor_edits', 'checkbox');
        $form->setMatch('monitor_edits', $_SESSION['PHPWS_WikiSettings']['monitor_edits']);
        $form->setTab('monitor_edits', $tabs);
        $tabs++;

        $form->add('admin_email', 'text', $_SESSION['PHPWS_WikiSettings']['admin_email']);
        $form->setSize('admin_email', 25);
        $form->setTab('admin_email', $tabs);
        $tabs++;

        $form->add('email_text', 'textarea', $_SESSION['PHPWS_WikiSettings']['email_text']);
        $form->setCols('email_text', 50);
        $form->setRows('email_text', 5);
        $form->setTab('email_text', $tabs);
        $tabs++;

        $form->add('default_page', 'text', $_SESSION['PHPWS_WikiSettings']['default_page']);
        $form->setSize('default_page', 25);
        $form->setTab('default_page', $tabs);
        $tabs++;

        $options = array('_blank'=>'_blank', '_parent'=>'_parent', '_self'=>'_self', '_top'=>'_top');
        $form->add('ext_page_target', 'select', $options);
        $form->setMatch('ext_page_target', $_SESSION['PHPWS_WikiSettings']['ext_page_target']);
        $form->setTab('ext_page_target', $tabs);
        $tabs++;

        $form->add('immutable_page', 'checkbox');
        $form->setMatch('immutable_page', $_SESSION['PHPWS_WikiSettings']['immutable_page']);
        $form->setTab('immutable_page', $tabs);
        $tabs++;

        $form->add('raw_text', 'checkbox');
        $form->setMatch('raw_text', $_SESSION['PHPWS_WikiSettings']['raw_text']);
        $form->setTab('raw_text', $tabs);
        $tabs++;

        $form->add('print_view', 'checkbox');
        $form->setMatch('print_view', $_SESSION['PHPWS_WikiSettings']['print_view']);
        $form->setTab('print_view', $tabs);
        $tabs++;

        $form->add('discussion', 'checkbox');
        $form->setMatch('discussion', $_SESSION['PHPWS_WikiSettings']['discussion']);
        $form->setTab('discussion', $tabs);
        $tabs++;

        $form->add('discussion_anon', 'checkbox');
        $form->setMatch('discussion_anon', $_SESSION['PHPWS_WikiSettings']['discussion_anon']);
        $form->setTab('discussion_anon', $tabs);
        $tabs++;

        $form->add('save', 'submit', $_SESSION['translate']->it('Save Settings'));
        $form->setTab('save', $tabs);

        $form->add('module', 'hidden', 'wiki');
        $form->add('op', 'hidden', 'savesettings');

        $tags = $form->getTemplate();
        $tags['BACK'] = '<a href="./index.php?module=wiki">' . $_SESSION['translate']->it('Back to Wiki') . '</a>';
        if (isset($message)) { $tags['MESSAGE'] = $message; }
        $tags['SHOW_ON_HOME_LABEL'] = $_SESSION['translate']->it('Show on home page');
        $tags['ALLOW_ANON_VIEW_LABEL'] = $_SESSION['translate']->it('Allow Anonymous Viewing');
        $tags['ALLOW_PAGE_EDIT_LABEL'] = $_SESSION['translate']->it('Allow all registered users to edit pages');
        $tags['ALLOW_IMAGE_UPLOAD_LABEL'] = $_SESSION['translate']->it('Allow all registered users to upload images');
        $tags['ALLOW_BBCODE_LABEL'] = $_SESSION['translate']->it('Enable BBCode parser');
        $tags['EXT_CHARS_SUPPORT_LABEL'] = $_SESSION['translate']->it('Enable extended character set for wiki page names');
        $tags['ADD_TO_TITLE_LABEL'] = $_SESSION['translate']->it('Add wiki page title to site title');
        $tags['FORMAT_TITLE_LABEL'] = $_SESSION['translate']->it('Format the wiki page title before displaying');
        $tags['SHOW_MODIFIED_INFO_LABEL'] = $_SESSION['translate']->it('Show page modified information');
        $tags['ADMIN_EMAIL_LABEL'] = $_SESSION['translate']->it('Wiki Admin Email');
        $tags['MONITOR_EDITS_LABEL'] = $_SESSION['translate']->it('Monitor Edits');
        $tags['EMAIL_TEXT_LABEL'] = $_SESSION['translate']->it('Email Notification Text');
        $tags['DEFAULT_PAGE_LABEL'] = $_SESSION['translate']->it('Default page');
        $tags['EXT_PAGE_TARGET_LABEL'] = $_SESSION['translate']->it('Target for external links');
        $tags['IMMUTABLE_PAGE_LABEL'] = $_SESSION['translate']->it('Show Immutable Page text (if applicable)');
        $tags['RAW_TEXT_LABEL'] = $_SESSION['translate']->it('Show Raw Text link');
        $tags['PRINT_VIEW_LABEL'] = $_SESSION['translate']->it('Show Print View link');
        $tags['MENU_ITEMS_LABEL'] = $_SESSION['translate']->it('Menu Items');
        $tags['DISCUSSION_LABEL'] = $_SESSION['translate']->it('Enable discussion for registered users');
        $tags['DISCUSSION_ANON_LABEL'] = $_SESSION['translate']->it('Allow anonymous discussion');
        $tags['DISCUSSION_SECTION_LABEL'] = $_SESSION['translate']->it('Discussion');
        if (!$GLOBALS['core']->moduleExists('comments')) $tags['COMMENTS_LABEL'] = $_SESSION['translate']->it('Comments module required');
        $tags['SETTINGS_LABEL'] = $_SESSION['translate']->it('Settings');

        $tags['SHOW_ON_HOME_HELP'] = CLS_help::show_link('wiki', 'show_on_home');
        $tags['ALLOW_ANON_VIEW_HELP'] = CLS_help::show_link('wiki', 'allow_anon_view');
        $tags['ALLOW_PAGE_EDIT_HELP'] = CLS_help::show_link('wiki', 'allow_page_edit');
        $tags['ALLOW_IMAGE_UPLOAD_HELP'] = CLS_help::show_link('wiki', 'allow_image_upload');
        $tags['ALLOW_BBCODE_HELP'] = CLS_help::show_link('wiki', 'allow_bbcode');
        $tags['EXT_CHARS_SUPPORT_HELP'] = CLS_help::show_link('wiki', 'ext_chars_support');
        $tags['ADD_TO_TITLE_HELP'] = CLS_help::show_link('wiki', 'add_to_title');
        $tags['FORMAT_TITLE_HELP'] = CLS_help::show_link('wiki', 'format_title');
        $tags['SHOW_MODIFIED_INFO_HELP'] = CLS_help::show_link('wiki', 'show_modified_info');
        $tags['ADMIN_EMAIL_HELP'] = CLS_help::show_link('wiki', 'admin_email');
        $tags['MONITOR_EDITS_HELP'] = CLS_help::show_link('wiki', 'monitor_edits');
        $tags['EMAIL_TEXT_HELP'] = CLS_help::show_link('wiki', 'email_text');
        $tags['DEFAULT_PAGE_HELP'] = CLS_help::show_link('wiki', 'default_page');
        $tags['EXT_PAGE_TARGET_HELP'] = CLS_help::show_link('wiki', 'ext_page_target');
        $tags['MENU_ITEMS_HELP'] = CLS_help::show_link('wiki', 'menu_items');
        $tags['DISCUSSION_HELP'] = CLS_help::show_link('wiki', 'discussion');

        $list =& new PHPWS_List;
        $listSettings = array('limit'   => 10,
                              'section' => true,
                              'limits'  => array(5,10,20,50),
                              'back'    => '&#60;&#60;',
                              'forward' => '&#62;&#62;',
                              'anchor'  => false);

        $list->setModule('wiki');
        $list->setClass('PHPWS_WikiPageList');
        $list->setTable('mod_wiki_pages');
        $list->setDbColumns(array('id', 'label', 'updated', 'hits', 'version'));
        $list->setListColumns(array('label', 'updated', 'hits', 'version', 'orphaned', 'actions'));
        $list->setName('list');
        $list->setOp('op=admin');
        $list->anchorOn();
        $list->setPaging($listSettings);
        $list->setOrder('label');
        $list->setExtraListTags(array('TITLE' => $_SESSION['translate']->it('Wiki Pages'),
                                      'LABEL_LABEL' => $_SESSION['translate']->it('Page Name'),
                                      'UPDATED_LABEL' => $_SESSION['translate']->it('Updated'),
                                      'HITS_LABEL' => $_SESSION['translate']->it('Hits'),
                                      'VERSION_LABEL' => $_SESSION['translate']->it('Version'),
                                      'ORPHANED_LABEL' => $_SESSION['translate']->it('Orphaned'),
                                      'ORPHANED_HELP' => CLS_help::show_link('wiki', 'orphaned_pages'),
                                      'ACTIONS_LABEL' => $_SESSION['translate']->it('Actions')));

        $tags['PAGE_LIST'] = $list->getList();

        $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Wiki Administration');
        $GLOBALS['CNT_wiki']['content'] .= PHPWS_Template::processTemplate($tags, 'wiki', 'admin.tpl');
    }// END FUNC admin

    /**
     * Save new settings
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _save() {
        require_once PHPWS_SOURCE_DIR . 'core/Text.php';

        if(isset($_POST['show_on_home']))
            $_SESSION['PHPWS_WikiSettings']['show_on_home'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['show_on_home'] = 0;

        if(isset($_POST['allow_anon_view']))
            $_SESSION['PHPWS_WikiSettings']['allow_anon_view'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['allow_anon_view'] = 0;

        if(isset($_POST['allow_page_edit']))
            $_SESSION['PHPWS_WikiSettings']['allow_page_edit'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['allow_page_edit'] = 0;

        if(isset($_POST['allow_image_upload']))
            $_SESSION['PHPWS_WikiSettings']['allow_image_upload'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['allow_image_upload'] = 0;

        if(isset($_POST['allow_bbcode']))
            $_SESSION['PHPWS_WikiSettings']['allow_bbcode'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['allow_bbcode'] = 0;

        if(isset($_POST['ext_chars_support']))
            $_SESSION['PHPWS_WikiSettings']['ext_chars_support'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['ext_chars_support'] = 0;

        if(isset($_POST['add_to_title']))
            $_SESSION['PHPWS_WikiSettings']['add_to_title'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['add_to_title'] = 0;

        if(isset($_POST['format_title']))
            $_SESSION['PHPWS_WikiSettings']['format_title'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['format_title'] = 0;

        if(isset($_POST['show_modified_info']))
            $_SESSION['PHPWS_WikiSettings']['show_modified_info'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['show_modified_info'] = 0;

        if(isset($_POST['monitor_edits']))
            $_SESSION['PHPWS_WikiSettings']['monitor_edits'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['monitor_edits'] = 0;

        if(isset($_POST['admin_email']))
            $_SESSION['PHPWS_WikiSettings']['admin_email'] = PHPWS_Text::parseInput($_POST['admin_email'], 'none');

        if(isset($_POST['email_text']))
            $_SESSION['PHPWS_WikiSettings']['email_text'] = PHPWS_Text::parseInput($_POST['email_text'], 'none');

        if(isset($_POST['default_page']) && (strlen($_POST['default_page']) > 0))
            $_SESSION['PHPWS_WikiSettings']['default_page'] = PHPWS_Text::parseInput($_POST['default_page'], 'none');

        if(isset($_POST['ext_page_target']))
            $_SESSION['PHPWS_WikiSettings']['ext_page_target'] = PHPWS_Text::parseInput($_POST['ext_page_target'], 'none');

        if(isset($_POST['immutable_page']))
            $_SESSION['PHPWS_WikiSettings']['immutable_page'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['immutable_page'] = 0;

        if(isset($_POST['raw_text']))
            $_SESSION['PHPWS_WikiSettings']['raw_text'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['raw_text'] = 0;

        if(isset($_POST['print_view']))
            $_SESSION['PHPWS_WikiSettings']['print_view'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['print_view'] = 0;

        if(isset($_POST['discussion']))
            $_SESSION['PHPWS_WikiSettings']['discussion'] = 1;
        else
            $_SESSION['PHPWS_WikiSettings']['discussion'] = 0;

        if(isset($_POST['discussion_anon']))
        {
            $_SESSION['PHPWS_WikiSettings']['discussion'] = 1;
            $_SESSION['PHPWS_WikiSettings']['discussion_anon'] = 1;
        }
        else
            $_SESSION['PHPWS_WikiSettings']['discussion_anon'] = 0;

        if($GLOBALS['core']->sqlUpdate($_SESSION['PHPWS_WikiSettings'], 'mod_wiki_settings')) {
            return $_SESSION['translate']->it('Your settings have been successfully saved.');
        } else {
            return $_SESSION['translate']->it('There was an error saving the settings.');
        }
    }// END FUNC _save
}

?>
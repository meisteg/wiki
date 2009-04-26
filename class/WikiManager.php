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
 * $Id: WikiManager.php,v 1.46 2006/03/05 04:10:22 blindman1344 Exp $
 */

class PHPWS_WikiManager {

    /**
     * Get the current Wiki settings from the database.
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getSettings() {
        if (!isset($_SESSION['PHPWS_WikiSettings'])) {
            $result = $GLOBALS['core']->sqlSelect('mod_wiki_settings');
            $_SESSION['PHPWS_WikiSettings'] = $result[0];
        }
    }// END FUNC getSettings

    /**
     * Transform text using Wiki library
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function transform($wikitext) {
        require_once PHPWS_SOURCE_DIR . 'mod/wiki/conf/config.php';
        require_once 'Text/Wiki.php';

        if (!isset($parser)) {
            $parser = 'Text_Wiki';
        }
        else if ($parser != 'Text_Wiki') {
            @include_once 'Text/Wiki/' . $parser . '.php';
            $parser = 'Text_Wiki_' . $parser;
            if (!class_exists($parser)) {
                return $_SESSION['translate']->it('Error! [var1] parser not found.', $parser);
            }
        }

        // The newer versions of the parseInput function replace apostrophes with HTML code
        // and parseOutput switches it back.  This module uses parseInput and not parseOutput
        // for the pagetext, so we have to make the fix here.
        $wikitext = str_replace("&#39;", "'", $wikitext);

        $sql = 'SELECT label FROM mod_wiki_pages';
        $pages = $GLOBALS['core']->getCol($sql, TRUE);

        $wiki = new $parser;

        // Add custom parser rules
        $wiki->addPath('parse', PHPWS_SOURCE_DIR . 'mod/wiki/class/parse/');
        $wiki->insertRule('Template', '');

        $wiki->setRenderConf('xhtml', 'wikilink', 'pages', $pages);
        $wiki->setRenderConf('xhtml', 'wikilink', 'view_url', './index.php?module=wiki&amp;page=%s');
        $wiki->setRenderConf('xhtml', 'wikilink', 'new_url', './index.php?module=wiki&amp;page=%s');
        $wiki->setRenderConf('xhtml', 'toc', 'title', '<strong>' . $_SESSION['translate']->it('Table of Contents') . '</strong>');
        $wiki->setRenderConf('xhtml', 'image', 'base', 'images/wiki/');
        $wiki->setRenderConf('xhtml', 'url', 'target', $_SESSION['PHPWS_WikiSettings']['ext_page_target']);
        $wiki->setRenderConf('xhtml', 'interwiki', 'target', $_SESSION['PHPWS_WikiSettings']['ext_page_target']);

        $sites = array();
        $result = $GLOBALS['core']->sqlSelect('mod_wiki_interwiki');
        foreach ($result as $row) {
            $sites[$row['label']] = $row['url'];
        }
        $wiki->setRenderConf('xhtml', 'interwiki', 'sites', $sites);

        if ($_SESSION['PHPWS_WikiSettings']['ext_chars_support']) {
            $wiki->setParseConf('Wikilink', 'ext_chars', true);
        }

        // Setting CSS styles for tags
        $wiki->setRenderConf('xhtml', 'blockquote', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'code', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'deflist', 'css_dl', 'wiki');
        $wiki->setRenderConf('xhtml', 'deflist', 'css_dt', 'wiki');
        $wiki->setRenderConf('xhtml', 'deflist', 'css_dd', 'wiki');
        $wiki->setRenderConf('xhtml', 'emphasis', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h1', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h2', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h3', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h4', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h5', 'wiki');
        $wiki->setRenderConf('xhtml', 'heading', 'css_h6', 'wiki');
        $wiki->setRenderConf('xhtml', 'horiz', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'image', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'interwiki', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'list', 'css_ol', 'wiki');
        $wiki->setRenderConf('xhtml', 'list', 'css_ol_li', 'wiki');
        $wiki->setRenderConf('xhtml', 'list', 'css_ul', 'wiki');
        $wiki->setRenderConf('xhtml', 'list', 'css_ul_li', 'wiki');
        $wiki->setRenderConf('xhtml', 'paragraph', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'table', 'css_table', 'wiki');
        $wiki->setRenderConf('xhtml', 'table', 'css_tr', 'wiki');
        $wiki->setRenderConf('xhtml', 'table', 'css_th', 'wiki');
        $wiki->setRenderConf('xhtml', 'table', 'css_td', 'wiki');
        $wiki->setRenderConf('xhtml', 'tt', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'url', 'css_inline', 'wiki');
        $wiki->setRenderConf('xhtml', 'url', 'css_footnote', 'wiki');
        $wiki->setRenderConf('xhtml', 'url', 'css_descr', 'wiki');
        $wiki->setRenderConf('xhtml', 'url', 'css_img', 'wiki');
        $wiki->setRenderConf('xhtml', 'wikilink', 'css', 'wiki');
        $wiki->setRenderConf('xhtml', 'wikilink', 'css_new', 'wiki');

        if ($GLOBALS['core']->text->strip_profanity)
            $wikitext = $wiki->transform(PHPWS_Text::profanityFilter($wikitext));
        else
            $wikitext = $wiki->transform($wikitext);

        if ($_SESSION['PHPWS_WikiSettings']['allow_bbcode']) {
            // Borrowed BBCodeParser code from core/Text.php parseOutput function
            require_once('HTML/BBCodeParser.php');
            $config = parse_ini_file(PHPWS_SOURCE_DIR . '/conf/BBCodeParser.ini', true);
            $options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
            $options = $config['HTML_BBCodeParser'];
            unset($options);
            $bbparser = new HTML_BBCodeParser();
            $bbparser->setText($wikitext);
            $bbparser->parse();
            $wikitext = $bbparser->getParsed();
        }

        return $wikitext;
    }// END FUNC transform

    /**
     * Format the wiki title text
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function formatTitle($title) {
        if($_SESSION['PHPWS_WikiSettings']['format_title']) {
            $title = ereg_replace("[A-Z]", " \\0", $title);
        }
        return $title;
    }// END FUNC formatTitle

    /**
     * Used by search module
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function search($where) {
        $resultArray = array();

        /* Needed because it is possible that the WikiSettings session variable is not set yet */
        PHPWS_WikiManager::getSettings();

        if(!$_SESSION['PHPWS_WikiSettings']['allow_anon_view'] && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_wiki']['content'] = $_SESSION['translate']->it('Anonymous viewing of the wiki has been disabled.');
        } else {
            $sql = 'SELECT label FROM mod_wiki_pages ' . $where;
            $result = $GLOBALS['core']->getAll($sql, TRUE);

            if($result) {
                foreach ($result as $row) {
                    $resultArray[$row['label']] = PHPWS_WikiManager::formatTitle($row['label']);
                }
            }
        }

        return $resultArray;
    }// END FUNC search

    /**
     * Sends email to Wiki Admin if option enabled
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function sendEmail() {
        if($_SESSION['PHPWS_WikiSettings']['monitor_edits']) {
            // PEAR mail class
            require_once('Mail.php');

            $admin_email = $_SESSION['PHPWS_WikiSettings']['admin_email'];
            if (empty($admin_email)) {
                extract(PHPWS_User::getSettings());
                $admin_email = $user_contact;
            }
            $from = '"' . $_SESSION['translate']->it('Wiki Admin') . '" <'.$admin_email.'>';
            $subject = $_SESSION['translate']->it('[var1] updated!', PHPWS_WikiManager::formatTitle($_REQUEST['page']));
            $message = $_SESSION['PHPWS_WikiSettings']['email_text'];
            // Replace [page] and [url] in $message
            $message = str_replace('[page]', '"' . PHPWS_WikiManager::formatTitle(strip_tags($_REQUEST['page'])) . '"', $message);
            $message = str_replace('[url]', 'http://' . PHPWS_HOME_HTTP . 'index.php?module=wiki&page=' . $_REQUEST['page'], $message);

            $mail_object =& Mail::factory('mail');
            $headers['From'] = $from;
            $headers['Subject'] = $subject;
            $mail_object->send($admin_email, $headers, $message);
        }
    }// END FUNC sendEmail

    /**
     * Image upload
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _imageUpload() {
        if (!($_SESSION['PHPWS_WikiSettings']['allow_image_upload'] && $_SESSION['OBJ_user']->username)
            && !$_SESSION['OBJ_user']->allow_access('wiki', 'upload_images')) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiManager::_imageUpload()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiImage.php';
        require_once PHPWS_SOURCE_DIR . 'core/List.php';

        if (isset($_POST['op']) && ($_POST['op'] == 'doimageupload')) {
            $newImage = new PHPWS_WikiImage;
            $message = $newImage->save();
        }

        if ($_REQUEST['op'] == 'doimagedelete') {
            $delImage = new PHPWS_WikiImage($_REQUEST['id']);
            $message = $delImage->delete();
        }

        $tags = PHPWS_WikiImage::add();
        $tags['BACK'] = '<a href="./index.php?module=wiki">' . $_SESSION['translate']->it('Back to Wiki') . '</a>';
        if (isset($message)) { $tags['MESSAGE'] = $message; }
        $tags['IMAGE_UPLOAD_LABEL'] = $_SESSION['translate']->it('Image Upload');

        $list =& new PHPWS_List;
        $listSettings = array('limit'   => 10,
                              'section' => true,
                              'limits'  => array(5,10,20,50),
                              'back'    => '&#60;&#60;',
                              'forward' => '&#62;&#62;',
                              'anchor'  => false);

        $list->setModule('wiki');
        $list->setClass('PHPWS_WikiImage');
        $list->setTable('mod_wiki_images');
        $list->setDbColumns(array('id', 'owner', 'created', 'filename', 'size', 'type', 'summary'));
        $list->setListColumns(array('owner', 'created', 'filename', 'size', 'type', 'summary', 'actions'));
        $list->setName('images');
        $list->setOp('op=imageupload');
        $list->anchorOn();
        $list->setPaging($listSettings);
        $list->setOrder('filename');
        $list->setExtraListTags(array('TITLE' => $_SESSION['translate']->it('Image List'),
                                      'USAGE' => $_SESSION['translate']->it('To include an image in a page, use [var1].', '[[image picture.jpg]]'),
                                      'OWNER_LABEL' => $_SESSION['translate']->it('Uploader'),
                                      'CREATED_LABEL' => $_SESSION['translate']->it('Upload Date'),
                                      'FILENAME_LABEL' => $_SESSION['translate']->it('Filename'),
                                      'SIZE_LABEL' => $_SESSION['translate']->it('Size'),
                                      'TYPE_LABEL' => $_SESSION['translate']->it('Type'),
                                      'ACTIONS_LABEL' => $_SESSION['translate']->it('Actions')));

        $tags['IMAGE_LIST'] = $list->getList();

        $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Wiki Images');
        $GLOBALS['CNT_wiki']['content'] .= PHPWS_Template::processTemplate($tags, 'wiki', 'images/admin.tpl');
    }// END FUNC _imageUpload

    /**
     * Recent Changes
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _recentChanges() {
        require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/OldWikiPage.php';
        require_once PHPWS_SOURCE_DIR . 'core/List.php';

        $tags = array();
        $tags['BACK'] = '<a href="./index.php?module=wiki">' . $_SESSION['translate']->it('Back to Wiki') . '</a>';
        $tags['VIEW_LABEL'] = $_SESSION['translate']->it('View');
        $tags['PAGE_LABEL'] = $_SESSION['translate']->it('Page Name');
        $tags['UPDATED_LABEL'] = $_SESSION['translate']->it('Updated');
        $tags['EDITOR_LABEL'] = $_SESSION['translate']->it('Editor');
        $tags['COMMENT_LABEL'] = $_SESSION['translate']->it('Comment');

        $list =& new PHPWS_List;
        $listSettings = array('limit'   => 25,
                              'section' => true,
                              'limits'  => array(25,50,75,100),
                              'back'    => '&#60;&#60;',
                              'forward' => '&#62;&#62;',
                              'anchor'  => false);

        $list->setModule('wiki');
        $list->setClass('PHPWS_OldWikiPage');
        $list->setTable('mod_wiki_versions');
        $list->setDbColumns(array('id', 'page', 'editor', 'updated', 'version', 'comment'));
        $list->setListColumns(array('view', 'page', 'updated', 'editor', 'comment'));
        $list->setName('recentchanges');
        $list->setOp('op=recentchanges');
        $list->anchorOn();
        $list->setPaging($listSettings);
        $list->setOrder('id desc');
        $list->setExtraListTags($tags);

        $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Recent changes');
        $GLOBALS['CNT_wiki']['content'] .= $list->getList();
    }// END FUNC _recentChanges

    /**
     * Gets random page from the database
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _random() {
        $_REQUEST['op'] = NULL;
        $sql = 'SELECT label FROM mod_wiki_pages ORDER BY RAND() LIMIT 1';
        $result = $GLOBALS['core']->getOne($sql, TRUE);

        if ($result != NULL) {
            $_REQUEST['page'] = $result;
        }
        PHPWS_WikiManager::action();
    }// END FUNC _random

    /**
     * Wiki Toolbox
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function _toolbox() {
        $admin = $_SESSION['translate']->it('Admin');
        $image = $_SESSION['translate']->it('Image upload');
        $linkshere = $_SESSION['translate']->it('What links here');
        $recentchanges = $_SESSION['translate']->it('Recent changes');
        $randompage = $_SESSION['translate']->it('Random page');
        $interwiki = $_SESSION['translate']->it('Interwiki setup');

        $GLOBALS['CNT_wiki_toolbox'] = array('title' => NULL, 'content' => NULL);
        $content = NULL;

        if (isset($_REQUEST['page']) && isset($_REQUEST['page_op'])) {
            if (($_REQUEST['page_op']=='view') || ($_REQUEST['page_op']=='togglelock')) {
                $link = '<a href="./index.php?module=wiki&amp;page=' . $_REQUEST['page'] . '&amp;page_op=whatlinkshere">' . $linkshere . '</a>';
                $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');
            }
        }

        $link = '<a href="./index.php?module=wiki&amp;op=recentchanges">' . $recentchanges . '</a>';
        $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');

        $link = '<a href="./index.php?module=wiki&amp;op=random">' . $randompage . '</a>';
        $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');

        if (($_SESSION['PHPWS_WikiSettings']['allow_image_upload'] && $_SESSION['OBJ_user']->username)
            || $_SESSION['OBJ_user']->allow_access('wiki', 'upload_images')) {
            $link = '<a href="./index.php?module=wiki&amp;op=imageupload">' . $image . '</a>';
            $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');
        }

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_page') || ($_SESSION['PHPWS_WikiSettings']['allow_page_edit'] && $_SESSION['OBJ_user']->username)) {
            $link = '<a href="./index.php?module=wiki&amp;op=interwikisetup">' . $interwiki . '</a>';
            $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');
        }

        if ($_SESSION['OBJ_user']->allow_access('wiki', 'edit_settings')) {
            $link = '<a href="./index.php?module=wiki&amp;op=admin">' . $admin . '</a>';
            $content .= PHPWS_Template::processTemplate(array('LINK'=>$link), 'wiki', 'toolbox/link.tpl');
        }

        $GLOBALS['CNT_wiki_toolbox']['title'] = $_SESSION['translate']->it('Wiki Toolbox');
        $GLOBALS['CNT_wiki_toolbox']['content'] = PHPWS_Template::processTemplate(array('LINKS'=>$content), 'wiki', 'toolbox/toolbox.tpl');
    }// END FUNC _toolbox

    /**
     * Action
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function action() {
        if(!$_SESSION['PHPWS_WikiSettings']['allow_anon_view'] && !$_SESSION['OBJ_user']->username) {
            $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Anonymous Viewing Denied');
            $GLOBALS['CNT_wiki']['content'] .= $_SESSION['translate']->it('Anonymous viewing of the wiki has been disabled.');
        } else {
            if(isset($_REQUEST['page']) && is_string($_REQUEST['page'])) {
                require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiPage.php';
                $wikipage =& new PHPWS_WikiPage($_REQUEST['page']);

                if(!isset($_REQUEST['op']) && !isset($_REQUEST['page_op'])) {
                    $_REQUEST['page_op'] = 'view';
                }
            }

            // Display the Wiki Toolbox
            PHPWS_WikiManager::_toolbox();

            if(isset($_REQUEST['page_op']) && isset($wikipage)) {
                $wikipage->action();
                return;
            }

            switch(@$_REQUEST['op']) {
                case 'admin':
                case 'savesettings':
                    require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiSettings.php';
                    PHPWS_WikiSettings::admin();
                    break;

                case 'doimagedelete':
                case 'doimageupload':
                case 'imageupload':
                    PHPWS_WikiManager::_imageUpload();
                    break;

                case 'imagedelete':
                    require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/WikiImage.php';
                    $delImage = new PHPWS_WikiImage($_REQUEST['id']);
                    $GLOBALS['CNT_wiki']['title'] = $_SESSION['translate']->it('Wiki Images');
                    $GLOBALS['CNT_wiki']['content'] .= $delImage->delete();
                    break;

                case 'recentchanges':
                    PHPWS_WikiManager::_recentChanges();
                    break;

                case 'random':
                    PHPWS_WikiManager::_random();
                    break;

                case 'interwikisetup':
                case 'addinterwiki':
                    require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/InterWiki.php';
                    $interwiki = new PHPWS_InterWiki();
                    $interwiki->setup();
                    break;

                case 'editinterwiki':
                case 'saveinterwiki':
                case 'deleteinterwiki':
                case 'dodeleteinterwiki':
                    require_once PHPWS_SOURCE_DIR . 'mod/wiki/class/InterWiki.php';
                    $interwiki = new PHPWS_InterWiki($_REQUEST['id']);
                    $interwiki->setup();
                    break;

                default:
                    $_REQUEST['page'] = $_SESSION['PHPWS_WikiSettings']['default_page'];
                    PHPWS_WikiManager::action();
            }
        }
    }// END FUNC action
}

?>
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
 * $Id: WikiImage.php,v 1.3 2006/02/12 05:56:25 blindman1344 Exp $
 */

require_once PHPWS_SOURCE_DIR . 'core/Item.php';

class PHPWS_WikiImage extends PHPWS_Item {

    /**
     * Stores the filename of the image
     *
     * @var string
     */
    var $_filename = NULL;

    /**
     * Size of the image
     *
     * @var int
     */
    var $_size = 0;

    /**
     * Type of the image
     *
     * @var string
     */
    var $_type = NULL;

    /**
     * Summary of the image
     *
     * @var string
     */
    var $_summary = null;


    /**
     * Constructor
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function PHPWS_WikiImage($image = null) {
        $this->setTable('mod_wiki_images');
        $this->addExclude(array('_hidden', '_approved', '_label', '_editor', '_updated'));

        if(isset($image)) {
            if(is_numeric($image)) {
                $this->setId($image);
                $this->init();
            } elseif(is_array($image)) {
                $this->init($image);
            }
        }
    }

    /**
     * Set summary
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function setSummary($summary) {
    require_once PHPWS_SOURCE_DIR . 'core/Text.php';

        if (is_string($summary)) {
            if (strlen($summary) > 0) {
                $this->_summary = PHPWS_Text::parseInput($summary, 'none');
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get summary
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListsummary() {
        require_once PHPWS_SOURCE_DIR . 'core/Text.php';
        return PHPWS_Text::parseOutput($this->_summary);
    }

    /**
     * Get filename
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListfilename() {
        return '<a href="images/wiki/' . $this->_filename . '">' . $this->_filename . '</a>';
    }

    /**
     * Get owner
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListowner() {
        return $this->_owner;
    }

    /**
     * Get type
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListtype() {
        return $this->_type;
    }

    /**
     * Get created date
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListcreated() {
        return $this->getCreated();
    }

    /**
     * Get size
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListsize() {
        if($this->_size < 1024) {
            // Display in bytes
            return number_format($this->_size, 2) . ' bytes';
        }
        else if($this->_size < pow(2, 20)) {
            // Display in kilobytes
            return number_format(round(($this->_size/1024),2), 2) . ' KB';
        }
        else {
            // Display in megabytes
            return number_format(round(($this->_size/1024)/1024,2), 2) . ' MB';
        }
    }

    /**
     * Get actions
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function getListactions() {
        $retVal = '<a href="images/wiki/' . $this->_filename . '">' . $_SESSION['translate']->it('View') . '</a>';
        $retVal .= ' | <a href="./index.php?module=wiki&amp;op=imagedelete&amp;id=' . $this->getId() . '">' . $_SESSION['translate']->it('Delete') . '</a>';

        return $retVal;
    }

    /**
     * Add image form
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function add() {
        require_once PHPWS_SOURCE_DIR . 'core/EZform.php';

        $form = new EZForm('image_upload');
        $form->add('module', 'hidden', 'wiki');
        $form->add('op', 'hidden', 'doimageupload');

        $form->add('filename', 'file');
        $form->setSize('filename', 50);

        $form->add('summary', 'text');
        $form->setSize('summary', 50);

        $form->add('save', 'submit', $_SESSION['translate']->it('Upload'));

        $tags = $form->getTemplate();
        $tags['FILENAME_LABEL'] = $_SESSION['translate']->it('Filename');
        $tags['SUMMARY_LABEL'] = $_SESSION['translate']->it('Summary');

        return $tags;
    }

    /**
     * Save
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function save() {
        if(!$this->setSummary($_POST['summary'])) {
            return $_SESSION['translate']->it('You need to supply a summary.');
        }

        if($_FILES['filename']['error'] == 0) {
            $name = str_replace(' ', '_', $_FILES['filename']['name']);
            $file = $GLOBALS['core']->home_dir . 'images/wiki/' . $name;

            if(is_file($file)) {
                $name = time() . '_' . str_replace(' ', '_', $_FILES['filename']['name']);
                $file = $GLOBALS['core']->home_dir . 'images/wiki/' . $name;
            }

            @move_uploaded_file($_FILES['filename']['tmp_name'], $file);
            if(is_file($file)) {
                chmod($file, 0664);
                include(PHPWS_SOURCE_DIR.'conf/allowedImageTypes.php');

                if(in_array($_FILES['filename']['type'], $allowedImageTypes)) {
                    $this->_filename = $name;
                    $this->_type = $_FILES['filename']['type'];
                    $this->_size = $_FILES['filename']['size'];
                } else {
                    @unlink($file);
                    return $_SESSION['translate']->it('The image uploaded was not an allowed image type.');
                }
            } else {
                return $_SESSION['translate']->it('There was a problem uploading the specified image.');
            }
        } else if($_FILES['filename']['error'] != 4) {
            return $_SESSION['translate']->it('The file uploaded exceeded the max size allowed.');
        } else {
            return $_SESSION['translate']->it('You need to specify a file to upload.');
        }

        $this->commit();
        return $_SESSION['translate']->it('Image Saved!');
    }

    /**
     * Delete
     *
     * @author Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
     */
    function delete() {
        if (!($_SESSION['PHPWS_WikiSettings']['allow_image_upload'] && $_SESSION['OBJ_user']->username) && !$_SESSION['OBJ_user']->allow_access('wiki', 'upload_images')) {
            $message = $_SESSION['translate']->it('Access was denied due to lack of proper permissions.');
            $error = new PHPWS_Error('wiki', 'PHPWS_WikiImage::delete()', $message, 'exit', 1);
            $error->message();
            return FALSE;
        }

        if (isset($_REQUEST['yes'])) {
            @unlink($GLOBALS['core']->home_dir . 'images/wiki/' . $this->_filename);
            $this->kill();
            return $_SESSION['translate']->it('Image deleted!');
        } else if (isset($_REQUEST['no'])) {
            return $_SESSION['translate']->it('Image was not deleted!');
        } else {
            $tags = array();
            $tags['MESSAGE'] = $_SESSION['translate']->it('Are you sure you want to delete this image?');

            $tags['YES'] = '<a href="index.php?module=wiki&amp;op=doimagedelete&amp;yes=1&amp;id=' . $this->getId() . '">' .
                $_SESSION['translate']->it('Yes') . '</a>';

            $tags['NO'] = '<a href="index.php?module=wiki&amp;op=doimagedelete&amp;no=1&amp;id=' . $this->getId() . '">' .
                $_SESSION['translate']->it('No') .'</a>';

            $tags['WIKIPAGE'] = '<img src="images/wiki/' . $this->_filename . '" alt="" />';

            return PHPWS_Template::processTemplate($tags, 'wiki', 'confirm.tpl');
        }
    }
}

?>
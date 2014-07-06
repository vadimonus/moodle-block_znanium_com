<?php

/**
 * Authentification on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
            'block_znanium_com/domain', 
            new lang_string('domain', 'block_znanium_com'), 
            '',
            '', 
            PARAM_RAW));
    
    $settings->add(new admin_setting_configtext(
            'block_znanium_com/secretkey', 
            new lang_string('secretkey', 'block_znanium_com'), 
            '',
            '', 
            PARAM_RAW));
   
    $settings->add(new admin_setting_configtext(
            'block_znanium_com/title', 
            new lang_string('title', 'block_znanium_com'), 
            '',
            new lang_string('defaulttitle', 'block_znanium_com'), 
            PARAM_RAW));
   
    $settings->add(new admin_setting_configtext(
            'block_znanium_com/link', 
            new lang_string('link', 'block_znanium_com'), 
            '',
            new lang_string('defaultlink', 'block_znanium_com'), 
            PARAM_RAW));
}



<?php

/**
 * Authentification on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_login();

$contextid = required_param('contextid',PARAM_INT);

$params = array(
    'contextid' => $contextid
);
$event = \block_znanium_com\event\link_used::create($params);
$event->trigger();

$visit = new stdClass();
$visit->time = time();
$visit->userid = $USER->id;
$visit->contextid = $contextid;
$DB->insert_record('block_znanium_com_visits', $visit);

$secretkey = get_config('block_znanium_com', 'secretkey');
$domain = get_config('block_znanium_com', 'domain');

$timestamp = date('YmdHis');
$signature = md5($USER->id . $secretkey . $timestamp);
$params = array(
    'domain' => $domain,
    'id' => $USER->id,
    'login' => $USER->username,
    'name' => $USER->firstname, 
    'patr' => '', 
    'lname' => $USER->lastname,
    'time' => $timestamp, 
    'sign' => $signature);
$url = new moodle_url('http://znanium.com/autosignon.php', $params);
redirect($url);

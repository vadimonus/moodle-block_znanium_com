<?php

/**
 * Authentification on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_login();
$context = context_system::instance();
require_capability('block/znanium_com:viewstats', $context);

$PAGE->set_url(new moodle_url('/blocks/znanium_com/statistics.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('statistics','block_znanium_com'));
$PAGE->set_heading(get_string('statistics','block_znanium_com'));
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();

$sql = "SELECT MIN(timecreated) AS timecreated 
        FROM {logstore_standard_log}
        WHERE eventname = :eventname";
$params = array(
        'eventname' => '\block_znanium_com\event\link_used');
$timefirst = $DB->get_field_sql($sql, $params);
if ($timefirst) {
    
    $table = new html_table();
    $table->width = 'auto';
    $table->head = array(get_string('month','block_znanium_com'), get_string('visits','block_znanium_com'));
    $table->align = array('center', 'center');
    
    $date = usergetdate(time());
    $year = $date['year'];
    $month = $date['mon'];
    
    do {
        $timestart = make_timestamp($year, $month, 1);
        $timeend = make_timestamp($year, $month + 1,  1);
        if ($timeend <= $timefirst) {
            break;
        }
        
        $monthname = userdate($timestart, '%B %Y');
        $sql = "SELECT COUNT(id) AS count_id 
            FROM {logstore_standard_log}
            WHERE eventname = :eventname AND timecreated >= :timestart AND timecreated < :timeend";
        $params = array(
            'eventname' => '\block_znanium_com\event\link_used',
            'timestart' => $timestart,
            'timeend' => $timeend);
        $count = $DB->get_field_sql($sql, $params);
        $table->data[] = array($monthname, $count);
        
        $month--; 
        if ($month == 0) {
            $month = 12;
            $year--;
        }
    } while (true);
    
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
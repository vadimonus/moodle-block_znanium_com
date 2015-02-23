<?php

/**
 * Authentification on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_znanium_com extends block_base {
    
    function init() {
        $this->title = get_string('pluginname', 'block_znanium_com');
    }
    
    function specialization() {
        $title = get_config('block_znanium_com', 'title');
        if ($title !== false) {
            $this->title = $title;
        } else {
            $this->title = get_string('defaulttitle', 'block_znanium_com');
        }
    }

    function applicable_formats() {
        return array('all' => true);
    }
    
    function instance_allow_multiple() {
        return true;
    }

    public function instance_can_be_docked() {
        return (!empty($this->title) && parent::instance_can_be_docked());
    }

    function has_config() {
        return true;
    }

    function get_content () {
        global $OUTPUT, $PAGE;
        
        $this->content = new stdClass;
        $this->content->footer = '';
        $text = get_config('block_znanium_com', 'link');
        if (!$text) {
            $text = get_string('defaultlink', 'block_znanium_com');
        }
        $url = new moodle_url('/blocks/znanium_com/redirect.php', array('contextid' => $PAGE->context->id));
        $link = new action_link($url, $text);
        $link->attributes = array('target' => '_blank');
        $this->content->text = $OUTPUT->render($link);
        return $this->content;
    }
}

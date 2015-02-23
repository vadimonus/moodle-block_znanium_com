<?php

/**
 * Authentification on znanium.com
 *
 * @package    block
 * @subpackage znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_znanium_com\event;

defined('MOODLE_INTERNAL') || die();

class link_used extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventlinkused', 'block_znanium_com');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' used link to authenticate on znanium.com.";
    }

}

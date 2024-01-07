<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Authentication on znanium.com
 *
 * @package    block_znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Main block class
 *
 * @package    block_znanium_com
 * @copyright  2014 Vadim Dvorovenko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_znanium_com extends block_base {

    /**
     * Standard block API function for initializing block instance.
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_znanium_com');
    }

    /**
     * This function is called on your subclass right after an instance is loaded
     * Use this function to act on instance data just after it's loaded and before anything else is done
     * For instance: if your block will have different title's depending on location (site, course, blog, etc)
     */
    public function specialization() {
        $title = get_config('block_znanium_com', 'title');
        if ($title !== false) {
            $this->title = $title;
        } else {
            $this->title = get_string('defaulttitle', 'block_znanium_com');
        }
    }

    /**
     * Which page types this block may appear on.
     *
     * @return array page-type prefix => true/false.
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Are you going to allow multiple instances of each block?
     * If yes, then it is assumed that the block WILL USE per-instance configuration
     * @return boolean
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Can be overridden by the block to prevent the block from being dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->title) && parent::instance_can_be_docked());
    }

    /**
     * Subclasses should override this and return true if the
     * subclass block has a settings.php file.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Parent class version of this function simply returns NULL
     * This should be implemented by the derived class to return
     * the content object.
     *
     * @return stdObject
     */
    public function get_content () {
        global $OUTPUT;

        $this->content = new stdClass;
        $this->content->footer = '';
        if (has_capability('block/znanium_com:use', $this->page->context)) {
            $text = get_config('block_znanium_com', 'link');
            if (!$text) {
                $text = get_string('defaultlink', 'block_znanium_com');
            }
            $url = new moodle_url('/blocks/znanium_com/redirect.php', ['contextid' => $this->page->context->id]);
            $link = new action_link($url, $text, null, ['target' => '_blank']);
            $this->content->text = html_writer::div($OUTPUT->render($link));
        }

        if (has_capability('block/znanium_com:viewstats', context_system::instance())) {
            $text = get_string('statistics', 'block_znanium_com');
            $url = new moodle_url('/blocks/znanium_com/statistics.php');
            $link = new action_link($url, $text);
            $this->content->text .= html_writer::div($OUTPUT->render($link));
        }
        return $this->content;
    }
}

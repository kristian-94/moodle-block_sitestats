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

use block_sitestats\output\main;

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block instances.
 *
 * @package   block_sitestats
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_sitestats extends block_base
{

    function init()
    {
        $this->title = get_string('pluginname', 'block_sitestats');
    }

    public function hide_header()
    {
        return true;
    }

    public function get_content()
    {
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $renderable = new main();
        $renderer = $this->page->get_renderer('block_sitestats');
        $this->content->text = $renderer->render($renderable);

        return $this->content;
    }

    public function has_config()
    {
        return true;
    }
}

        
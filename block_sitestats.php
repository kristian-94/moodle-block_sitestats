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


    function get_content()
    {

        global $DB;
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();

        $this->content->text .= '<div id="sitestats"';
        $this->content->text .= '<p>Total Active Users:</p>';

        // Retrieve the total number of active users
        $totalActiveUsers = $DB->count_records_sql("
            SELECT COUNT(DISTINCT u.id)
            FROM {user} u
            INNER JOIN {user_lastaccess} ul ON u.id = ul.userid
            WHERE u.deleted = 0 AND ul.timeaccess > :timeaccess
        ", ['timeaccess' => time() - 60]);

        $this->content->text .= '<p>' . $totalActiveUsers . '</p>';

        $this->content->text .= '<p>Total Enrolments:</p>';

        // Retrieve the total enrolments
        $totalEnrolments = $DB->count_records('enrol');

        $this->content->text .= '<p>' . $totalEnrolments . '</p>';

        $this->content->text .= '<p>Number of Courses:</p>';

        // Retrieve the number of courses
        $numberOfCourses = $DB->count_records('course');

        $this->content->text .= '<p>' . $numberOfCourses . '</p>';

        $this->content->text .= '</div>';


        $this->content->footer = '';
        return $this->content;


    }
}

        
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
 * Class containing data for site stats block.
 *
 * @package     block_sitestats
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sitestats\output;
defined('MOODLE_INTERNAL') || die();

use core_user\output\status_field;
use renderable;
use renderer_base;
use templatable;

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/completionlib.php');

/**
 * Class containing data for site stats block.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable
{

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output)
    {
        global $DB;
        $categories = get_config('block_sitestats', 'categorychoices');

        $sql = "SELECT c.id, c.fullname, COUNT(e.userid) AS enrolments
        FROM {course} c
        LEFT JOIN {enrol} en ON en.courseid = c.id
        LEFT JOIN {user_enrolments} e ON e.enrolid = en.id ";

        if ($categories) {
            $sql .= " WHERE c.category IN ($categories) ";
        }

        $sql .= " GROUP BY c.id, c.fullname
        ORDER BY enrolments DESC
        LIMIT 3";

        $topcourses = $DB->get_records_sql($sql);
        $totalActiveUsers = $DB->count_records_sql(" SELECT COUNT(DISTINCT u.id) FROM {user} u WHERE u.deleted = 0 AND u.username NOT LIKE 'tool_generator%'");

        $totalEnrolments = $DB->count_records('user_enrolments', ['status' => status_field::STATUS_ACTIVE]);
        $sql = "SELECT COUNT(c.id) FROM {course} c WHERE c.visible = 1";
        if ($categories) {
            $sql .= " AND c.category IN ($categories) ";
        }
        $numberOfCourses = $DB->count_records_sql($sql);

        return [
            'top_courses' => array_values($topcourses),
            'total_active_users' => $totalActiveUsers,
            'number_of_courses' => $numberOfCourses,
            'total_enrolments' => $totalEnrolments,
        ];
    }
}

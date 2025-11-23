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
 * Get site statistics
 *
 * @package   block_sitestats
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sitestats\external;

defined('MOODLE_INTERNAL') || die;

use core_user\output\status_field;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use context_system;
use moodle_url;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/course/lib.php');

trait get_site_stats {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_site_stats_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Get site statistics
     *
     * @return array site statistics
     */
    public static function get_site_stats() {
        global $DB, $USER;

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        $categories = get_config('block_sitestats', 'categorychoices');
        $topcourseslimit = (int)get_config('block_sitestats', 'topcourseslimit');

        // Get new courses.
        $newcoursessql = "SELECT c.id, c.fullname, c.timecreated, COUNT(DISTINCT e.userid) AS enrolments,
            cc.name AS category,
            MAX(CASE WHEN e.userid = :userid THEN 1 ELSE 0 END) AS is_enrolled
            FROM {course} c
            LEFT JOIN {enrol} en ON en.courseid = c.id
            JOIN {course_categories} cc ON cc.id = c.category
            LEFT JOIN {user_enrolments} e ON e.enrolid = en.id
            WHERE c.visible = 1
            GROUP BY c.id, c.fullname, c.timecreated, cc.name
            ORDER BY c.timecreated DESC LIMIT " . $topcourseslimit;

        $newcourses = $DB->get_records_sql($newcoursessql, ['userid' => $USER->id]);

        foreach ($newcourses as $course) {
            $coursecontext = \context_course::instance($course->id);
            $can_view_course = has_capability('moodle/course:view', $coursecontext) || $course->is_enrolled;
            $course->link = $can_view_course ?
                ((new moodle_url('/course/view.php', ['id' => $course->id]))->out(false)) :
                'https://calcupa.org/lms-course/index.html?moodle_course_id=' . $course->id;
            $course->track = $course->category;
        }

        // Get top courses by enrolments.
        $sql = "SELECT c.id, c.fullname, COUNT(e.userid) AS enrolments,
            cc.name AS category,
            MAX(CASE WHEN e.userid = :userid THEN 1 ELSE 0 END) AS is_enrolled
            FROM {course} c
            LEFT JOIN {enrol} en ON en.courseid = c.id
            JOIN {course_categories} cc ON cc.id = c.category
            LEFT JOIN {user_enrolments} e ON e.enrolid = en.id ";

        if ($categories) {
            $sql .= " WHERE c.visible = 1 AND c.fullname NOT LIKE 'Test course%'";
        }
        $sql .= " GROUP BY c.id, c.fullname, c.timecreated, cc.name
            ORDER BY enrolments DESC
            LIMIT " . $topcourseslimit;

        $topcourses = $DB->get_records_sql($sql, ['userid' => $USER->id]);

        foreach ($topcourses as $course) {
            $coursecontext = \context_course::instance($course->id);
            $can_view_course = has_capability('moodle/course:view', $coursecontext) || $course->is_enrolled;
            $course->link = $can_view_course ?
                ((new moodle_url('/course/view.php', ['id' => $course->id]))->out(false)) :
                'https://calcupa.org/lms-course/index.html?moodle_course_id=' . $course->id;
            $course->track = $course->category;
        }

        // Get totals.
        $totalActiveUsers = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT u.id) FROM {user} u WHERE u.deleted = 0 AND u.username NOT LIKE 'tool_generator%'"
        );

        $totalEnrolments = $DB->count_records('user_enrolments', ['status' => status_field::STATUS_ACTIVE]);

        $sql = "SELECT COUNT(c.id) FROM {course} c WHERE c.visible = 1";
        if ($categories) {
            $sql .= " AND c.category IN ($categories) ";
        }
        $numberOfCourses = $DB->count_records_sql($sql);

        $totalCompletions = $DB->count_records_select('course_completions', 'timecompleted > 0');

        return [
            'top_courses' => array_values($topcourses),
            'new_courses' => array_values($newcourses),
            'topcourseslimit' => $topcourseslimit,
            'total_active_users' => $totalActiveUsers,
            'number_of_courses' => $numberOfCourses,
            'total_enrolments' => $totalEnrolments,
            'total_completions' => $totalCompletions,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function get_site_stats_returns() {
        return new external_single_structure(
            array(
                'top_courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Course ID'),
                            'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                            'enrolments' => new external_value(PARAM_INT, 'Number of enrolments'),
                            'category' => new external_value(PARAM_TEXT, 'Category name'),
                            'is_enrolled' => new external_value(PARAM_INT, 'Is user enrolled'),
                            'link' => new external_value(PARAM_URL, 'Course link'),
                            'track' => new external_value(PARAM_TEXT, 'Course track/category'),
                        )
                    )
                ),
                'new_courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Course ID'),
                            'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                            'timecreated' => new external_value(PARAM_INT, 'Time created'),
                            'enrolments' => new external_value(PARAM_INT, 'Number of enrolments'),
                            'category' => new external_value(PARAM_TEXT, 'Category name'),
                            'is_enrolled' => new external_value(PARAM_INT, 'Is user enrolled'),
                            'link' => new external_value(PARAM_URL, 'Course link'),
                            'track' => new external_value(PARAM_TEXT, 'Course track/category'),
                        )
                    )
                ),
                'topcourseslimit' => new external_value(PARAM_INT, 'Top courses limit'),
                'total_active_users' => new external_value(PARAM_INT, 'Total active users'),
                'number_of_courses' => new external_value(PARAM_INT, 'Number of courses'),
                'total_enrolments' => new external_value(PARAM_INT, 'Total enrolments'),
                'total_completions' => new external_value(PARAM_INT, 'Total completions'),
            )
        );
    }
}

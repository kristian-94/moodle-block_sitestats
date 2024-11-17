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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    global $DB;
    $categories = $DB->get_records('course_categories', null, 'sortorder ASC', 'id, name');

    $options = [];
    foreach ($categories as $category) {
        $options[$category->id] = $category->name;
    }

    $settings->add(new admin_setting_configtext('block_sitestats/topcourseslimit',
            new lang_string('topcourseslimit', 'block_sitestats'),
            new lang_string('topcourseslimit_desc', 'block_sitestats'), 3, PARAM_INT)
    );

    $settings->add(new admin_setting_configmulticheckbox('block_sitestats/categorychoices',
            new lang_string('categorychoice', 'block_sitestats'),
            new lang_string('categorychoice_desc', 'block_sitestats'), null, $options)
    );
}
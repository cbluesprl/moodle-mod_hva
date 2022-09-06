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
 * @package     mod_hva
 * @author      Loïc Hannecart <lhannecart@cblue.be>
 * @copyright   2022 CBlue (https://www.cblue.be/)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $DB, $PAGE, $OUTPUT;

require_once $CFG->dirroot . '/local/hva/form.php';

$id = required_param('id', PARAM_INT);           // Course ID

// Ensure that the course specified is valid
$id = required_param('id', PARAM_INT);

if (!empty($id)) {
    if (!$course = $DB->get_record('course', ['id' => $id])) {
        print_error('invalidcourseid');
    }
} else {
    print_error('missingparameter');
}

require_course_login($course);

$PAGE->set_url('/mod/hva/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');

// Print the header.
$PAGE->set_title(format_string(get_string('modulename', 'hva')));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

echo $OUTPUT->footer();
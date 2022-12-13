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

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB, $PAGE, $OUTPUT;

// Course ID
$id = required_param('id', PARAM_INT);

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

require_course_login($course);

$PAGE->set_url('/mod/hva/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');

// Print the header.
$PAGE->set_title(format_string(get_string('modulename', 'hva')));
$PAGE->set_heading(format_string($course->fullname));

$hva_instances = get_coursemodules_in_course('hva', $course->id);

$table = new html_table();
$table->head = [
    get_string('name'),
    get_string('section'),
    get_string('description'),
    get_string('visible'),
];

$table->align = ['center', 'center'];
$table->data = [];

foreach ($hva_instances as $id => $hva_instance) {
    $hva = $DB->get_record('hva',['id' => $hva_instance->instance],'intro');
    $visible = $hva_instance->visible === '1' ? get_string('visible') : get_string('hidden');

    $table->data[] = new html_table_row(array(
        new html_table_cell($hva_instance->name),
        new html_table_cell($hva_instance->section),
        new html_table_cell($hva->intro),
        new html_table_cell($visible),
    ));
}

echo $OUTPUT->header();
echo html_writer::table($table);
echo $OUTPUT->footer();
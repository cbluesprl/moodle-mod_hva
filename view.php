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

require_once __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/completionlib.php';
require_once $CFG->dirroot . '/mod/hva/lib.php';
require_once $CFG->dirroot . '/mod/hva/classes/Pin.php';

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$hid = optional_param('hid', 0, PARAM_INT);  // HVA ID.

if (isset($hid) && $hid > 0) {  // Two ways to specify the module.
    $hva = $DB->get_record('hva', ['id' => $hid], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('hva', $hva->id, $hva->course, false, MUST_EXIST);
} else {
    $cm = get_coursemodule_from_id('hva', $id, 0, false, MUST_EXIST);
    $hva = $DB->get_record('hva', ['id' => $cm->instance], '*', MUST_EXIST);
}

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

$PAGE->set_cm($cm, $course); // Set's up global $COURSE.
$context = context_module::instance($cm->id);
$PAGE->set_context($context);

require_login($course, false, $cm);

$url = new moodle_url('/mod/hva/view.php', ['id' => $cm->id]);
$PAGE->set_url($url);


echo $OUTPUT->header();

if (is_object($hva) && isset($USER)) {
    try {
        $a = new StdClass();
        $a->delay = (Pin::$duration / 60);
        $a->pincode = Pin::generate($hva->id, $USER->id);
        echo html_writer::tag('div', get_string('pincode_message', 'hva', $a), ['class' => 'alert alert-warning']);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

echo $OUTPUT->footer();
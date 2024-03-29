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

require_once __DIR__ . '/../../../config.php';

global $CFG, $DB, $OUTPUT, $PAGE;

require_once $CFG->dirroot . '/mod/hva/test/curl.php';
require_once $CFG->dirroot . '/mod/hva/test/form.php';

$resp = '';

$url = new moodle_url('/mod/hva/test/index.php');

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_heading('Test plugin HVA');
$PAGE->set_url("/mod/hva/test/index.php");

echo $OUTPUT->header();
$form = new mod_hva_form($url);


$form->display();
if ($form->is_cancelled()) {
    redirect($url);
} elseif ($data = $form->get_data()) {
    if (isset($data->web_service)) {
        if ($data->web_service === 'get_info') {
            [$resp,$resp_json] = mod_hva_curl_get_info($data->pincode, $data->token);
            if ($resp === 'invalidrecord') {
                echo html_writer::tag('div', get_string('invalidpincode', 'mod_hva'), ['class' => 'alert alert-danger']);
            } else if ($resp === 'invalidtoken') {
                echo html_writer::tag('div', get_string('invalidtoken', 'mod_hva'), ['class' => 'alert alert-danger']);
            } else {
                echo html_writer::tag('div', get_string('result', 'mod_hva'), ['class' => 'alert alert-success']);
                echo html_writer::start_tag('div');
                echo html_writer::tag('p', get_string('studentName', 'mod_hva') . $resp->studentName);
                echo html_writer::tag('p', get_string('activityTitle', 'mod_hva') . $resp->activityTitle);
                echo html_writer::tag('p', get_string('score', 'mod_hva') . $resp->LMSTracking->score);
                echo html_writer::tag('p', get_string('completion', 'mod_hva') . $resp->LMSTracking->completion);
                echo html_writer::tag('p', get_string('hyperfictionTracking', 'mod_hva') . $resp->hyperfictionTracking);
                echo html_writer::tag('p', get_string('url', 'mod_hva'));
                echo html_writer::start_tag('button', ['class' => 'btn btn-link']);
                echo html_writer::tag('a', $resp->url, ['href' => $resp->url]);
                echo html_writer::end_tag('button');
                echo html_writer::end_tag('div');

                echo html_writer::tag('div', get_string('result_json', 'mod_hva'), ['class' => 'alert alert-success']);
                echo '<pre>'; var_dump(json_encode($resp_json)); echo '</pre>';
            }
        }
        if ($data->web_service === 'save_data') {
            $resp = mod_hva_curl_save_data($data->pincode, $data->score, $data->completion, $data->hyperfictionTracking, $data->token);
            if ($resp === 'invalidrecord') {
                echo html_writer::tag('div', get_string('invalidpincode', 'mod_hva'), ['class' => 'alert alert-danger']);
            } else if ($resp === 'invalidtoken') {
                echo html_writer::tag('div', get_string('invalidtoken', 'mod_hva'), ['class' => 'alert alert-danger']);
            } else {
                echo html_writer::tag('div', get_string('savedone', 'mod_hva'), ['class' => 'alert alert-success']);
                echo '<pre>'; var_dump(json_encode($resp)); echo '</pre>';
            }
        }
    }
}


echo $OUTPUT->footer();


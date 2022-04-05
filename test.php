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

global $CFG, $DB, $OUTPUT, $PAGE;

require_once $CFG->dirroot . '/mod/hva/curl.php';
require_once $CFG->dirroot . '/mod/hva/test_form.php';

$resp = '';

$url = new moodle_url('test.php');

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_heading('Curl test');
$PAGE->set_url("/mod/hva/test.php");

echo $OUTPUT->header();
$form = new mod_hva_form($url);
if ($form->is_cancelled()) {
    redirect($url);
} elseif ($data = $form->get_data()) {
    if (isset($data->web_service)) {
        if ($data->web_service === 'get_info') {
            $resp = curl_get_info($data->pincode, $data->token);
            var_dump($resp);die;
        }
        if ($data->web_service === 'get_zip') {
            $resp = curl_get_zip($data->pincode, $data->token);
            var_dump($resp);die;
        }
        if ($data->web_service === 'save_data') {
            $resp = curl_save_data($data->pincode, $data->score,$data->completion,$data->hyperfictionTracking, $data->token);
            var_dump($resp);die;
        }
    }
}

$form->display();


echo $OUTPUT->footer();


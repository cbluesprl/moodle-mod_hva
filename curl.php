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

/**
 * curl for test get_info webservice
 *
 * @param $pincode
 * @param $token
 * @return mixed|string
 */
function curl_get_info($pincode, $token)
{
    global $CFG;
    $curl = new curl();
    $url = $CFG->wwwroot . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=mod_hva_get_info&moodlewsrestformat=json';
    $params = 'pincode=' . $pincode;
    $url .= '&' . $params;
    $resp = json_decode($curl->get($url));
    $resp_json = json_decode($curl->get($url));

    if (!empty($resp)) {
        if (isset($resp->errorcode)) {
            return [$resp->errorcode, false];
        }
        if ($resp->LMSTracking->completion == 0 || $resp->LMSTracking->completion == 1) {
            $resp->LMSTracking->completion == 0 ? $resp->LMSTracking->completion = get_string('incompleted', 'mod_hva') : $resp->LMSTracking->completion = get_string('completed', 'mod_hva');
        } else {
            $resp->LMSTracking->completion == 3 ? $resp->LMSTracking->completion = get_string('failed', 'mod_hva') : $resp->LMSTracking->completion = get_string('passed', 'mod_hva');
        }
        return [$resp,$resp_json];
    } else {
        return 'data empty';
    }
}

/**
 * curl for test the save_data webservice and return only a string
 * that confirm if the wb work or not. Call curl get_info for check if
 * the new data has been correctly save
 *
 * @param $pincode
 * @param $score
 * @param $completion
 * @param $hyperfictionTracking
 * @param $token
 * @return mixed|string
 */
function curl_save_data($pincode, $score, $completion, $hyperfictionTracking, $token)
{
    global $CFG;

    if ($completion == 'incompleted'|| $completion == 'completed') {
        $completion == 'incompleted' ? $completion = 0 : $completion = 1;
    } else {
        $completion == 'failed' ? $completion = 3 : $completion = 2;
    }

    $curl = new curl();
    $url = $CFG->wwwroot . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=mod_hva_save_data&moodlewsrestformat=json';
    $LMSTracking = [
        'score' => $score,
        'completion' => $completion
    ];
    $params = [
        'pincode' => $pincode,
        'LMSTracking' => $LMSTracking,
        'hyperfictionTracking' => $hyperfictionTracking,
    ];
    $params = format_postdata_for_curlcall($params);

    $resp = json_decode($curl->post($url, $params));

    if (!empty($resp)) {
        if (isset($resp->errorcode)) {
            return $resp->errorcode;
        } if ($resp->status != "save succeeded") {
            $resp->errorcode = "invalidrecord";
            return $resp->errorcode;
        }
        return $resp;
    } else {
        return 'error ';
    }
}

//dd315c54548c8ef9b1238b11111b27c3


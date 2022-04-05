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



function curl_get_info($pincode, $token){
    global $USER;
    $curl = new curl();
    $url = 'http://grtgaz.local73/webservice/rest/server.php?wstoken='.$token.'&wsfunction=mod_hva_get_info&moodlewsrestformat=json';
    $params = 'pincode='.$pincode;
    $url .= '&'. $params;
    $resp = $curl->get($url);


    if (!empty($resp)) {
        $data = json_decode($resp);
        if (!empty($data)) {
            return $data;
        } else {
            return 'data empty';
        }
    }

    return 'error';
}

function curl_get_zip($pincode, $token){
    $curl = new curl();
    $url ='http://grtgaz.local73/webservice/rest/server.php?wstoken='.$token.'&wsfunction=mod_hva_get_zip&moodlewsrestformat=json';
    $params = 'pincode='.$pincode;
    $url .= '&'. $params;
    $resp = $curl->get($url);

    if (!empty($resp)) {
        return $resp;
    } else {
        return 'data empty';
    }

    return 'error web service';
}



function curl_save_data($pincode, $score,$completion,$hyperfictionTracking, $token){
    $curl = new curl();
    $url = 'http://grtgaz.local73/webservice/rest/server.php?wstoken='.$token.'&wsfunction=mod_hva_save_data&moodlewsrestformat=json';
    $LMSTracking =[
        'score' => $score,
        'completion' => $completion
    ];
    $params = [
        'pincode' => $pincode,
        'LMSTracking' => $LMSTracking,
        'hyperfictionTracking' => $hyperfictionTracking,
    ];
    $params = format_postdata_for_curlcall($params);
    // $params = json_encode($params);
    $resp = $curl->post($url, $params);
    if (!empty($resp)) {
        $data = json_decode($resp);
        if (!empty($data)) {
            return $data;
        } else {
            return 'data empty';
        }
    }

    return 'error';
}




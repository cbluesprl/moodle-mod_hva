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

$string['pluginname'] = 'Hyperfiction VR Activity';
$string['activityTitle'] = 'Name of the activity: ';
$string['completed'] = 'completed';
$string['completion'] = 'Completion of the student : ';
$string['completion_form'] = 'Completion of the student (incompleted,completed,passed,failed) : ';
$string['failed'] = 'fail';
$string['hva:addinstance'] = 'Add a new HVA activity';
$string['hva:test'] = 'Give acces to test page of hva plugin';
$string['hva:view'] = 'View the HVA activity';
$string['hvaintro'] = 'HVA introduction';
$string['hvaname'] = 'Activity\'s name';
$string['hyperfictionTracking'] = 'Tracking of the student : ';
$string['incompleted'] = 'incompleted';
$string['invalidpincode'] = 'The pin code you entered is invalid or does not exist. The pin code is displayed on the previous page and consists of 4 digits.';
$string['invalidtoken'] = 'The token is invalid';
$string['linkzip'] = 'Link to download the zip : ';
$string['metadata'] = 'Metadata file';
$string['modulename'] = 'Hyperfiction VR Activity';
$string['modulenameplural'] = 'HVA';
$string['pagetest'] = 'Test page';
$string['passed'] = 'pass';
$string['pincode'] = 'Pin code : ';
$string['pincode_message'] = 'You have 120 minutes to put on the virtual reality headset, launch the application and enter the following code: ';
$string['pluginadministration'] = 'Plugin administration';
$string['result'] = 'The web service works, here is the result : ';
$string['result_json'] = 'The json that the webservice returns : ';
$string['savedone'] = 'The save is done correctly. You can run the get_info web service to see your changes';
$string['score'] = 'Student\'s score: ';
$string['studentName'] = 'Student\'s name: ';
$string['token'] = 'token : ';
$string['tokennotset'] = 'Warning! It is necessary to parameterize the token by the page External services and Manage tokens';
$string['url'] = 'Url : ';
$string['webservice'] = 'Webservice';
$string['zipfile'] = 'Zip file';

$string['privacy:metadata:mod_hva_subs'] = 'Information about the data to individual hva activity. This includes when a user has a score and a status of a VR activity.';
$string['privacy:metadata:mod_hva_subs:userid'] = 'The user\'s ID with the results of their VR activity.';
$string['privacy:metadata:mod_hva_subs:hvaid'] = 'The ID of the hva that was subscribed to.';
$string['privacy:metadata:mod_hva_subs:status'] = 'Tha status of completion of the VR activity';
$string['privacy:metadata:mod_hva_subs:score'] = 'The score of the VR activity';
$string['privacy:metadata:mod_hva_api'] = 'In order to integrate with a remote API service, user data needs to be exchanged with that service';
$string['privacy:metadata:mod_hva_api:userid'] = 'The userid is sent from Moodle to allow you to participate to the VR activity';
$string['privacy:metadata:mod_hva_api:score'] = 'Transfer the user\'s score for the VR activity';
$string['privacy:metadata:mod_hva_api:status'] = 'Transfer the status\'s score for the VR activity';
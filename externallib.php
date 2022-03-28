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

class mod_hva_external extends external_api {

    private static $module;

    protected static function get_module()
    {
        global $DB;
        if (empty(self::$module)) {
            self::$module = $DB->get_record('modules', ['name' => 'hva']);
        }
        return self::$module;
    }


    public static function get_info_parameters()  {
        return new external_function_parameters(
            array(
                'pincode' => new external_value(PARAM_INT,'Code pin')
            )
        );
    }

    public static function get_info($pincode) {
        global $CFG;
        require_once __DIR__ . '/../../config.php';
        require_once $CFG->dirroot . '/mod/hva/classes/PinHva.php';
        require_once $CFG->dirroot . '/mod/hva/classes/HvaData.php';

        $params = self::validate_parameters(self::get_info_parameters(), array('pincode' => $pincode));

        //    - donne le zip,
        //    - donne les info de l'utilisateur (nom/prénom)
        //    - donne le statuts de l'activité ou le définir
        //    - donne le tracking de l'utilisateur

        if (!isset($params) || empty($params) || !PinHva::is_valid($params)) {
            if (!empty($object->error)) {
                $msg = "HTTP/1.0 " . $object->error;
            } else {
                $msg = "HTTP/1.0 403";
            }
            header($msg);
            if (isset($object->message)) {
                echo $object->message;
            }
        }

        $hvaData = HvaData::get_from_pin($params);
        PinHva::update($pincode);

        return $hvaData->output();

    }

    public function get_info_returns() {
        return new external_single_structure(
            array(
                'studentId' => new external_value(PARAM_INT, 'name of user'),
                'studentName' => new external_value(PARAM_TEXT, 'name of user'),
                'activityTitle' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                'LMSTracking' => new external_single_structure(
                    array(
                        'score' => new external_value(PARAM_INT,'score of activity'),
                        'completion' => new external_value(PARAM_TEXT,'completion of activity')
                    )
                ),
                'hyperfictionTracking' => new external_value(PARAM_RAW, 'metadata'),
                'zipfile' => new external_value(PARAM_TEXT, 'base64'),
            ));
        //describes return values => json
    }


    public static function save_data_parameters() {
        return new external_function_parameters(
            array(
                'pincode' => new external_value(PARAM_INT,'Code pin'),
                'LMSTracking' => new external_single_structure(
                    array(
                        'score' => new external_value(PARAM_INT,'score of activity'),
                        'completion' => new external_value(PARAM_TEXT,'completion of activity')
                    )
                ),
                'hyperfictionTracking' => new external_external_value(PARAM_RAW,'save of user')
            )
        );
    }

    public function save_data() {
        global $CFG;
        require_once __DIR__ . '/../../config.php';
        require_once $CFG->dirroot . '/mod/hva/classes/PinHva.php';
        require_once $CFG->dirroot . '/mod/hva/classes/HvaData.php';

        $params = self::validate_parameters(self::get_data_parameters(), array('pincode' => $pincode, 'LMSTracking' => $LMSTracking, 'hyperfictionTracking' => $hyperfictionTracking));
        // récup le pincode
        // save les infos de l'user
        // save le status de l'activité
        // save le tracking sous format json
        try {
            $infos = new stdClass();
            $infos->LMSTracking = $LMSTracking;
            $infos->hyperficitonTracking = $hyperfictionTracking;

            $hyperfictionData = HyperfictionData::get_from_pin($pincode);
            Pin::update($pincode);
            $hyperfictionData->update_tracking($infos);
        } catch (Exception $e) {
            ResponseManager::send(false);
        }
    }

    public function save_data_return() {
        return 'save passed';
    }

}
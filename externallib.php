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
class mod_hva_external extends external_api
{

    private static $module;

    protected static function get_module()
    {
        global $DB;
        if (empty(self::$module)) {
            self::$module = $DB->get_record('modules', ['name' => 'hva']);
        }
        return self::$module;
    }

    public static function get_info_parameters()
    {
        return new external_function_parameters(
            [
                'pincode' => new external_value(PARAM_INT, 'Code pin')
            ]
        );
    }

    /**
     * get info user by the pincode
     *
     * @param $pincode
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_info($pincode)
    {
        global $CFG;
        require_once __DIR__ . '/../../config.php';
        require_once $CFG->dirroot . '/mod/hva/classes/PinHva.php';
        require_once $CFG->dirroot . '/mod/hva/classes/HvaData.php';
        require_once $CFG->dirroot . '/mod/hva/classes/Zipfile.php';

        $params = self::validate_parameters(self::get_info_parameters(), ['pincode' => $pincode]);

        //create object with all data by pincode
        $hvaData = HvaData::get_from_pin($params);
        //update the pincode time
        PinHva::update($pincode);

        $object = $hvaData->output();

        $object->url = Zipfile::get_zipfile_from_pincode($hvaData)->url;

        return $object;
    }

    public static function get_info_returns()
    {
        return new external_single_structure(
            [
                'studentId' => new external_value(PARAM_INT, 'name of user'),
                'studentName' => new external_value(PARAM_TEXT, 'name of user'),
                'activityTitle' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                'LMSTracking' => new external_single_structure(
                    [
                        'score' => new external_value(PARAM_INT, 'score of activity'),
                        'completion' => new external_value(PARAM_TEXT, 'completion of activity')
                    ]
                ),
                'hyperfictionTracking' => new external_value(PARAM_RAW, 'metadata'),
                'url' => new external_value(PARAM_URL, 'link for download the zip file')
            ]);
    }


    public static function save_data_parameters()
    {
        return new external_function_parameters(
            [
                'pincode' => new external_value(PARAM_INT, 'Code pin'),
                'LMSTracking' => new external_single_structure(
                    [
                        'score' => new external_value(PARAM_INT, 'score of activity'),
                        'completion' => new external_value(PARAM_TEXT, 'completion of activity')
                    ]
                ),
                'hyperfictionTracking' => new external_value(PARAM_RAW, 'save of user')
            ]
        );
    }

    /**
     * Save activity of user
     *
     * @param $pincode
     * @param $LMSTracking
     * @param $hyperfictionTracking
     * @return stdClass
     * @throws invalid_parameter_exception
     */
    public static function save_data($pincode, $LMSTracking, $hyperfictionTracking)
    {
        global $CFG;
        require_once __DIR__ . '/../../config.php';
        require_once $CFG->dirroot . '/mod/hva/classes/PinHva.php';
        require_once $CFG->dirroot . '/mod/hva/classes/HvaData.php';


        $params = self::validate_parameters(self::save_data_parameters(), ['pincode' => $pincode, 'LMSTracking' => $LMSTracking, 'hyperfictionTracking' => $hyperfictionTracking]);
        $message = new stdClass();

        try {
            $infos = new stdClass();
            $infos->LMSTracking = $params['LMSTracking'];
            $infos->hyperfictionTracking = $params['hyperfictionTracking'];
            $hvaData = HvaData::get_from_pin($params);
            PinHva::update($params['pincode']);
            $hvaData->update_tracking($infos);
            $message->status = "save succeeded";
            return $message;
        } catch (Exception $e) {
            $message->status = $e->getMessage();
            return $message;
        }
    }

    public static function save_data_returns()
    {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_TEXT, 'status of this wb')
            ]);
    }
}
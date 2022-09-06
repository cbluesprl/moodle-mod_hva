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

namespace mod_hva;

require_once __DIR__ . '/../../../config.php';
global $CFG;
require_once $CFG->dirroot . '/mod/hva/classes/PinHva.php';
require_once $CFG->dirroot . '/mod/hva/classes/LMSTrackingHva.php';
require_once $CFG->dirroot . '/mod/hva/classes/HVA.php';

class HvaData
{
    public static $table = 'hva_tracking';

    private $id;
    private $hva;
    private $user;
    private $LMSTracking;
    private $hyperfictionTracking;

    /**
     * HyperfictionData constructor.
     * @param $object
     */
    private function __construct($object)
    {
        $this->id = isset($object->id) ? $object->id : null;
        $this->hva = $object->hva;
        $this->user = $object->user;
        $this->LMSTracking = $object->LMSTracking;
        $this->id = $object->id;

        $file = $this->get_tracking_file($this->hva->cmid, $object->id);

        if (is_a($file, stored_file::class)) {
            $this->hyperfictionTracking = $file->get_content();
        } else {
            $this->hyperfictionTracking = '';
        }
    }

    /**
     * magic getter
     *
     * @param $name
     * @return mixed
     * @throws coding_exception
     */
    public function __get($name)
    {
        if (property_exists(self::class, $name)) {
            return $this->$name;
        } else {
            throw new coding_exception("property $name does not exists");
        }
    }

    /**
     * The $infos object must contains 3 informations :
     *  - the activity's score (LMSTracking->score)
     *  - the activity's completion (LMSTracking->completion)
     *  - some json data (hyperfictionTracking)
     *
     * Here the database tracking will be updated or created
     * Then the file will be updated or created
     *
     * @param $infos
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function update_tracking($infos)
    {
        global $DB, $CFG;
        require_once $CFG->dirroot .'/lib/gradelib.php';


        $score = isset($infos->LMSTracking['score']) ? $infos->LMSTracking['score']: $this->LMSTracking->score;
        $completion = isset($infos->LMSTracking['completion']) ? $infos->LMSTracking['completion'] : $this->LMSTracking->completion;
        $tracking_file = isset($infos->hyperfictionTracking) ? $infos->hyperfictionTracking : $this->hyperfictionTracking;

        // first manage the database tracking
        $tracking = new LMSTrackingHva($score, $completion);
        $o = new stdClass();
        $o->hvaid = $this->hva->id;
        $o->userid = $this->user->id;
        $o->status = $tracking->completion;
        $o->score = $tracking->score;
        $o->timemodified = time();

        $cm = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE instance = {$this->hva->id} AND module = (SELECT id FROM {modules} WHERE name LIKE 'hva')");
        $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id = $cm->course");

        if ($this->id === null) { // if id is null, that means that no tracking is currently stored in the database
            $o->timecreated = $o->timemodified;
            $o->id = $DB->insert_record(self::$table, $o);
        } else { // the tracking already exist in database, that means we have to update it
            $o->id = $this->id;
            $DB->update_record(self::$table, $o);
        }

        // Handle moodle grade book
        $grade = new stdClass();
        $grade->userid = $this->user->id;
        $grade->rawgrade = $o->score;
        $grade->finalgrade = $o->score;

        $itemdetails = [];
        $itemdetails['grademin'] = 0;
        $itemdetails['grademax'] = 100;
        grade_update('mod_hva', $course->id, 'mod', 'hva', $cm->instance, 0, $grade, $itemdetails);

        // Update Moodle Completion
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);
        if ($completion->is_enabled($cm)) {
             $completion->update_state($cm, $tracking->completion, $this->user->id);
            $current = $completion->get_data($cm, false, $this->user->id);
            $current->completionstate = $tracking->completion;
            $current->timemodified = time();
            $current->overrideby = null;
            $completion->internal_set_data($cm, $current);
        }

        // then manage the json file
        // if the file exist, we delete it
        $file = $this->get_tracking_file($this->hva->cmid, $o->id);
        if (is_a($file, \stored_file::class)) {
            $file->delete();
        }

        $fs = get_file_storage();
        $fileinfo = [
            'contextid' => \context_module::instance($this->hva->cmid)->id,
            'component' => 'mod_hva',
            'filearea' => 'hva_tracking',
            'itemid' => $o->id,
            'filepath' => '/',
            'filename' => 'hva-tracking-' . $o->id . '.json'
        ];


        // then we create a new one with the given content
        $fs->create_file_from_string($fileinfo, $tracking_file);
    }

    /**
     * Main purpose : JSON display
     *
     * @param $mode
     * @return stdClass
     * @throws coding_exception
     */
    public function output()
    {
        $output = new \stdClass();

        $output->studentId = $this->user->id;
        $output->studentName = $this->user->firstname . ' ' . $this->user->lastname;
        $output->activityTitle = $this->hva->name;
        $output->LMSTracking = $this->LMSTracking;
        $output->hyperfictionInitialize = $this->hva->metadata;
        $output->hyperfictionTracking = $this->hyperfictionTracking;

        return $output;
    }

    /**
     * Get the tracking informations from the pin code
     *
     * @param $pincode
     * @return Hvadata
     * @throws dml_exception
     */
    public static function get_from_pin($pincode)
    {
        $pin = PinHva::get_from_pin($pincode);

        return self::get_from_user_and_hva($pin->user, $pin->hva);
    }

    /**
     * Get the tracking informations from the userid & the activity (hva) id
     *
     * @param $userid
     * @param $hvaid
     * @return HvaData
     * @throws dml_exception
     */
    public static function get_from_userid_and_hvaid($userid, $hvaid)
    {
        global $DB;

        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $hva = HVA::get($hvaid);

        return self::get_from_user_and_hva($user, $hva);
    }

    /**
     * Get the tracking information from the user & the activity (hva)
     *
     * /!\ Warning /!\
     * This function does not check if the parameter are valid.
     * If you are unsure of what you need to pass, prefer the use of get_from_userid_and_hvaid()
     *
     * @param $user
     * @param $hva
     * @return HvaData
     * @throws dml_exception
     */
    public static function get_from_user_and_hva($user, $hva)
    {
        global $DB;
        $object = new \stdClass();
        $object->user = $user;
        $object->hva = $hva;
        $tracking = $DB->get_record(self::$table, ['userid' => $object->user->id, 'hvaid' => $object->hva->id]);
        if ($tracking !== false) {
            $tracking->score = empty($tracking->score) ? 0 : $tracking->score;
            $tracking->completion = empty($tracking->status) ? 0 : $tracking->status;
        } else {
            $tracking = new \stdClass();
            $tracking->score = 0;
            $tracking->completion = 0;
            $tracking->id = null;
        }
        $object->LMSTracking = [
            'score' => $tracking->score,
            'completion' => $tracking->completion];
        $object->id = $tracking->id;

        return new HvaData($object);
    }

    /**
     * Get user tracking for the given hva activity
     *
     * If the user have the role 'teacher', it will fetch every user's tracking for the given hva activity
     *
     * @param $user
     * @param $hva
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function get_informations($user, $hva)
    {
        global $DB;

        // the course context
        $course_context = context_course::instance($hva->course->id);

        // create the information's container
        $tab = [];

        // check if user must get the tracking of every user in the course or just his own
        $can_see_other = false;
        foreach (['trainer', 'manager', 'coursecreator', 'editingteacher', 'teacher'] as $role) {
            $role = $DB->get_record('role', ['shortname' => $role], '*', MUST_EXIST);
            if (user_has_role_assignment($user->id, $role->id, $course_context->id)) {
                $can_see_other = true;
                break;
            }
        }

        if ($can_see_other || is_siteadmin()) {
            // get all user in course
            $users = enrol_get_course_users($hva->course->id);

            // get all informations from these users
            foreach ($users as $u) {
                $tab[] = self::get_from_user_and_hva($u, $hva)->output('info');
            }
        } else {
            $tab[] = self::get_from_user_and_hva($user, $hva)->output('info');
        }

        return $tab;
    }

    /**
     * @param $cmid
     * @param $fileid
     * @return bool|stdClass|stored_file
     * @throws dml_exception
     */
    private function get_tracking_file($cmid, $fileid)
    {
        if (empty($fileid) || $fileid == false) {
            return new \stdClass();
        }

        global $DB;
        $context = \context_module::instance($cmid);
        $file_params = [
            'contextid' => $context->id,
            'component' => 'mod_hva',
            'filearea' => 'hva_tracking',
            'itemid' => $fileid,
            'filepath' => '/',
            'filename' => 'hva-tracking-' . $fileid . '.json'
        ];

        if (!$DB->record_exists('files', $file_params)) {
            return new stdClass();
        }

        $fs = get_file_storage();
        $file = $fs->get_file(
            $file_params['contextid'],
            $file_params['component'],
            $file_params['filearea'],
            $file_params['itemid'],
            $file_params['filepath'],
            $file_params['filename']
        );

        return $file;
    }

    /**
     * @param $cmid
     * @return bool|stored_file|null
     * @throws dml_exception
     */
    private function get_zipfile_from_cmid($cmid)
    {
        global $DB;

        $fs = get_file_storage();
        $context = \context_module::instance($cmid);
        $file_info = $DB->get_record_sql(
            "SELECT *
            FROM {files}
            WHERE contextid = :contextid AND component = 'mod_hva' AND itemid = '1' AND filepath = '/' AND filename != '.'",
            ['contextid' => $context->id]
        );
        if ($file_info !== false) {
            return $fs->get_file($context->id, 'mod_hva', 'zipfile', 1, '/', $file_info->filename);
        } else {
            return null;
        }
    }
}


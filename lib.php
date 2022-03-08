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
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 * @return bool
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function hva_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = [])
{
    require_login();
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_hva/files/" . $relativepath;
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    return send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * @param object $data
 * @return bool|int
 * @throws dml_exception
 */
function hva_add_instance($data)
{
    global $DB;

    $activity = new StdClass;
    $activity->course = $data->course;
    $activity->name = $data->name;
    $activity->timecreated = time();
    $activity->timemodified = $activity->timecreated;
    $activity->id = $DB->insert_record('hva', $activity, true);

//    if (isset($data->metadatafile)) {
//        $cmid = $data->coursemodule;
//        $context = context_module::instance($cmid);
//        file_save_draft_area_files(
//            $data->metadatafile,
//            $context->id,
//            'mod_hva',
//            'metadatafile',
//            0
//        );
//    }

    return $activity->id;
}

/**
 * @param object $data
 * @return bool
 * @throws dml_exception
 */
function hva_update_instance($data)
{
    global $DB;

    if (!isset($data->update)) {
        return false;
    }

    $cm = $DB->get_record('course_modules', ['id' => $data->update]);

    $hva = $DB->get_record('hva', ['id' => $cm->instance]);

    $activity = new StdClass;
    $activity->id = $hva->id;
    $activity->course = $data->course;
    $activity->name = $data->name;
    $activity->timecreated = $hva->timecreated;
    $activity->timemodified = time();
    $DB->update_record('hva', $activity);

//    if (isset($data->metadatafile)) {
//        $context = context_module::instance($cm->id);
//        file_save_draft_area_files(
//            $data->metadatafile,
//            $context->id,
//            'mod_hva',
//            'metadatafile',
//            0
//        );
//    }

    return true;
}

/**
 * @param int $id
 * @return bool
 * @throws dml_exception
 */
function hva_delete_instance($id)
{
    global $DB;

    if (!$hva = $DB->get_record('hva', ['id' => $id])) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('hva', ['id' => $id]);
    $DB->delete_records('hva_pincode', ['hva' => $id]);

    grade_update('mod/hva', $hva->course, 'mod', 'hva', $id, 0, null, ['deleted' => 1]);

    return true;
}


/**
 * @param int $id
 * @return mixed
 * @throws Exception
 */
function hva_get_instance($id)
{
    global $DB;
    try {
        return $DB->get_record('hva', ['id' => $id], '*', MUST_EXIST);
    } catch (Exception $e) {
        throw new Exception('This hva instance does not exist!');
    }
}

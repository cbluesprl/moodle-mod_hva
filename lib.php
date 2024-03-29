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
 * @param $context
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
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'zipfile' && $filearea !== 'metadata') {
        return false;
    }

    if (defined('AJAX_SCRIPT') == false ) {
        require_login($course, true, $cm);
        if (!has_capability('mod/hva:view', $context)) {
            return false;
        }
    }


    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_hva', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
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

    if (isset($data->metadatafile)) {
        $cmid = $data->coursemodule;
        $context = context_module::instance($cmid);
        file_save_draft_area_files(
            $data->metadatafile,
            $context->id,
            'mod_hva',
            'metadata',
            0
        );
    }

    if (isset($data->zipfile)) {
        $cmid = $data->coursemodule;
        $context = context_module::instance($cmid);
        file_save_draft_area_files(
            $data->zipfile,
            $context->id,
            'mod_hva',
            'zipfile',
            1
        );
    }

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
    $activity->intro = $data->intro;
    $activity->introformat = $data->introformat;
    $activity->timecreated = $hva->timecreated;
    $activity->timemodified = time();
    $DB->update_record('hva', $activity);

    if (isset($data->metadatafile)) {
        $context = context_module::instance($cm->id);
        file_save_draft_area_files(
            $data->metadatafile,
            $context->id,
            'mod_hva',
            'metadata',
            0
        );
    }

    if (isset($data->zipfile)) {
        $context = context_module::instance($cm->id);
        file_save_draft_area_files(
            $data->zipfile,
            $context->id,
            'mod_hva',
            'zipfile',
            1
        );
    }

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
    if ($DB->count_records('hva_pincode',['hvaid' => $id]) > 0) {
        $DB->delete_records('hva_pincode', ['hvaid' => $id]);
    }

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

function hva_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}
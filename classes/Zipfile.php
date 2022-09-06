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

class Zipfile
{
    /**
     * return url for download the zipfile
     *
     * @param $cmid
     * @return string
     * @throws dml_exception
     */
    static function get_zipfile_from_pincode($hvaData)
    {
        global $DB;

        $cmid = HVA::get_cmid_from_hvaid($hvaData->hva->id);
        $object = new stdClass();
        $fs = get_file_storage();
        $context = context_module::instance($cmid);


        $file_info = $DB->get_record_sql(
            "SELECT *
                FROM {files}
                WHERE contextid = :contextid AND component = 'mod_hva' AND itemid = '1' AND filepath = '/' AND filename != '.' AND filearea = 'zipfile'",
            ['contextid' => $context->id]
        );

        if ($file_info !== false) {
            $file = $fs->get_file($context->id, 'mod_hva', 'zipfile', 1, '/', $file_info->filename);
            $url = moodle_url::make_webservice_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
            $object = new stdClass();
            $object->url = $url->out() . '?token=';
            return  $object;
        } else {
            return 'empty file';
        }
    }
}
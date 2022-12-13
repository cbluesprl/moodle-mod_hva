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

use coding_exception;
use context_module;

class HVA
{
    public static $table = 'hva';
    private $id;
    private $course;
    private $cmid;
    private $name;
    private $timecreated;
    private $timemodified;

    /**
     * HVA constructor.
     *
     * @param $object
     * @throws dml_exception
     */
    private function __construct($object)
    {
        global $DB;

        $this->id = $object->id;
        $this->course = $DB->get_record('course', ['id' => $object->course], '*', MUST_EXIST);
        $this->name = $object->name;
        $this->timecreated = $object->timecreated;
        $this->timemodified = $object->timemodified;
        $this->cmid = $this->get_cmid_from_hvaid($this->id);

        $metadata = $this->get_file_from_cmid($this->cmid);

        if ($metadata !== null) {
            $this->metadata = json_decode($metadata->get_content());
        } else {
            $this->metadata = '';
        }
    }

    /**
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
     * @param $id
     * @return HVA
     * @throws dml_exception
     */
    public static function get($id)
    {
        global $DB;

        $o = $DB->get_record(self::$table, ['id' => $id], '*', MUST_EXIST);

        return new HVA($o);
    }

    /**
     * @param $cmid
     * @return HVA
     * @throws dml_exception
     */
    public static function get_from_scorm_cmid($cmid)
    {
        global $DB;

        $sql = "
            SELECT a.id
            FROM {course_modules} cm_s
            JOIN {scorm} s ON s.id = cm_s.instance
            JOIN {course_modules} cm_a ON cm_a.course = cm_s.course AND cm_a.module = (SELECT id FROM {modules} WHERE name = 'hva')
            JOIN {hva} a ON a.id = cm_a.instance
            WHERE cm_s.id = :cmid AND cm_a.deletioninprogress = 0 AND cm_s.module = (SELECT id FROM {modules} WHERE name = 'scorm')
        ";

        $params = ['cmid' => $cmid];
        $instance = $DB->get_record_sql($sql, $params, MUST_EXIST);

        return self::get($instance->id);
    }

    /**
     * @param $hvaid
     * @return mixed
     * @throws dml_exception
     */
    static function get_cmid_from_hvaid($hvaid)
    {
        global $DB;

        $r = $DB->get_record_sql(
            "SELECT cm.id as cmid
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module AND m.name = 'hva'
            JOIN {hva} a ON a.id = cm.instance
            WHERE a.id = :hvaid",
            ['hvaid' => $hvaid]
        );

        return $r->cmid;
    }

    /**
     * @param $cmid
     * @return bool|stored_file|null
     * @throws dml_exception
     */
    private function get_file_from_cmid($cmid)
    {
        global $DB;

        $fs = get_file_storage();
        $context = context_module::instance($cmid);
        $file_info = $DB->get_record_sql(
            "SELECT *
            FROM {files}
            WHERE contextid = :contextid AND component = 'mod_hva' AND itemid = '0' AND filepath = '/' AND filename != '.'",
            ['contextid' => $context->id]
        );
        if ($file_info !== false) {
            return $fs->get_file($context->id, 'mod_hva', 'metadata', 0, '/', $file_info->filename);
        } else {
            return null;
        }
    }
}
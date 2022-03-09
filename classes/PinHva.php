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

require_once __DIR__ . '/../../../config.php';
require_once $CFG->dirroot . '/mod/hva/classes/HVA.php';

class PinHva
{
    public static $table = 'hva_pincode';
    public static $duration = 7200; // 2h

    private $id;
    private $pincode;
    private $hva;
    private $user;
    private $timecreated;
    private $timemodified;

    /**
     * Pin constructor.
     *
     * @param $object
     * @throws dml_exception
     */
    private function __construct($object)
    {
        global $DB;
        $this->id = $object->id;
        $this->pincode = $object->pincode;
        $this->hva = HVA::get($object->hvaid);
        $this->user = $DB->get_record('user', ['id' => $object->userid]);
        $this->timecreated = $object->timecreated;
        $this->timemodified = $object->timemodified;
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
     * @param $pin
     * @return Pin
     * @throws dml_exception
     */
    public static function get_from_pin($pin)
    {
        global $DB;

        $record = $DB->get_record(self::$table, ['pincode' => $pin['pincode']]);

        return new PinHva($record);
    }

    /**
     * Return true if pin code given exists and has been modified in the last 2 hours.
     * Return false otherwise
     *
     * @param $pin
     * @return bool
     * @throws dml_exception
     */
    public static function is_valid($pin)
    {
        global $DB;

        $timediff = time() - self::$duration;

        return $DB->record_exists_sql(
            "SELECT *
            FROM {" . self::$table . "}
            WHERE pincode = :pincode AND timemodified >= :timediff",
            ['pincode' => $pin, 'timediff' => $timediff]
        );
    }

    /**
     * Return true if the pin could have been updated
     *
     * @param $pin
     * @return bool
     * @throws dml_exception when pin does not exist (and when error on SQL transaction)
     */
    public static function update($pin)
    {
        global $DB;

        $o = $DB->get_record(self::$table, ['pincode' => $pin], '*', MUST_EXIST);
        $o->timemodified = time();

        return $DB->update_record(self::$table, $o);
    }

    /**
     * @param $hvaid
     * @param $userid
     * @return false|string
     * @throws dml_exception
     */
    public static function generate($hvaid, $userid)
    {
        global $DB;
        $params = ['hvaid' => $hvaid, 'userid' => $userid];

        // if record already exists and is still valid, return it
        if ($DB->record_exists(self::$table, $params)) {
            $record = $DB->get_record(self::$table, $params, '*', IGNORE_MULTIPLE);
            if (self::is_valid($record->pincode)) {
                self::update($record->pincode);
                return $record->pincode;
            }
        }

        // else, generate a new one
        $pincode = $userid . $hvaid . '0000';
        $pincode = substr($pincode, 0, 4);
        while ($DB->record_exists(self::$table, ['pincode' => $pincode])) {
            $pincode = '';
            for ($x = 1; $x <= 4; $x++) {
                $pincode .= random_int(0, 9);
            }
        }

        $o = new stdClass();
        $o->pincode = $pincode;
        $o->hvaid = $hvaid;
        $o->userid = $userid;
        $o->timecreated = time();
        $o->timemodified = $o->timecreated;

        if ($DB->insert_record(self::$table, $o)) {
            return $o->pincode;
        }

        throw new Exception("Error while creating pin code");
    }
}

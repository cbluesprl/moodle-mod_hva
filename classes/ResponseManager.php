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
class ResponseManagerHva
{
    /**
     * @param $object
     */
    public static function send($object)
    {
        if (isset($object->error) || empty($object) || $object === false) {
            if (!empty($object->error)) {
                $msg = "HTTP/1.0 " . $object->error;
            } else {
                $msg = "HTTP/1.0 403";
            }
            header($msg);
            if (isset($object->message)) {
                echo $object->message;
            }
        } else {
            header("Content-Type: application/json");
            echo json_encode($object);
        }
        die;
    }
}

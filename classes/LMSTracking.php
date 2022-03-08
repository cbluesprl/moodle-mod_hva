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
global $CFG;
require_once $CFG->libdir . '/completionlib.php';

class LMSTracking
{
    private $score;
    private $completion;

    public static $allowed_completion = [
        COMPLETION_COMPLETE => 'completed',
        COMPLETION_COMPLETE_PASS => 'passed',
        COMPLETION_COMPLETE_FAIL => 'failed',
        COMPLETION_INCOMPLETE => 'incomplete'
    ];

    /**
     * LMSTracking constructor.
     * @param $score
     * @param $completion
     * @throws Exception
     */
    public function __construct($score, $completion)
    {
        $this->set_score($score);
        $this->set_completion($completion);
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
     * Be sure that score is an integer between 0 and 100
     *
     * @param $score
     * @throws Exception
     */
    public function set_score($score)
    {
        if (!is_numeric($score)) {
            throw new Exception("Score must be an integer");
        }

        $score = (int) $score;
        if ($score < 0 || $score > 100) {
            throw new Exception("Score must be between 0 and 100");
        }

        $this->score = $score;
    }

    /**
     * Be sure that completion is within the allowed values;
     *
     * @param $completion
     * @throws Exception
     */
    public function set_completion($completion)
    {
        // ugly hack to allow to set string & numeric from the same function
        if (is_string($completion) && !is_numeric($completion)) {
            $completion = array_search($completion, self::$allowed_completion);
        }

        if (!in_array($completion, array_keys(self::$allowed_completion))) {
            throw new Exception("The completion '{$completion}' is not allowed");
        }
        $this->completion = $completion;
    }

    /**
     * Main purpose of this : display JSON
     *
     * @return stdClass
     */
    public function output()
    {
        $output = new stdClass();
        $output->score = $this->score;
        $output->completion = self::$allowed_completion[$this->completion];

        return $output;
    }
}

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

namespace mod_hva\task;

use coding_exception;
use core\task\scheduled_task;
use dml_exception;

require_once __DIR__ . '/../../../../config.php';

class delete_pincode_hva extends scheduled_task
{
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name()
    {
        return get_string('modulename', 'hva');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     *
     * @return bool
     * @throws dml_exception
     */
    public function execute()
    {
        global $DB;

        return $DB->execute("DELETE FROM {hva_pincode} WHERE timemodified < UNIX_TIMESTAMP(NOW() - INTERVAL 120 MINUTE)");
    }
}

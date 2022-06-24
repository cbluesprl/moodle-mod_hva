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
 * Structure step to restore one hva activity
 */
class restore_hva_activity_structure_step extends restore_activity_structure_step
{
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('hva', '/activity/hva');
        if ($userinfo) {
            $paths[] = new restore_path_element('hva_tracking', '/activity/hva/hva_trackings/hva_tracking');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_hva($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        // insert the hva record
        $newitemid = $DB->insert_record('hva', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_hva_tracking($data) {
        global $DB;

        $data = (object)$data;

        $data->hvaid = $this->get_new_parentid('hva');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('hva_tracking', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add hva related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_hva', 'intro', null);
    }

}
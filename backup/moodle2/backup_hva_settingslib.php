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
 * Define the complete custommailing structure for backup, with file and id annotations
 */
class backup_hva_activity_structure_step extends backup_activity_structure_step
{

    protected function define_structure()
    {
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $hva = new backup_nested_element('hva', ['id'], [
            'course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified']);

        $hva_trackings = new backup_nested_element('hva_trackings');

        $hva_tracking = new backup_nested_element('hva_tracking', ['id'], [
            'hvaid', 'userid', 'status', 'score', 'timecreated', 'timemodified']);

        // Build the tree
        $hva->add_child($hva_trackings);
        $hva_trackings->add_child($hva_tracking);

        // Define sources
        $hva->set_source_table('hva', ['id' => backup::VAR_ACTIVITYID]);

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $hva_tracking->set_source_table('hva_tracking', ['hvaid' => '../../id']);
        }
        // Define id annotations
        $hva_tracking->annotate_ids('user', 'userid');

        // Define file annotations

        // Return the root element (hva), wrapped into standard activity structure
        return $this->prepare_activity_structure($hva);
    }
}

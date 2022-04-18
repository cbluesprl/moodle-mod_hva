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

defined('MOODLE_INTERNAL') || die();
global $CFG, $PAGE;
require_once $CFG->libdir . '/formslib.php';

class mod_hva_form extends moodleform
{
    public function definition()
    {
        $mform =& $this->_form;

        $mform->addElement('text', 'pincode', get_string('pincode', 'mod_hva'));
        $mform->setType('pincode', PARAM_INT);

        $mform->addElement('text', 'score', get_string('score', 'mod_hva'));
        $mform->setType('score', PARAM_INT);

        $mform->addElement('text', 'completion', get_string('completion', 'mod_hva'));
        $mform->setType('completion', PARAM_TEXT);

        $mform->addElement('text', 'hyperfictionTracking', get_string('hyperfictionTracking', 'mod_hva'));
        $mform->setType('hyperfictionTracking', PARAM_TEXT);

        $options = [
            'get_info' => 'get_info',
            'get_zip' => 'get_zip',
            'save_data' => 'save_data',
        ];
        $mform->addElement('select', 'web_service', get_string('webservice', 'mod_hva'), $options);

        $this->add_action_buttons(null, 'test');
    }
}
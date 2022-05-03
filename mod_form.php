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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/hva/lib.php');

class mod_hva_mod_form extends moodleform_mod
{
    function definition()
    {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('name'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('hvaintro', 'hva'));
        $mform->setAdvanced('introeditor');

        // Display the label to the right of the checkbox so it looks better & matches rest of the form.
        if ($mform->elementExists('showdescription')) {
            $coursedesc = $mform->getElement('showdescription');
            if (!empty($coursedesc)) {
                $coursedesc->setText(' ' . $coursedesc->getLabel());
                $coursedesc->setLabel('&nbsp');
            }
        }

        // Zip File upload.
        $filemanageroptions = [];
        $filemanageroptions['accepted_types'] = ['.zip'];
        $filemanageroptions['maxbytes'] = 4000;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;

        $mform->addElement('filemanager', 'zipfile', get_string('zipfile', 'hva'), null, $filemanageroptions);
        $mform->addRule('zipfile', null, 'required');

        // Metadata File upload.
        $filemanageroptions = [];
        $filemanageroptions['accepted_types'] = ['.json'];
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;

        $mform->addElement('filemanager', 'metadatafile', get_string('metadata', 'hva'), null, $filemanageroptions);

        $this->standard_coursemodule_elements();

        $this->apply_admin_defaults();

        $this->add_action_buttons();
    }


    /**
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data)
    {
        return ($data['customcompletion'] != 0);
    }


    /**
     * @param array $default_values
     */
    public function data_preprocessing(&$default_values)
    {
        parent::data_preprocessing($default_values);

        $default_values['completion'] = 2;
        $default_values['completionview'] = 0;
        $default_values['completionusegrade'] = 1;

        if (empty($entry->id)) {
            $entry = new stdClass;
            $entry->id = null;
        }

        $draftitemid = file_get_submitted_draft_itemid('metadatafile');
        file_prepare_draft_area(
            $draftitemid,
            $this->context->id,
            'mod_hva',
            'metadata',
            0,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        $default_values['metadatafile'] = $draftitemid;

        $draftitemidzipfile = file_get_submitted_draft_itemid('zipfile');
        file_prepare_draft_area(
            $draftitemidzipfile,
            $this->context->id,
            'mod_hva',
            'zipfile',
            1,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        $default_values['zipfile'] = $draftitemidzipfile;
    }
}
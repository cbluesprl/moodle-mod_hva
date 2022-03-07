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

class mod_hva_external {

    public function get_info($pin) {
        //    - donne le zip,
        //    - donne les info de l'utilisateur (nom/prénom)
        //    - donne le statuts de l'activité ou le définir
        //    - donne le tracking de l'utilisateur

    }

    public static function get_info_parameters()  {
        return new external_function_parameters(
            array(
                'pin' => new external_value(PARAM_INT,'Code pin')
            )
        );
    }

    public function get_info_return() {
        //describes return values => json
    }

    public function update_tracking() {
        // récupère les infos de l'user
        // récupère le status de l'activité
        // récupère le tracking sous format json
        // update le status et le tracking de l'utilisateur
    }

    public static function update_tracking_parameters() {
        return new external_function_parameters(
            array(
                //code pin parameter
            )
        );
    }

    public function update_tracking_return() {
        //describes return values => json
    }

}
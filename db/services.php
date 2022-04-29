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

$functions = array(
    'mod_hva_get_info' => array(         //web service function name
        'classname'   => 'mod_hva_external',  //class containing the external function OR namespaced class in classes/external/XXXX.php
        'methodname'  => 'get_info',          //external function name
        'classpath'   => 'mod/hva/externallib.php',  //file containing the class/external function - not required if using namespaced auto-loading classes.
        // defaults to the service's externalib.php
        'description' => 'Get scenario and user\'s info.',    //human readable description of the web service function
        'type'        => 'read',                  //database rights of the web service function (read, write)
        'ajax' => true,        // is the service available to 'internal' ajax calls.
    ),
    'mod_hva_save_data' => array(         //web service function name
        'classname'   => 'mod_hva_external',  //class containing the external function OR namespaced class in classes/external/XXXX.php
        'methodname'  => 'save_data',          //external function name
        'classpath'   => 'mod/hva/externallib.php',  //file containing the class/external function - not required if using namespaced auto-loading classes.
        // defaults to the service's externalib.php
        'description' => 'Save tracking of user.',    //human readable description of the web service function
        'type'        => 'write',                  //database rights of the web service function (read, write)
        'ajax' => true,        // is the service available to 'internal' ajax calls.
    )
);
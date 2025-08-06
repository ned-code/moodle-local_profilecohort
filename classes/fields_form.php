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
 * Local plugin "Profile field based cohort membership" - Form to edit the profile field rules
 *
 * @package   local_profilecohort
 * @copyright 2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_profilecohort;

use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');

/**
 * Class fields_form
 * @package local_profilecohort
 * @copyright 2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fields_form extends moodleform {

    /** @var field_base[]  */
    protected $_rules = [];
    /** @var string[] */
    protected $_values = [];
    protected $_page = 0;
    protected $_perpage = 0;
    protected $_rules_count = 0;

    /**
     * Init params from custom data
     *
     * @return void
     */
    protected function _init(){
        $_d = $this->_customdata;
        $this->_rules = $_d['rules'];
        $this->_values = $_d['values'];
        $this->_page = $_d['page'];
        $this->_perpage = $_d['perpage'];
        $this->_rules_count = $_d['rules_count'];
    }

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition(){
        global $PAGE, $OUTPUT;

        $this->_init();

        $mform = $this->_form;

        $mform->addElement('hidden', 'add', null);
        $mform->setType('add', PARAM_INT);
        $mform->addElement('hidden', 'action', null);
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'page', $this->_page);
        $mform->setType('page', PARAM_INT);

        $show_pagingbar = $this->_perpage > 0;
        if ($show_pagingbar){
            $pagingbar = new \paging_bar($this->_rules_count, $this->_page, $this->_perpage, $PAGE->url, 'page');
            $pagingbar_html = $OUTPUT->render($pagingbar);
            $mform->addElement('html', $pagingbar_html);
        }

        $values = [null => get_string('choosedots')] + $this->_values;
        foreach ($this->_rules as $rule){
            $rule->add_form_field($mform, $values, $this->_rules_count);
        }

        if ($show_pagingbar && !empty($pagingbar_html)){
            $mform->addElement('html', $pagingbar_html);
        }
        $this->add_action_buttons();

        /**
         * Script 'reorder' doesn't work normal with pagination
         * If you remove pagination, you can try to return script again
         */
        //$PAGE->requires->js_call_amd('local_profilecohort/reorder', 'init');
    }

    /**
     * Get each of the rules to validate its own fields
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files){
        $errors = parent::validation($data, $files);
        foreach ($this->_rules as $rule){
            $err = $rule->validation($data);
            $errors = array_merge($errors, $err);
        }
        return $errors;
    }
}

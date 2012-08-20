<?php

/* * **********************************************************************
 * *                          Stamp Collection                           **
 * ************************************************************************
 * @package     mod                                                      **
 * @subpackage  stampcoll                                                **
 * @name        StampColl                                                **
 * @copyright   oohoo.biz                                                **
 * @link        http://oohoo.biz                                         **
 * @author      Braedan Jongerius <jongeriu@ualberta.ca>                 **
 * @author      David Mudrak <david@moodle.com> (Original author)        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later **
 * ************************************************************************
 * ********************************************************************** */

/**
 * This file defines the main Stamp collection module setting form
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/lib/filelib.php');

/**
 * Stamp collection module setting form
 */
class mod_stampcoll_mod_form extends moodleform_mod {

    /**
     * Defines the form
     */
    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        // General -------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Description
        $this->add_intro_editor(false);

        // Stamp collection ----------------------------------------------------
        $mform->addElement('header', 'stampcollection', get_string('modulename', 'stampcoll'));

        // Stamp image
        $imageoptions = array('subdirs' => false, 'accepted_types' => array('image'),
            'maxbytes' => $COURSE->maxbytes, 'return_types' => FILE_INTERNAL);
        $mform->addElement('filemanager', 'image', get_string('stampimage', 'stampcoll'), null, $imageoptions);

        $mform->addElement('static', 'stampimageinfo', '', get_string('stampimageinfo', 'stampcoll'));

        // Display users with no stamps
        $mform->addElement('selectyesno', 'displayzero', get_string('displayzero', 'stampcoll'));
        $mform->setDefault('displayzero', 0);

        // Common module settings ----------------------------------------------
        $this->standard_coursemodule_elements();

        // Buttons -------------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Sets the default form data
     *
     * When editing an existing instance, this method copies the current stamp image into the
     * draft area (standard filemanager workflow).
     *
     * @param array $defaultvalues
     */
    function data_preprocessing(&$defaultvalues) {
        global $COURSE;

        parent::data_preprocessing($defaultvalues);

        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('image');
            $options = array('subdirs' => false, 'accepted_types' => array('image'),
                'maxbytes' => $COURSE->maxbytes, 'return_types' => FILE_INTERNAL);
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_stampcoll', 'image', $this->current->id, $options);
            $defaultvalues['image'] = $draftitemid;
        }
    }

}

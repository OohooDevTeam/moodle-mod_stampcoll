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
 * Defines a form to add or edit a stamp
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Defines a form to add or edit a stamp
 */
class stampcoll_stamp_form extends moodleform {

    /**
     * Defines the form elements
     */
    public function definition() {
        global $OUTPUT, $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        //----------------------------------------------------------------------
        if (empty($data['current'])) {
            // the form is used to add a new stamp
            $mform->addElement('header', 'stampform', get_string('addstamp', 'stampcoll'));
        } else {
            // the form is used to edit some existing stamp
            $mform->addElement('header', 'stampform', get_string('editstamp', 'stampcoll'));
        }

        //----------------------------------------------------------------------
        if (!empty($data['userfrom'])) {
            // we have the giver's details available - let us display them
            $mform->addElement('static', 'from', get_string('from'), $OUTPUT->user_picture($data['userfrom'], array('size' => 16)) . ' ' . fullname($data['userfrom']));
        }

        //----------------------------------------------------------------------
        $mform->addElement('textarea', 'text', get_string('stamptext', 'stampcoll'), array('cols' => 40, 'rows' => 5));
        $mform->setType('text', PARAM_RAW);

        //----------------------------------------------------------------------
        $imgvalues = array();
        $records = $DB->get_records('stampcoll_images', array('stampcollid' => $data['stampcollid']));
        foreach ($records as $record) {
            $imgvalues[$record->id] = $record->name;
        }
        $imgvalues[0] = 'Default Stamp';
        $mform->addElement('select', 'stamptype', get_string('stampimage', 'stampcoll'), $imgvalues);
        $mform->setType('stamptype', PARAM_INT);

        //----------------------------------------------------------------------
        $mform->addGroup(array(
            $mform->createElement('submit', 'submit', get_string('addstampbutton', 'stampcoll')),
            $mform->createElement('cancel', 'cancel', get_string('cancel'))), 'controlbuttons', '&nbsp;', array(' '), false);

        //----------------------------------------------------------------------
        $mform->addElement('hidden', 'userto');
        $mform->setType('userto', PARAM_INT);

        $mform->addElement('hidden', 'userfrom');
        $mform->setType('userfrom', PARAM_INT);
    }

}

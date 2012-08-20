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
 * Defines all the restore steps that will be used by the restore_stampcoll_activity_task
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one stampcoll activity
 */
class restore_stampcoll_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('stampcoll', '/activity/stampcoll');
        if ($userinfo) {
            $paths[] = new restore_path_element('stampcoll_stamp', '/activity/stampcoll/stamps/stamp');
            $paths[] = new restore_path_element('stampcoll_image', '/activity/stampcoll/images/image');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_stampcoll($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('stampcoll', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_stampcoll_stamp($data) {
        global $DB;

        $data = (object) $data;
        $data->stampcollid = $this->get_new_parentid('stampcoll');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!is_null($data->giver)) {
            $data->giver = $this->get_mappingid('user', $data->giver);
        }
        if (!is_null($data->modifier)) {
            $data->modifier = $this->get_mappingid('user', $data->modifier);
        }
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if (!is_null($data->timemodified)) {
            $data->timemodified = $this->apply_date_offset($data->timemodified);
        }

        $DB->insert_record('stampcoll_stamps', $data);
    }

    protected function process_stampcoll_image($data) {
        global $DB;

        $data = (object) $data;
        $data->stampcollid = $this->get_new_parentid('stampcoll');

        $DB->insert_record('stampcoll_images', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_stampcoll', 'intro', null);
        $this->add_related_files('mod_stampcoll', 'image', null);
    }

}

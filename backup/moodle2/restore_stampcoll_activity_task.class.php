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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/stampcoll/backup/moodle2/restore_stampcoll_stepslib.php');

/**
 * stampcoll restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_stampcoll_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // stampcoll only has one structure step
        $this->add_step(new restore_stampcoll_activity_structure_step('stampcoll_structure', 'stampcoll.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('stampcoll', array('intro'), 'stampcoll');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('STAMPCOLLVIEWBYID', '/mod/stampcoll/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('STAMPCOLLINDEX', '/mod/stampcoll/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * stampcoll logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * @todo add missing log rules
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('stampcoll', 'add', 'view.php?id={course_module}', '{stampcoll}');
        $rules[] = new restore_log_rule('stampcoll', 'update', 'view.php?id={course_module}', '{stampcoll}');
        $rules[] = new restore_log_rule('stampcoll', 'view', 'view.php?id={course_module}', '{stampcoll}');
        $rules[] = new restore_log_rule('stampcoll', 'manage', 'view.php?id={course_module}', '{stampcoll}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('stampcoll', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('stampcoll', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}

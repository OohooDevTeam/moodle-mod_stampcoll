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

require_once($CFG->dirroot . '/mod/stampcoll/backup/moodle2/backup_stampcoll_stepslib.php');

/**
 * stampcoll backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_stampcoll_activity_task extends backup_activity_task {

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
        $this->add_step(new backup_stampcoll_activity_structure_step('stampcoll_structure', 'stampcoll.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of stampcolls
        $search = "/(" . $base . "\/mod\/stampcoll\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@STAMPCOLLINDEX*$2@$', $content);

        // Link to stampcoll view by moduleid
        $search = "/(" . $base . "\/mod\/stampcoll\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@STAMPCOLLVIEWBYID*$2@$', $content);

        return $content;
    }

}

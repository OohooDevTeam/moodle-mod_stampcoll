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
 * Lists all Stamp collection instances in the course
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course id

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

add_to_log($course->id, 'stampcoll', 'view all', 'index.php?id='.$course->id, '');

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/stampcoll/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

// output starts here
echo $OUTPUT->header();

if (!$stampcolls = get_all_instances_in_course('stampcoll', $course)) {
    notice(get_string('noinstances', 'stampcoll'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

// get the ids of stampcoll instances, in order they appear in the course
$stampcollids = array();
foreach ($stampcolls as $stampcoll) {
    $stampcollids[] = $stampcoll->id;
}

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array (get_string('week'), get_string('name'), get_string('numberofstamps', 'stampcoll'));
    $table->align = array ('center', 'left', 'center');

} else if ($course->format == 'topics') {
    $table->head  = array (get_string('topic'), get_string('name'), get_string('numberofstamps', 'stampcoll'));
    $table->align = array ('center', 'left', 'center');

} else {
    $table->head  = array (get_string('name'), get_string('numberofstamps', 'stampcoll') );
    $table->align = array ('left', 'left');
}

$currentsection = '';

foreach ($stampcolls as $stampcoll) {
    $cm = get_coursemodule_from_instance('stampcoll', $stampcoll->id, $course->id, false, MUST_EXIST);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $canviewownstamps = has_capability('mod/stampcoll:viewownstamps', $context, null, false);
    $canviewotherstamps = has_capability('mod/stampcoll:viewotherstamps', $context);
    $canviewsomestamps = $canviewownstamps || $canviewotherstamps;

    if (! $canviewsomestamps) {
        $countmystamps = get_string('notallowedtoviewstamps', 'stampcoll');
    } else {
        // todo separated group mode and actual state of enrolments not taken into account here yet
        $rawstamps = $DB->get_records('stampcoll_stamps', array('stampcollid' => $stampcoll->id), 'timecreated', '*');

        $counttotalstamps = count($rawstamps);
        $countmystamps = 0;
        foreach ($rawstamps as $s) {
            if ($s->userid == $USER->id) {
                $countmystamps++;
            }
        }
        unset($rawstamps);
        unset($s);
    }

    $printsection = '';
    if ($stampcoll->section !== $currentsection) {
        if ($stampcoll->section) {
            $printsection = $stampcoll->section;
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $stampcoll->section;
    }

    if (!$stampcoll->visible) {
        $activitylink = html_writer::link(
            new moodle_url('/mod/stampcoll/view.php', array('id' => $stampcoll->coursemodule)),
            format_string($stampcoll->name, true),
            array('class' => 'dimmed'));
    } else {
        $activitylink = html_writer::link(
            new moodle_url('/mod/stampcoll/view.php', array('id' => $stampcoll->coursemodule)),
            format_string($stampcoll->name, true));
    }

    if (! $canviewsomestamps) {
        $stats = get_string('notallowedtoviewstamps', 'stampcoll');
    } else {
        $stats = '';
        if ($canviewownstamps) {
            $stats .= $countmystamps;
        }
        if ($canviewotherstamps) {
            $stats .= ' ('. ($counttotalstamps - $countmystamps) .')';
        }
    }

    if ($course->format == 'weeks' || $course->format == 'topics') {
        $table->data[] = array ($printsection, $activitylink, $stats);
    } else {
        $table->data[] = array ($printsection, $stats);
    }
}

echo $OUTPUT->heading(get_string('modulenameplural', 'stampcoll'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();

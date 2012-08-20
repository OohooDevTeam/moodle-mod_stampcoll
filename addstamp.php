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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/addstamp_form.php');

$scid = required_param('scid', PARAM_INT);  // stamp collection instance id

$stampcoll = $DB->get_record('stampcoll', array('id' => $scid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $stampcoll->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('stampcoll', $stampcoll->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);

$stampcoll = new stampcoll($stampcoll, $cm, $course);

if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$PAGE->set_url(new moodle_url('/mod/stampcoll/addstamp.php', array('scid' => $stampcoll->id)));
$PAGE->set_title($stampcoll->name);
$PAGE->set_heading($course->fullname);

require_capability('mod/stampcoll:givestamps', $stampcoll->context);

$form = new stampcoll_stamp_form(null, array('stampcollid' => $stampcoll->id));

if ($data = $form->get_data()) {

    if ($data->userfrom != $USER->id) {
        throw new moodle_exception('invalid_userfrom_id', 'stampcoll');
    }

    if (!has_capability('mod/stampcoll:collectstamps', $stampcoll->context, $data->userto, false)) {
        throw new moodle_exception('invalid_userto_id', 'stampcoll');
    }

    add_to_log($course->id, 'stampcoll', 'add stamp', 'view.php?id=' . $cm->id, $data->userto, $cm->id);

    $DB->insert_record('stampcoll_stamps', array(
        'stampcollid' => $stampcoll->id,
        'userid' => $data->userto,
        'giver' => $data->userfrom,
        'text' => $data->text,
        'image' => $data->stamptype,
        'timecreated' => time()));

    redirect(new moodle_url('/mod/stampcoll/view.php', array('id' => $cm->id, 'view' => 'single', 'userid' => $data->userto)));
}

redirect(new moodle_url('/mod/stampcoll/view.php', array('id' => $cm->id, 'view' => 'all')));

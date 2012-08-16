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
 * Prints a particular instance of stamp collection module
 *
 * The script prints either user's own stamps or all stamps collected in this
 * activity.
 *
 * @todo make the sortby and sorthow default values configurable per instance
 *
 * @package    mod
 * @subpackage stampcoll
 * @copyright  2007 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/addstamp_form.php');

$cmid       = required_param('id', PARAM_INT);                                  // course module id
$view       = optional_param('view', 'all', PARAM_ALPHA);                       // display mode all|own|single
$userid     = optional_param('userid', null, PARAM_INT);                        // view this single user
$sortby     = optional_param('sortby', 'lastname', PARAM_ALPHA);                // sort by column
$sorthow    = optional_param('sorthow', 'ASC', PARAM_ALPHA);                    // sort direction
$page       = optional_param('page', 0, PARAM_INT);                             // page
$updatepref = optional_param('updatepref', false, PARAM_BOOL);                  // is the preferences form being saved
$perpage    = optional_param('perpage', stampcoll::USERS_PER_PAGE, PARAM_INT);  // users per page preference

$cm         = get_coursemodule_from_id('stampcoll', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$stampcoll  = $DB->get_record('stampcoll', array('id' => $cm->instance), '*', MUST_EXIST);

if (!in_array($view, array('own', 'all', 'single'))) {
    $view = 'all';
}

if ($view == 'single' and is_null($userid)) {
    $view = 'own';
}

if (!in_array($sortby, array('firstname', 'lastname', 'count'))) {
    $sortby = 'lastname';
}

if ($sorthow != 'ASC' and $sorthow != 'DESC') {
    $sorthow = 'ASC';
}

if ($page < 0) {
    $page = 0;
}

require_login($course, true, $cm);

$stampcoll = new stampcoll($stampcoll, $cm, $course);

if ($view == 'single' and $userid == $USER->id) {
    $view = 'own';
}

$PAGE->set_url(new moodle_url('/mod/stampcoll/view.php', array('id' => $cmid, 'view' => $view, 'sortby' => $sortby, 'sorthow' => $sorthow)));
$PAGE->set_title($stampcoll->name);
$PAGE->set_heading($course->fullname);

require_capability('mod/stampcoll:view', $stampcoll->context);

add_to_log($course->id, 'stampcoll', 'view', 'view.php?id='.$cm->id, $stampcoll->id, $cm->id);

if ($updatepref) {
    require_sesskey();
    if ($perpage > 0) {
        set_user_preference('stampcoll_perpage', $perpage);
    }
    redirect($PAGE->url);
}

$canviewownstamps = has_capability('mod/stampcoll:viewownstamps', $stampcoll->context);
$canviewotherstamps = has_capability('mod/stampcoll:viewotherstamps', $stampcoll->context);
$canviewsomestamps = $canviewownstamps || $canviewotherstamps;
$canviewonlyownstamps = $canviewownstamps && (!$canviewotherstamps);

if (!$canviewownstamps and $view == 'own') {
    $view = 'all';
}

if ($canviewonlyownstamps and ($view == 'all' or $view == 'single')) {
    $view = 'own';
}

$output = $PAGE->get_renderer('mod_stampcoll');

echo $output->header();

if (trim($stampcoll->intro)) {
    echo $output->box(format_module_intro('stampcoll', $stampcoll, $cmid), 'generalbox');
}

if (!$canviewsomestamps) {
    notice(get_string('notallowedtoviewstamps', 'mod_stampcoll'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

////////////////////////////////////////////////////////////////////////////////
// View own stamps                                                            //
////////////////////////////////////////////////////////////////////////////////

if ($view == 'own') {

    if (!$canviewownstamps) {
        throw new coding_exception('error in permission evaluation');
    }

    // construct the sql returning all stamp info to display
    $sql = "SELECT s.id AS stampid, s.userid AS holderid, s.text AS stamptext, s.image AS image,
                   s.timecreated AS stamptimecreated, s.timemodified AS stamptimemodified,".
                   user_picture::fields('gu', null, 'giverid', 'giver')."
              FROM {stampcoll_stamps} s
         LEFT JOIN {user} gu ON s.giver = gu.id AND gu.deleted = 0
             WHERE s.stampcollid = :stampcollid AND s.userid = :holderid
          ORDER BY s.timecreated";
    $params = array('stampcollid' => $stampcoll->id, 'holderid' => $USER->id);

    // prepare the renderable collection
    $collection = new stampcoll_singleuser_collection($stampcoll, $USER);

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $record) {
        if (!empty($record->giverid)) {
            $collection->register_user(user_picture::unalias($record, null, 'giverid', 'giver'));
        }
        if (!empty($record->stampid)) {
            $stamp = (object)array(
                'id'            => $record->stampid,
                'userid'        => $record->holderid,
                'giver'         => $record->giverid,
                'text'          => $record->stamptext,
                'image'        => $record->image,
                'timecreated'   => $record->stamptimecreated,
                'timemodified'  => $record->stamptimemodified,
            );
            $collection->add_stamp($stamp);
        }
    }
    $rs->close();

    echo $output->render($collection);

////////////////////////////////////////////////////////////////////////////////
// View someone else's stamps                                                 //
////////////////////////////////////////////////////////////////////////////////

} else if ($view == 'single') {

    if (!$canviewotherstamps) {
        throw new coding_exception('error in permission evaluation');
    }

    $user = $DB->get_record('user', array('id' => $userid), user_picture::fields(), MUST_EXIST);

    if (!is_enrolled($stampcoll->context, $user->id, '', true)) {
        notice(get_string('usernotenrolled', 'stampcoll'), new moodle_url('/course/view.php', array('id' => $course->id)));
    }

    // construct the sql returning all stamp info to display
    $sql = "SELECT s.id AS stampid, s.userid AS holderid, s.text AS stamptext, s.image AS image,
                   s.timecreated AS stamptimecreated, s.timemodified AS stamptimemodified,".
                   user_picture::fields('gu', null, 'giverid', 'giver')."
              FROM {stampcoll_stamps} s
         LEFT JOIN {user} gu ON s.giver = gu.id AND gu.deleted = 0
             WHERE s.stampcollid = :stampcollid AND s.userid = :holderid
          ORDER BY s.timecreated";
    $params = array('stampcollid' => $stampcoll->id, 'holderid' => $user->id);


    // prepare the renderable collection
    $collection = new stampcoll_singleuser_collection($stampcoll, $user);

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $record) {
        if (!empty($record->giverid)) {
            $collection->register_user(user_picture::unalias($record, null, 'giverid', 'giver'));
        }
        if (!empty($record->stampid)) {
            $stamp = (object)array(
                'id'            => $record->stampid,
                'userid'        => $record->holderid,
                'giver'         => $record->giverid,
                'text'          => $record->stamptext,
                'image'         => $record->image,
                'timecreated'   => $record->stamptimecreated,
                'timemodified'  => $record->stamptimemodified,
            );
            $collection->add_stamp($stamp);
        }
    }
    $rs->close();

    echo $output->render($collection);

    // append a form to give a new stamp
    if (has_capability('mod/stampcoll:collectstamps', $stampcoll->context, $user, false) and
            has_capability('mod/stampcoll:givestamps', $stampcoll->context, $USER)) {

        $form = new stampcoll_stamp_form(
                        new moodle_url('/mod/stampcoll/addstamp.php', array('scid' => $stampcoll->id)),
                        array(
                            'userfrom'    => $USER,
                            'stampcollid' => $stampcoll->id
                        ),
                        'post', '', array('class' => 'stampform'));

        $form->set_data(array(
            'userfrom'  => $USER->id,
            'userto'    => $user->id,
        ));

        $form->display();
    }

////////////////////////////////////////////////////////////////////////////////
// View all stamps                                                            //
////////////////////////////////////////////////////////////////////////////////

} else if ($view == 'all') {

    if (!$canviewotherstamps) {
        throw new coding_exception('error in permission evaluation');
    }

    $PAGE->url->param('sortby', $sortby);
    $PAGE->url->param('sorthow', $sorthow);

    $groupmode = groups_get_activity_groupmode($cm);

    if ($groupmode == NOGROUPS) {
        $groupid = false;

    } else {
        groups_print_activity_menu($cm, $PAGE->url);
        $groupid = groups_get_activity_group($cm);

        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $stampcoll->context)) {
            if (!groups_is_member($groupid)) {
                // this should not happen but...
                notice(get_string('groupusernotmember', 'core_error'), new moodle_url('/course/view.php', array('id' => $course->id)));
            }
        }
    }

    // get the sql returning all actively enrolled users who can collect stamps
    list($enrolsql, $enrolparams) = get_enrolled_sql($stampcoll->context, 'mod/stampcoll:collectstamps', $groupid, true);

    // determine how to join user and stamps tables
    if ($stampcoll->displayzero) {
        $jointype = 'LEFT';
    } else {
        $jointype = 'INNER';
    }

    // in the first query, get the list of users to be displayed
    $sql = "SELECT COUNT(*)
              FROM (SELECT DISTINCT(u.id)
                      FROM {user} u
                      JOIN ($enrolsql) eu ON u.id = eu.id
            $jointype JOIN {stampcoll_stamps} s ON s.stampcollid = :stampcollid AND s.userid = u.id) t";

    $params = array_merge($enrolparams, array('stampcollid' => $stampcoll->id));

    $totalcount = $DB->count_records_sql($sql, $params);

    // in the second query, get the list of user ids to display based on the sorting and paginating
    $sql = "SELECT u.id, u.firstname, u.lastname, COUNT(s.id) AS count
              FROM {user} u
              JOIN ($enrolsql) eu ON u.id = eu.id
    $jointype JOIN {stampcoll_stamps} s ON s.stampcollid = :stampcollid AND s.userid = u.id
          GROUP BY u.id, u.firstname, u.lastname
          ORDER BY $sortby $sorthow";

    $params = array_merge($enrolparams, array('stampcollid' => $stampcoll->id));

    $perpage = get_user_preferences('stampcoll_perpage', stampcoll::USERS_PER_PAGE);

    $userids = array_keys($DB->get_records_sql($sql, $params, $page * $perpage, $perpage));

    // prepare the renderable collection
    $collection             = new stampcoll_multiuser_collection($stampcoll, $userids);
    $collection->sortedby   = $sortby;
    $collection->sortedhow  = $sorthow;
    $collection->page       = $page;
    $collection->perpage    = $perpage;
    $collection->totalcount = $totalcount;

    if ($userids) {
        // in the third query, get all stamps info to display
        list($holdersql, $holderparam) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $sql = "SELECT ".user_picture::fields('hu', null, 'holderid', 'holder').",
                       s.id AS stampid, s.text AS stamptext, s.image AS image,
                       s.timecreated AS stamptimecreated, s.timemodified AS stamptimemodified,".
                       user_picture::fields('gu', null, 'giverid', 'giver')."
                  FROM {user} hu
        $jointype JOIN {stampcoll_stamps} s ON s.stampcollid = :stampcollid AND s.userid = hu.id
             LEFT JOIN {user} gu ON s.giver = gu.id AND gu.deleted = 0
                 WHERE hu.id $holdersql
              ORDER BY s.timecreated";

        $params = array_merge(array('stampcollid' => $stampcoll->id), $holderparam);

        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $record) {
            if (!empty($record->holderid)) {
                $collection->register_holder(user_picture::unalias($record, null, 'holderid', 'holder'));
            }
            if (!empty($record->giverid)) {
                $collection->register_user(user_picture::unalias($record, null, 'giverid', 'giver'));
            }
            if (!empty($record->stampid)) {
                $stamp = (object)array(
                    'id'            => $record->stampid,
                    'userid'        => $record->holderid,
                    'giver'         => $record->giverid,
                    'text'          => $record->stamptext,
                    'image'         => $record->image,
                    'timecreated'   => $record->stamptimecreated,
                    'timemodified'  => $record->stamptimemodified,
                );
                $collection->add_stamp($stamp);
            }
        }
        $rs->close();
    }

    echo $output->render($collection);
}

echo $output->footer();

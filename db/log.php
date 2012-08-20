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
 * Definition of log events
 */
defined('MOODLE_INTERNAL') || die();

global $DB;

$logs = array(
    array('module' => 'stampcoll', 'action' => 'view', 'mtable' => 'stampcoll', 'field' => 'name'),
    array('module' => 'stampcoll', 'action' => 'update', 'mtable' => 'stampcoll', 'field' => 'name'),
    array('module' => 'stampcoll', 'action' => 'add', 'mtable' => 'stampcoll', 'field' => 'name'),
    array('module' => 'stampcoll', 'action' => 'manage', 'mtable' => 'stampcoll', 'field' => 'name'),
    array('module' => 'stampcoll', 'action' => 'add stamp', 'mtable' => 'user', 'field' => $DB->sql_concat('firstname', "' '", 'lastname')),
    array('module' => 'stampcoll', 'action' => 'update stamp', 'mtable' => 'user', 'field' => $DB->sql_concat('firstname', "' '", 'lastname')),
    array('module' => 'stampcoll', 'action' => 'delete stamp', 'mtable' => 'user', 'field' => $DB->sql_concat('firstname', "' '", 'lastname')),
);

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
 * Keeps track of upgrades to the Stamp collection module
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Checks if some upgrade steps are needed and performs them eventually
 *
 * @param int $oldversion the current version we are upgrading from
 * @return true
 */
function xmldb_stampcoll_upgrade($oldversion = 0) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    //==== 1.9 upgrade line ====

    /**
     * Only upgrades from the version for Moodle 1.9 are supported
     */
    if ($oldversion < 2008022003) {
        throw new upgrade_exception('mod_stampcoll', $oldversion, 'Unable to upgrade such an old version of the module.');
    }

    /**
     * Rename field text to intro
     */
    if ($oldversion < 2011070100) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('text', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'name');
        $dbman->rename_field($table, $field, 'intro');
        upgrade_mod_savepoint(true, 2011070100, 'stampcoll');
    }

    /**
     * Make intro field nullable
     */
    if ($oldversion < 2011070101) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'name');
        $dbman->change_field_notnull($table, $field);
        upgrade_mod_savepoint(true, 2011070101, 'stampcoll');
    }

    /**
     * Make intro field big
     */
    if ($oldversion < 2011070102) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'name');
        $dbman->change_field_precision($table, $field);
        upgrade_mod_savepoint(true, 2011070102, 'stampcoll');
    }

    /**
     * Rename field format to introformat
     */
    if ($oldversion < 2011070103) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('format', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');
        $dbman->rename_field($table, $field, 'introformat');
        upgrade_mod_savepoint(true, 2011070103, 'stampcoll');
    }

    //==== 2.0 upgrade line ====

    /**
     * Drop foreign keys and indices
     */
    if ($oldversion < 2011120700) {
        $table = new xmldb_table('stampcoll_stamps');
        $key = new xmldb_key('stampcollid', XMLDB_KEY_FOREIGN, array('stampcollid'), 'stampcoll', array('id'));
        $dbman->drop_key($table, $key);

        $table = new xmldb_table('stampcoll_stamps');
        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $table = new xmldb_table('stampcoll_stamps');
        $index = new xmldb_index('giver', XMLDB_INDEX_NOTUNIQUE, array('giver'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // stampcoll savepoint reached
        upgrade_mod_savepoint(true, 2011120700, 'stampcoll');
    }

    /**
     * Drop the anonymous field from the stampcoll table
     */
    if ($oldversion < 2011120701) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('anonymous');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2011120701, 'stampcoll');
    }

    /**
     * Change the nullability of field image on table stampcoll to null
     */
    if ($oldversion < 2011120702) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('image', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'introformat');
        $dbman->change_field_notnull($table, $field);
        upgrade_mod_savepoint(true, 2011120702, 'stampcoll');
    }

    /**
     * Changing the default of field stampcollid on table stampcoll_stamps to drop it
     */
    if ($oldversion < 2011120703) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('stampcollid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'id');
        $dbman->change_field_default($table, $field);
        upgrade_mod_savepoint(true, 2011120703, 'stampcoll');
    }

    /**
     * Changing the default of field userid on table stampcoll_stamps to drop it
     */
    if ($oldversion < 2011120704) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'stampcollid');
        $dbman->change_field_default($table, $field);
        upgrade_mod_savepoint(true, 2011120704, 'stampcoll');
    }

    /**
     * Changing nullability of field giver on table stampcoll_stamps to null
     */
    if ($oldversion < 2011120705) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('giver', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'userid');
        $dbman->change_field_notnull($table, $field);
        upgrade_mod_savepoint(true, 2011120705, 'stampcoll');
    }

    /**
     * Changing the default of field giver on table stampcoll_stamps to drop it
     */
    if ($oldversion < 2011120706) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('giver', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'userid');
        $dbman->change_field_default($table, $field);
        upgrade_mod_savepoint(true, 2011120706, 'stampcoll');
    }

    /**
     * Since 2.0, the giver contains NULL instead of 0 if the stamp was originally given in 1.x anonymous mode
     *
     * This is mainly to prevent eventual issues with foreign key reference once we start
     * using it.
     */
    if ($oldversion < 2011120707) {
        $DB->set_field('stampcoll_stamps', 'giver', null, array('giver' => 0));
        upgrade_mod_savepoint(true, 2011120707, 'stampcoll');
    }

    /**
     * Changing sign of field timemodified on table stampcoll_stamps to unsigned
     */
    if ($oldversion < 2011120708) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'timecreated');
        $dbman->change_field_unsigned($table, $field);
        upgrade_mod_savepoint(true, 2011120708, 'stampcoll');
    }

    /**
     * Changing the default of field timemodified on table stampcoll_stamps to drop it
     */
    if ($oldversion < 2011120709) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'timecreated');
        $dbman->change_field_default($table, $field);
        upgrade_mod_savepoint(true, 2011120709, 'stampcoll');
    }

    /**
     * Add the field modifier to the stampcoll_stamps table
     */
    if ($oldversion < 2011120710) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('modifier', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'giver');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2011120710, 'stampcoll');
    }

    /**
     * Add the field timecreated to the stampcoll_stamps table - initially with default 0
     */
    if ($oldversion < 2011120711) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'text');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2011120711, 'stampcoll');
    }

    /**
     * Set the initial value of the timecreated field for existing stamps
     *
     * We have no other option here but to pretend that the stamp was created when it was modified most recently
     */
    if ($oldversion < 2011120712) {
        $DB->execute("UPDATE {stampcoll_stamps} SET timecreated = timemodified");
        upgrade_mod_savepoint(true, 2011120712, 'stampcoll');
    }

    /**
     * Drop the default value of the field timecreated - it was there just temporarily so we were able to add
     * that field.
     */
    if ($oldversion < 2011120713) {
        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'text');
        $dbman->change_field_default($table, $field);
        upgrade_mod_savepoint(true, 2011120713, 'stampcoll');
    }

    /**
     * Regenerate the foreign keys
     */
    if ($oldversion < 2011120714) {

        $table = new xmldb_table('stampcoll_stamps');
        $key = new xmldb_key('fk_stampcollid', XMLDB_KEY_FOREIGN, array('stampcollid'), 'stampcoll', array('id'));
        $dbman->add_key($table, $key);

        $table = new xmldb_table('stampcoll_stamps');
        $key = new xmldb_key('fk_userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $dbman->add_key($table, $key);

        $table = new xmldb_table('stampcoll_stamps');
        $key = new xmldb_key('fk_giver', XMLDB_KEY_FOREIGN, array('giver'), 'user', array('id'));
        $dbman->add_key($table, $key);

        $table = new xmldb_table('stampcoll_stamps');
        $key = new xmldb_key('fk_modifier', XMLDB_KEY_FOREIGN, array('modifier'), 'user', array('id'));
        $dbman->add_key($table, $key);

        upgrade_mod_savepoint(true, 2011120714, 'stampcoll');
    }

    /**
     * Migrate custom stamp images to stored files in the file pool
     */
    if ($oldversion < 2011120715) {

        $fs = get_file_storage();

        $sql = "SELECT sc.id, sc.course, sc.image, cm.id AS cmid
                  FROM {stampcoll} sc
                  JOIN {modules} m ON (m.name = 'stampcoll')
                  JOIN {course_modules} cm ON (cm.module = m.id AND cm.instance = sc.id)
                 WHERE sc.image IS NOT NULL AND sc.image <> '/UPGRADEINPROGRESS/'";
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $stampcoll) {
            if (empty($stampcoll->image)) {
                $DB->set_field('stampcoll', 'image', null, array('id' => $stampcoll->id));
                continue;
            }
            $imagefilename = basename($stampcoll->image);
            $context = get_context_instance(CONTEXT_MODULE, $stampcoll->cmid);
            if ($fs->file_exists($context->id, 'mod_stampcoll', 'image', 0, '/', $imagefilename)) {
                // hmm, I can't really see how this might happen but just in case...
                $DB->set_field('stampcoll', 'image', '/UPGRADEINPROGRESS/', array('id' => $stampcoll->id));
                continue;
            }
            $coursecontext = get_context_instance(CONTEXT_COURSE, $stampcoll->course);
            $imagefilepath = dirname($stampcoll->image);
            if ($imagefilepath == '.') {
                $imagefilepath = '/';
            } else {
                $imagefilepath = '/' . $imagefilepath . '/';
            }
            $legacyimage = $fs->get_file($coursecontext->id, 'course', 'legacy', 0, $imagefilepath, $imagefilename);
            if ($legacyimage instanceof stored_file) {
                $filerecord = array('contextid' => $context->id,
                    'component' => 'mod_stampcoll',
                    'filearea' => 'image',
                    'itemid' => 0,
                    'filepath' => '/',
                    'filename' => $imagefilename);
                $stampimage = $fs->create_file_from_storedfile($filerecord, $legacyimage);
                $DB->set_field('stampcoll', 'image', '/UPGRADEINPROGRESS/', array('id' => $stampcoll->id));
            } else {
                $DB->set_field('stampcoll', 'image', null, array('id' => $stampcoll->id));
                continue;
            }
        }
        $rs->close();

        upgrade_mod_savepoint(true, 2011120715, 'stampcoll');
    }

    /**
     * Store the filenames of migrate custom stamp images in stampcoll table
     */
    if ($oldversion < 2011120716) {

        $fs = get_file_storage();

        $sql = "SELECT sc.id, sc.course, sc.image, cm.id AS cmid
                  FROM {stampcoll} sc
                  JOIN {modules} m ON (m.name = 'stampcoll')
                  JOIN {course_modules} cm ON (cm.module = m.id AND cm.instance = sc.id)
                 WHERE sc.image = '/UPGRADEINPROGRESS/'";
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $stampcoll) {
            $context = get_context_instance(CONTEXT_MODULE, $stampcoll->cmid);
            foreach ($fs->get_area_files($context->id, 'mod_stampcoll', 'image', 0, 'timemodified DESC', false) as $storedfile) {
                $imagefilename = $storedfile->get_filename();
                if (!$storedfile->is_valid_image()) {
                    echo $OUTPUT->notification('Invalid stamp image ' . $imagefilename . ' in the stampcoll id ' . $stampcoll->id . ' (cmid ' . $stampcoll->cmid . ')');
                }
                break;
            }
            $DB->set_field('stampcoll', 'image', $imagefilename, array('id' => $stampcoll->id));
        }
        $rs->close();

        upgrade_mod_savepoint(true, 2011120716, 'stampcoll');
    }

    /**
     * Add a table to store information about stamp images
     */
    if ($oldversion < 2012081500) {
        $table = new xmldb_table('stampcoll');
        $field = new xmldb_field('image');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $table = new xmldb_table('stampcoll_images');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('stampcollid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_stampcollid', XMLDB_KEY_FOREIGN, array('stampcollid'), 'stampcoll', array('id'));
        // Conditionally launch create table for this table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('stampcoll_stamps');
        $field = new xmldb_field('text', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, null, 'modifier');
        $dbman->change_field_precision($table, $field);
        $field = new xmldb_field('image', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'text');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2012081500, 'stampcoll');
    }

    return true;
}

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
 * Bulk course registration script from a comma separated file
 *
 * @package    tool
 * @subpackage uploadcourse
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @copyright  2011 Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once('locallib.php');
require_once('course_form.php');

$iid         = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60*60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

require_login();
admin_externalpage_setup('tooluploadcourse');
require_capability('moodle/site:uploadusers', get_context_instance(CONTEXT_SYSTEM));

$strcourserenamed             = get_string('courserenamed', 'tool_uploadcourse');
$strcoursenotrenamedexists    = get_string('coursenotrenamedexists', 'tool_uploadcourse');
$strcoursenotrenamedmissing   = get_string('coursenotrenamedmissing', 'tool_uploadcourse');
$strcoursenotrenamedoff       = get_string('coursenotrenamedoff', 'tool_uploadcourse');

$strcourseupdated             = get_string('courseaccountupdated', 'tool_uploadcourse');
$strcoursenotupdated          = get_string('coursenotupdatederror', 'tool_uploadcourse');
$strcoursenotupdatednotexists = get_string('coursenotupdatednotexists', 'tool_uploadcourse');

$strcourseuptodate            = get_string('courseaccountuptodate', 'tool_uploadcourse');

$strcourseadded               = get_string('newcourse');
$strcoursenotadded            = get_string('coursenotadded', 'tool_uploadcourse');
$strcoursenotaddederror       = get_string('coursenotaddederror', 'tool_uploadcourse');

$strcoursedeleted             = get_string('coursedeleted', 'tool_uploadcourse');
$strcoursenotdeletederror     = get_string('coursenotdeletederror', 'tool_uploadcourse');
$strcoursenotdeletedmissing   = get_string('coursenotdeletedmissing', 'tool_uploadcourse');
$strcoursenotdeletedoff       = get_string('coursenotdeletedoff', 'tool_uploadcourse');
$errorstr                     = get_string('error');

$returnurl = new moodle_url('/admin/tool/uploadcourse/index.php');
$bulknurl  = new moodle_url('/admin/tool/uploadcourse/index.php');

$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

// array of all valid fields for validation
$STD_FIELDS = array('fullname', 'shortname', 'category', 'idnumber', 'summary',
        'format', 'showgrades', 'newsitems', 'teacher', 'teachers', 'student',
        'students', 'startdate', 'numsections', 'maxbytes', 'visible', 'groupmode',
        'enrolperiod', 'groupmodeforce', 'metacourse', 'lang', 'theme',
        'cost', 'showreports', 'guest', 'enrollable', 'enrolstartdate',
        'enrolenddate', 'notifystudents', 'expirynotify', 'expirythreshold',
        'deleted',     // 1 means delete course
        'oldshortname', // for renaming
    );


if (empty($iid)) {
    $mform1 = new admin_uploadcourse_form1();

    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($iid, 'uploadcourse');

        $content = $mform1->get_file_content('coursefile');

        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);

        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok
        $filecolumns = cc_validate_course_upload_columns($cir, $STD_FIELDS, $returnurl);
        // continue to form2

    } else {
        echo $OUTPUT->header();

        echo $OUTPUT->heading_with_help(get_string('uploadcourses', 'tool_uploadcourse'), 'uploadcourses', 'tool_uploadcourse');

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploadcourse');
    $filecolumns = cc_validate_course_upload_columns($cir, $STD_FIELDS, $returnurl);
}

$mform2 = new admin_uploadcourse_form2(null, array('columns'=>$filecolumns, 'data'=>array('iid'=>$iid, 'previewrows'=>$previewrows)));

// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);

} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadcoursesresult', 'tool_uploadcourse'));

    $optype = $formdata->cctype;

    $updatetype        = isset($formdata->ccupdatetype) ? $formdata->ccupdatetype : 0;
    $allowrenames      = (!empty($formdata->ccallowrenames) and $optype != CC_COURSE_ADDNEW and $optype != CC_COURSE_ADDINC);
    $allowdeletes      = (!empty($formdata->ccallowdeletes) and $optype != CC_COURSE_ADDNEW and $optype != CC_COURSE_ADDINC);
    $bulk              = isset($formdata->ccbulk) ? $formdata->ccbulk : 0;
    $standardshortnames = $formdata->ccstandardshortnames;

    // verification moved to two places: after upload and into form2
    $coursesnew      = 0;
    $coursesupdated  = 0;
    $coursesuptodate = 0; //not printed yet anywhere
    $courseserrors   = 0;
    $deletes       = 0;
    $deleteerrors  = 0;
    $renames       = 0;
    $renameerrors  = 0;
    $coursesskipped  = 0;
    $weakpasswords = 0;

    // caches
    $ccache         = array(); // course cache - do not fetch all courses here, we  will not probably use them all anyway!
    $cohorts        = array();
    $manualcache    = array(); // cache of used manual enrol plugins in each course

    // clear bulk selection
    if ($bulk) {
        $SESSION->bulk_courses = array();
    }

    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line

    // init upload progress tracker
    $upt = new cc_progress_tracker();
    $upt->start(); // start table

    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;

        $upt->track('line', $linenum);

        $course = new stdClass();

        // add fields to course object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $course->$key = $value;

            if (in_array($key, $upt->columns)) {
                // default value in progress tracking table, can be changed later
                $upt->track($key, s($value), 'normal');
            }
        }
        // validate category
        if (!empty($course->category)) {
                $category = $DB->get_record('course_categories', array('name'=>$course->category));
                if (empty($category)) {
                    $upt->track('status', get_string('invalidvalue', 'tool_uploadcourse', 'category'), 'error');
                    $upt->track('category', $errorstr, 'error');
                    $error = true;
                }
                else {
                    $course->category = $category->id;
                }
        }
        if (!isset($course->shortname)) {
            // prevent warnings bellow
            $course->shortname = '';
        }

        if ($optype == CC_COURSE_ADDNEW or $optype == CC_COURSE_ADDINC) {
            // course creation is a special case - the shortname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($course->fullname) or $course->fullname === '') {
                $upt->track('status', get_string('missingfield', 'error', 'fullname'), 'error');
                $upt->track('fullname', $errorstr, 'error');
                $error = true;
            }
            if (!isset($course->summary) or $course->summary === '') {
                $upt->track('status', get_string('missingfield', 'error', 'summary'), 'error');
                $upt->track('summary', $errorstr, 'error');
                $error = true;
            }
            if ($error) {
                $courseserrors++;
                continue;
            }
            // we require shortname too - we might use template for it though
            if (empty($course->shortname) and !empty($formdata->ccshortname)) {
                $course->shortname = cc_process_template($formdata->ccshortname, $course);
                $upt->track('shortname', s($course->shortname));
            }
        }

        // normalize shortname
        $originalshortname = $course->shortname;
        if ($standardshortnames) {
            $course->shortname = clean_param($course->shortname, PARAM_MULTILANG);
        }

        // make sure we really have shortname
        if (empty($course->shortname)) {
            $upt->track('status', get_string('missingfield', 'error', 'shortname'), 'error');
            $upt->track('shortname', $errorstr, 'error');
            $courseserrors++;
            continue;
        }

        if ($existingcourse = $DB->get_record('course', array('shortname' => $course->shortname))) {
            $upt->track('id', $existingcourse->id, 'normal', false);
        }

        // find out in shortname incrementing required
        if ($existingcourse and $optype == CC_COURSE_ADDINC) {
            $course->shortname = cc_increment_shortname($course->shortname);
            if (!empty($course->idnumber)) {
                $oldidnumber = $course->idnumber;
                $course->idnumber = cc_increment_idnumber($course->idnumber);
                if ($course->idnumber !== $oldidnumber) {
                    $upt->track('idnumber', s($oldidnumber).'-->'.s($course->idnumber), 'info');
                }
            }
            $existingcourse = false;
        }
        
        // check duplicate idnumber
        if (!$existingcourse and isset($course->idnumber)) {
            if ($DB->record_exists('course', array('idnumber' => $course->idnumber))) {
                $upt->track('status', get_string('idnumbernotunique', 'tool_uploadcourse'), 'error');
                $upt->track('idnumber', $errorstr, 'error');
                $error = true;
            }
        }

        // notify about nay shortname changes
        if ($originalshortname !== $course->shortname) {
            $upt->track('shortname', '', 'normal', false); // clear previous
            $upt->track('shortname', s($originalshortname).'-->'.s($course->shortname), 'info');
        } else {
            $upt->track('shortname', s($course->shortname), 'normal', false);
        }

        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($course->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                $course->$field = $formdata->$field;
                $formdefaults[$field] = true;
                if (in_array($field, $upt->columns)) {
                    $upt->track($field, s($course->$field), 'normal');
                }
            }
            else {
                // process templates
                if (isset($formdata->{"cc".$field}) && !empty($formdata->{"cc".$field}) && empty($course->$field)) {
                    $course->$field = cc_process_template($formdata->{"cc".$field}, $course);
                }
            }
        }
        if (empty($course->category)) {
            $course->category = $formdata->cccategory;
        }

        // delete course
        if (!empty($course->deleted)) {
            if (!$allowdeletes) {
                $coursesskipped++;
                $upt->track('status', $strcoursenotdeletedoff, 'warning');
                continue;
            }
            if ($existingcourse) {
                if (delete_course($existingcourse->id, false)) {
                    $upt->track('status', $strcoursedeleted);
                    $deletes++;
                } else {
                    $upt->track('status', $strcoursenotdeletederror, 'error');
                    $deleteerrors++;
                }
            } else {
                $upt->track('status', $strcoursenotdeletedmissing, 'error');
                $deleteerrors++;
            }
            continue;
        }
        // we do not need the deleted flag anymore
        unset($course->deleted);

        // renaming requested?
        if (!empty($course->oldshortname) ) {
            if (!$allowrenames) {
                $coursesskipped++;
                $upt->track('status', $strcoursenotrenamedoff, 'warning');
                continue;
            }

            if ($existingcourse) {
                $upt->track('status', $strcoursenotrenamedexists, 'error');
                $renameerrors++;
                continue;
            }

            if ($standardshortnames) {
                $oldshortname = clean_param($course->oldshortname, PARAM_MULTILANG);
            } else {
                $oldshortname = $course->oldshortname;
            }

            // no guessing when looking for old shortname, it must be exact match
            if ($oldcourse = $DB->get_record('course', array('shortname'=>$oldshortname))) {
                $upt->track('id', $oldcourse->id, 'normal', false);
                $DB->set_field('course', 'shortname', $course->shortname, array('id'=>$oldcourse->id));
                $upt->track('shortname', '', 'normal', false); // clear previous
                $upt->track('shortname', s($oldshortname).'-->'.s($course->shortname), 'info');
                $upt->track('status', $strcourserenamed);
                $renames++;
            } else {
                $upt->track('status', $strcoursenotrenamedmissing, 'error');
                $renameerrors++;
                continue;
            }
            $existingcourse = $oldcourse;
            $existingcourse->shortname = $course->shortname;
        }

        // can we process with update or insert?
        $skip = false;
        switch ($optype) {
            case CC_COURSE_ADDNEW:
                if ($existingcourse) {
                    $coursesskipped++;
                    $upt->track('status', $strcoursenotadded, 'warning');
                    $skip = true;
                }
                break;

            case CC_COURSE_ADDINC:
                if ($existingcourse) {
                    //this should not happen!
                    $upt->track('status', $strcoursenotaddederror, 'error');
                    $courseserrors++;
                    $skip = true;
                }
                break;

            case CC_COURSE_ADD_UPDATE:
                break;

            case CC_COURSE_UPDATE:
                if (!$existingcourse) {
                    $coursesskipped++;
                    $upt->track('status', $strcoursenotupdatednotexists, 'warning');
                    $skip = true;
                }
                break;

            default:
                // unknown type
                $skip = true;
        }

        if ($skip) {
            continue;
        }
        

        if ($existingcourse) {
            $course->id = $existingcourse->id;

            $upt->track('shortname', html_writer::link(new moodle_url('/course/view.php', array('id'=>$existingcourse->id)), s($existingcourse->shortname)), 'normal', false);

            $existingcourse->timemodified = time();
            // do NOT mess with timecreated or firstaccess here!
            $doupdate = false;

            if ($updatetype != CC_UPDATE_NOCHANGES) {
                foreach ($STD_FIELDS as $column) {
                    if ($column === 'shortname') {
                        // these can not be changed here
                        continue;
                    }
                    if (!property_exists($course, $column) or !property_exists($existingcourse, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == CC_UPDATE_MISSING) {
                        if (!is_null($existingcourse->$column) and $existingcourse->$column !== '') {
                            continue;
                        }
                    } else if ($updatetype == CC_UPDATE_ALLOVERRIDE) {
                        // we override everything

                    } else if ($updatetype == CC_UPDATE_FILEOVERRIDE) {
                        if (!empty($formdefaults[$column])) {
                            // do not override with form defaults
                            continue;
                        }
                    }
                    if ($existingcourse->$column !== $course->$column) {
                        if (in_array($column, $upt->columns)) {
                            $upt->track($column, s($existingcourse->$column).'-->'.s($course->$column), 'info', false);
                        }
                        $existingcourse->$column = $course->$column;
                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {
                // we want only courses that were really updated
                update_course($existingcourse);
                $upt->track('status', $strcourseupdated);
                $coursesupdated++;

                events_trigger('course_updated', $existingcourse);

                if ($bulk == CC_BULK_UPDATED or $bulk == CC_BULK_ALL) {
                    if (!in_array($course->id, $SESSION->bulk_courses)) {
                        $SESSION->bulk_courses[] = $course->id;
                    }
                }

            } else {
                // no course information changed
                $upt->track('status', $strcourseuptodate);
                $coursesuptodate++;

                if ($bulk == CC_BULK_ALL) {
                    if (!in_array($course->id, $SESSION->bulk_courses)) {
                        $SESSION->bulk_courses[] = $course->id;
                    }
                }
            }

        } else {
            // save the new course to the database
            $course->timemodified = time();
            $course->timecreated  = time();


            // create course - insert_record ignores any extra properties
            try {
                $course = create_course($course);
            }
            catch (moodle_exception $e) {
                $upt->track('status', $e->getMessage(), 'error');
                $courseserrors++;
                $skip = true;
                continue;
            }
            $upt->track('shortname', html_writer::link(new moodle_url('/course/view.php', array('id'=>$course->id)), s($course->shortname)), 'normal', false);

            $upt->track('status', $strcourseadded);
            $upt->track('id', $course->id, 'normal', false);
            $coursesnew++;

            // make sure course context exists
            get_context_instance(CONTEXT_COURSE, $course->id);

            events_trigger('course_created', $course);

            if ($bulk == CC_BULK_NEW or $bulk == CC_BULK_ALL) {
                if (!in_array($course->id, $SESSION->bulk_courses)) {
                    $SESSION->bulk_courses[] = $course->id;
                }
            }
        }


    }
    $upt->close(); // close table

    $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != CC_COURSE_UPDATE) {
        echo get_string('coursescreated', 'tool_uploadcourse').': '.$coursesnew.'<br />';
    }
    if ($optype == CC_COURSE_UPDATE or $optype == CC_COURSE_ADD_UPDATE) {
        echo get_string('coursesupdated', 'tool_uploadcourse').': '.$coursesupdated.'<br />';
    }
    if ($allowdeletes) {
        echo get_string('coursesdeleted', 'tool_uploadcourse').': '.$deletes.'<br />';
        echo get_string('deleteerrors', 'tool_uploadcourse').': '.$deleteerrors.'<br />';
    }
    if ($allowrenames) {
        echo get_string('coursesrenamed', 'tool_uploadcourse').': '.$renames.'<br />';
        echo get_string('renameerrors', 'tool_uploadcourse').': '.$renameerrors.'<br />';
    }
    if ($coursesskipped) {
        echo get_string('coursesskipped', 'tool_uploadcourse').': '.$coursesskipped.'<br />';
    }
    echo get_string('errors', 'tool_uploadcourse').': '.$courseserrors.'</p>';
    echo $OUTPUT->box_end();

    if ($bulk) {
        echo $OUTPUT->continue_button($bulknurl);
    } else {
        echo $OUTPUT->continue_button($returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

// Print the header
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploadcoursespreview', 'tool_uploadcourse'));

// NOTE: this is JUST csv processing preview, we must not prevent import from here if there is something in the file!!
//       this was intended for validation of csv formatting and encoding, not filtering the data!!!!
//       we definitely must not process the whole file!

// preview table data
$data = array();
$cir->init();
$linenum = 1; //column header is first line
while ($linenum <= $previewrows and $fields = $cir->next()) {
    $linenum++;
    $rowcols = array();
    $rowcols['line'] = $linenum;
    foreach($fields as $key => $field) {
        $rowcols[$filecolumns[$key]] = s($field);
    }
    $rowcols['status'] = array();

    if (isset($rowcols['shortname'])) {
        $stdshortname = clean_param($rowcols['shortname'], PARAM_MULTILANG);
        if ($rowcols['shortname'] !== $stdshortname) {
            $rowcols['status'][] = get_string('invalidshortnameupload');
        }
        if ($courseid = $DB->get_field('course', 'id', array('shortname'=>$stdshortname))) {
            $rowcols['shortname'] = html_writer::link(new moodle_url('/course/view.php', array('id'=>$courseid)), $rowcols['shortname']);
        }
    } else {
        $rowcols['status'][] = get_string('missingshortname');
    }

    if (isset($rowcols['email'])) {
        if (!validate_email($rowcols['email'])) {
            $rowcols['status'][] = get_string('invalidemail');
        }
        if ($DB->record_exists('course', array('email'=>$rowcols['email']))) {
            $rowcols['status'][] = $stremailduplicate;
        }
    }

    if (isset($rowcols['city'])) {
        $rowcols['city'] = trim($rowcols['city']);
        if (empty($rowcols['city'])) {
            $rowcols['status'][] = get_string('fieldrequired', 'error', 'city');
        }
    }

    $rowcols['status'] = implode('<br />', $rowcols['status']);
    $data[] = $rowcols;
}
if ($fields = $cir->next()) {
    $data[] = array_fill(0, count($fields) + 2, '...');
}
$cir->close();

$table = new html_table();
$table->id = "ccpreview";
$table->attributes['class'] = 'generaltable';
$table->tablealign = 'center';
$table->summary = get_string('uploadcoursespreview', 'tool_uploadcourse');
$table->head = array();
$table->data = $data;

$table->head[] = get_string('cccsvline', 'tool_uploadcourse');
foreach ($filecolumns as $column) {
    $table->head[] = $column;
}
$table->head[] = get_string('status');

echo html_writer::tag('div', html_writer::table($table), array('class'=>'flexible-wrap'));

/// Print the form

$mform2->display();
echo $OUTPUT->footer();
die;


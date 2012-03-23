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
 * Strings for component 'tool_uploadcourse', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package    tool
 * @subpackage uploadcourse
 * @copyright  2011 Petr Skoda {@link http://skodak.org}
 * @copyright  2011 Piers Harding
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowdeletes'] = 'Allow deletes';
$string['allowrenames'] = 'Allow renames';
$string['csvdelimiter'] = 'CSV delimiter';
$string['defaultvalues'] = 'Default values';
$string['deleteerrors'] = 'Delete errors';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['pluginname'] = 'Course upload';
$string['renameerrors'] = 'Rename errors';
$string['requiredtemplate'] = 'Required. You may use template syntax here (%l = lastname, %f = firstname, %u = coursename). See help for details and examples.';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploadpicture_badcoursefield'] = 'The course attribute specified is not valid. Please, try again.';
$string['uploadpicture_cannotmovezip'] = 'Cannot move zip file to temporary directory.';
$string['uploadpicture_cannotprocessdir'] = 'Cannot process unzipped files.';
$string['uploadpicture_cannotsave'] = 'Cannot save picture for course {$a}. Check original picture file.';
$string['uploadpicture_cannotunzip'] = 'Cannot unzip pictures file.';
$string['uploadpicture_invalidfilename'] = 'Picture file {$a} has invalid characters in its name. Skipping.';
$string['uploadpicture_overwrite'] = 'Overwrite existing course pictures?';
$string['uploadpicture_coursefield'] = 'Course attribute to use to match pictures:';
$string['uploadpicture_coursenotfound'] = 'Course with a \'{$a->coursefield}\' value of \'{$a->coursevalue}\' does not exist. Skipping.';
$string['uploadpicture_courseskipped'] = 'Skipping course {$a} (already has a picture).';
$string['uploadpicture_courseupdated'] = 'Picture updated for course {$a}.';
$string['uploadpictures'] = 'Upload course pictures';
$string['uploadpictures_help'] = 'Course pictures can be uploaded as a zip file of image files. The image files should be named chosen-course-attribute.extension, for example course1234.jpg for a course with coursename course1234.';
$string['uploadcourses'] = 'Upload courses';
$string['uploadcourses_help'] = 'Courses may be uploaded (and optionally enrolled in courses) via text file. The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file
* Required fieldnames are coursename, password, firstname, lastname, email';
$string['uploadcoursespreview'] = 'Upload courses preview';
$string['uploadcoursesresult'] = 'Upload courses results';
$string['courseaccountupdated'] = 'Course updated';
$string['courseaccountuptodate'] = 'Course up-to-date';
$string['coursedeleted'] = 'Course deleted';
$string['courserenamed'] = 'Course renamed';
$string['coursescreated'] = 'Courses created';
$string['coursesdeleted'] = 'Courses deleted';
$string['coursesrenamed'] = 'Courses renamed';
$string['coursesskipped'] = 'Courses skipped';
$string['coursesupdated'] = 'Courses updated';
$string['coursenotadded'] = 'Course not added - already exists';
$string['coursenotaddederror'] = 'Course not added - error';
$string['coursenotdeletederror'] = 'Course not deleted - error';
$string['coursenotdeletedmissing'] = 'Course not deleted - missing';
$string['coursenotdeletedoff'] = 'Course not deleted - delete off';
$string['coursenotdeletedadmin'] = 'Course not deleted - no admin access';
$string['coursenotupdatederror'] = 'Course not updated - error';
$string['coursenotupdatednotexists'] = 'Course not updated - does not exist';
$string['coursenotupdatedadmin'] = 'Course not updated - no admin';
$string['coursenotrenamedexists'] = 'Course not renamed - target exists';
$string['coursenotrenamedmissing'] = 'Course not renamed - source missing';
$string['coursenotrenamedoff'] = 'Course not renamed - renaming off';
$string['coursenotrenamedadmin'] = 'Course not renamed - no admin';
$string['invalidvalue'] = 'Invalid value for field {$a}';
$string['shortnamecourse'] = 'Shortname';
$string['shortnamecourse_help'] = 'The short name of the course is displayed in the navigation. You may use template syntax here (%f = fullname, %i = idnumber), or enter an initial value that is incremented. See help for details and examples.';
$string['idnumbernotunique'] = 'idnumber is not unique';
$string['ccbulk'] = 'Select for bulk operations';
$string['ccbulkall'] = 'All courses';
$string['ccbulknew'] = 'New courses';
$string['ccbulkupdated'] = 'Updated courses';
$string['cccsvline'] = 'CSV line';
$string['cclegacy1role'] = '(Original Student) typeN=1';
$string['cclegacy2role'] = '(Original Teacher) typeN=2';
$string['cclegacy3role'] = '(Original Non-editing teacher) typeN=3';
$string['ccnoemailduplicates'] = 'Prevent email address duplicates';
$string['ccoptype'] = 'Upload type';
$string['ccoptype_addinc'] = 'Add all, append number to shortnames if needed';
$string['ccoptype_addnew'] = 'Add new only, skip existing courses';
$string['ccoptype_addupdate'] = 'Add new and update existing courses';
$string['ccoptype_update'] = 'Update existing courses only';
$string['ccpasswordcron'] = 'Generated in cron';
$string['ccpasswordnew'] = 'New course password';
$string['ccpasswordold'] = 'Existing course password';
$string['ccstandardshortnames'] = 'Standardise shortnames';
$string['ccupdateall'] = 'Override with file and defaults';
$string['ccupdatefromfile'] = 'Override with file';
$string['ccupdatemissing'] = 'Fill in missing from file and defaults';
$string['ccupdatetype'] = 'Existing course details';
$string['ccshortnametemplate'] = 'Shortname template';
$string['ccfullnametemplate'] = 'Fullname template';
$string['ccidnumbertemplate'] = 'Idnumber template';
$string['missingtemplate'] = 'Template not found';
$string['incorrecttemplatefile'] = 'Template file not found';
$string['coursetemplatename'] = 'Course template shortname';
$string['coursetemplatename_help'] = 'Select an existing course shortname to use as a template for the creation of all courses.';
$string['templatefile'] = 'Template backup file';
$string['invalidbackupfile'] = 'Invalid backup file';


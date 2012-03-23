moodle-tool_uploadcourse
========================

Is a Moodle admin/tools plugin for uploading course outlines
in much the same way that admin/tools/uploaduser works for users.
These plugins became available from Moodle 2.2x and onwards, as
this is when the admin/tools framework first appeared.

https://gitorious.org/moodle-tool_uploadcourse

This takes CSV files as input and enables override or augmentation
with default parameter values.

All the usual add,updated,rename, and delete functions.

CSV File format
===============

Possible column names are:
fullname, shortname, category, idnumber, summary, format, showgrades,
newsitems, 'teacher', 'editingteacher', 'student', 'manager',
'coursecreator', 'guest', 'user', startdate, numsections,
maxbytes, visible, groupmode, enrolperiod, groupmodeforce, metacourse,
lang, theme, cost, showreports, guest, enrollable, enrolstartdate,
enrolenddate, notifystudents, expirynotify, expirythreshold,
deleted,     // 1 means delete course
oldshortname, // for renaming
backupfile, // for restoring a course template after creation
templatename, // course to use as a template - the shortname

An example file is:

fullname,shortname,category,idnumber,summary,backupfile
Computer Science 101,CS101,Cat1,CS101,The first thing you will ever know,/path/to/backup-moodle2-course-cs101-20120213-0748-nu.mbz

Role Names
===========
 'teacher', 'editingteacher', 'student', 'manager',
'coursecreator', 'guest', 'user' are - where config permitting - you can
substitute your own name for these roles (string value).

Category
========
For category you must supply the category name as it is in Moodle and this
field is case sensitive.  If Sub Categories are involved then the full
category hierarchy needs to be specified as a '/' delimited string eg:
'Miscellaneous / Sub Cat / Sub Sub Cat'.

Startdate and Enrolstartdate
============================
For startdate and enrolstartdate, the values should be supplied in the form of
31.01.2012 or 31/01/2012.

Course Templating
=================
add column backupfile which has the fully qualified path name to a file on
the server that has a a Moodle course backup in it. 

Add a column templatename which is the shortname of an existing course that 
will be copied over the top of the new course.

Installation
=================
git clone this repository into <moodle root>/admin/tools/uploadcourse directory.

Point your browser at Moodle, and login as admin.  This should kick off
the upgrade so that Moodle can now recognise the new plugin.

This was inspired in part by a need for a complimentary function for uploading
courses (as for users) for the the NZ MLE tools for Identity and 
Access Managment (synchronising users with the School SMS):
https://gitorious.org/pla-udi
and
https://gitorious.org/pla-udi/mle_ide_tools

Copyright (C) Piers Harding 2011 and beyond, All rights reserved

moodle-tool_uploadcourse free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.


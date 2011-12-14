moodle-tool_uploadcourse
========================

Is a Moodle 2.x admin/tools plugin for uploading course outlines
in much the same way that admin/tools/uploaduser works for users.

https://gitorious.org/moodle-tool_uploadcourse

This takes CSV files as input and enables override or augmentation
with default parameter values.

All the usual add,updated,rename, and delete functions.

For category you must supply the category name as it is in Moodle and this
field is case sensitive.

For startdate and enrolstartdate, the values should be supplied in the form of
31.01.2012 or 31/01/2012.

unpack this archive, and ensure that you have a:
<moodle root>/admin/tools/uploadcourse directory.

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


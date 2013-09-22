MultiWikiMedia
==============

WikiMedia Multimedia programming assignment.


Usage Notes
-----------

### ImageMagick ###
The index.php script makes use of the Image Magick command line tools. Please make sure you have them installed on your system before beginning, and that they are pathed correctly.

### FileSystem ###
As a part of uploads, this script will write to the filesystem for storage. You can configure where this is by editing the `$CONFIG` global variable's `UPLOAD_DIR` property. Make sure that you pay attention to the permissions placed on this directory. If you run PHP in a user different than your own, you'll need to make this directory writeable before anything will work.

Goals
-----
 Create a small web service (no user interface necessary; POST from curl/wget is fine) with two APIs. One that accepts uploads and stores SVG, PNG, and JPEG files, and another that takes requests for a thumbnail (with format and size info) and converts, resizes, and displays the uploaded image as JPEG or PNG. Your code should shell out to ImageMagick to do the actual resizing. The focus of your application should be in excellent management of the upload process and on providing a sane service for automated use; a user interface is unnecessary.

Create a “TODO” list of things that would be important to complete in order to make your solution fully production ready (e.g. security and performance concerns).


TODOS
-----
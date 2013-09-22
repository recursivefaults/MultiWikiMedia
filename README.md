MultiWikiMedia
==============

WikiMedia Multimedia programming assignment.


Goals
-----
 Create a small web service (no user interface necessary; POST from curl/wget is fine) with two APIs. One that accepts uploads and stores SVG, PNG, and JPEG files, and another that takes requests for a thumbnail (with format and size info) and converts, resizes, and displays the uploaded image as JPEG or PNG. Your code should shell out to ImageMagick to do the actual resizing. The focus of your application should be in excellent management of the upload process and on providing a sane service for automated use; a user interface is unnecessary.

Create a “TODO” list of things that would be important to complete in order to make your solution fully production ready (e.g. security and performance concerns).


TODOS
-----
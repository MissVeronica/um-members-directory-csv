# UM Members Directory CSV
Extension to Ultimate Member for defining Members Directory primary user list order from a spreadsheet saved as a CSV file.

Either a CSV file with comments for each user ID if the priotity list is very long. The user management of the list is faster and easier with spreadsheet input.
An export plugin can be used for downloading the User IDs and username if required.

A short priority list can be managed within UM settings with the same format as the CSV in a textarea.

## UM Settings -> General -> Users
1. Members Directory CSV - Form name - Select the Member Directory Form name for Primary/Secondary User listing
2. Members Directory CSV - User IDs input - Select the Member Directory input from CSV file name or Textbox for primary user IDs listing order
3. Members Directory CSV - Primary Order User IDs by CSV file - Enter the Member Directory CSV file name for primary user listing order
4. Members Directory CSV - Primary Order User IDs by UM list - Enter the Member Directory list for Primary User listing order. One user ID per line with optional username and comment separated by comma or semicolon
5. Members Directory CSV - Secondary Order sorting Users by - Select the Member Directory Secondary sorting order except Primary Users

## CSV File format
1. First column User ID, second column your comments like Username etc
2. Only first column used by this plugin for the Primary Directory sort order
3. Column separator semicolon or comma
4. Export User list with for example the "Export User Data" plugin to your spreadsheet application. https://wordpress.org/plugins/export-user-data/
5. CSV File uploads folder: .../wp-content/uploads/ultimatemember/
6. Use a FTP client or FileManager for the upload.

## UM Textarea format
1. Same format as CSV file ie two columns and one User ID and your comment one user per line

## UM Members Directory settings
1. Createa new Members Directory or use an existing Directory
2. Select "Primary Users list from CSV File or UM settings" as "Default sort users by"
3. Configure other UM settings for your Directory: List/Grid, User Profile card etc

## Translations or Text changes
1. Use the "Say What?" plugin with text domain ultimate-member
2. https://wordpress.org/plugins/say-what/

## Updates
None

## Installation
1. Install by downloading the plugin ZIP file and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Members Directory CSV

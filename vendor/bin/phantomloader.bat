@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../jonnyw/php-phantomjs/bin/phantomloader
php "%BIN_TARGET%" %*

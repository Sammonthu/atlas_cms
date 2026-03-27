@echo off
REM D?finit le chemin vers le php.ini local du projet Atlas CMS.
set "ATLAS_PHP_INI=%~dp0..\config\php\php-cli-atlas.ini"
REM Ex?cute la console Symfony avec le php.ini local qui active pdo_mysql.
php -c "%ATLAS_PHP_INI%" "%~dp0console" %*

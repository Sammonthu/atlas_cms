@echo off
REM D?finit le chemin du r?pertoire courant du script.
set "SCRIPT_DIR=%~dp0"
REM D?finit le chemin du php.ini local avec pdo_mysql activ?.
set "ATLAS_PHP_INI=%SCRIPT_DIR%..\config\php\php-cli-atlas.ini"
REM D?finit l'h?te local de d?veloppement.
set "ATLAS_HOST=127.0.0.1"
REM D?finit le port HTTP local de d?veloppement.
set "ATLAS_PORT=8000"
REM Lance le serveur PHP natif avec le php.ini local Atlas.
php -c "%ATLAS_PHP_INI%" -S %ATLAS_HOST%:%ATLAS_PORT% -t public

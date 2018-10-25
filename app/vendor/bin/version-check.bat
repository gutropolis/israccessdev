@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../jeremykendall/password-validator/bin/version-check
php "%BIN_TARGET%" %*

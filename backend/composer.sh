#!/bin/sh
export PATH="/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH"
exec "C:/ProgramData/ComposerSetup/bin/composer.bat" "$@"

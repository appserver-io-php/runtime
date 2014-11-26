
:: This file builds PHP along with any configured extensions.
:: It depends on a configured SDK.
:: @see https://wiki.php.net/internals/windows/stepbystepbuild
::
:: Reason for this script is that there is no good way to work with the
:: VC command prompt from within Ant
::
:: @author Bernhard Wick b.wick@techdivision.com

:: Call the Visual Studio command prompt to set our environment
cd "${win.native-tools-cmd.dir}"
call "vcvarsall.bat" ${win.os.architecture}

:: Go back to the PHP SDK dir and set the environment there
cd "${win.php-sdk.dir}\bin"
call "phpsdk_setvars.bat"
call "phpsdk_buildtree.bat" phpdev

:: Go to the PHP src dir and do the actual building
cd "${win.php-buildtree.dir}\php-${runtime.php.version}"
call "buildconf"
call "configure" ${win.binaries.config-string}
call "nmake"


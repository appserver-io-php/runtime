
:: This file prepares the PHP SDK as described in the link below.
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

:: Trigger buildtree buildup
start call "phpsdk_buildtree.bat" phpdev
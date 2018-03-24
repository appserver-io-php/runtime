# Version 1.1.6

## Bugfixes

* None

## Features

* Switch to PHP 5.6.34

# Version 1.1.5

## Bugfixes

* None

## Features

* Switch to PHP 5.6.23
* Set always_populate_raw_post_data to -1 in all PHP ini files

# Version 1.1.4

## Bugfixes

* None

## Features

* Switch to PHP 5.6.20
* Remove libmemcache and PECL extension memcached from build process

# Version 1.1.3

## Bugfixes

* None

## Features

* Switch to OpenSSL 1.0.1s

# Version 1.1.2

## Bugfixes

* None

## Features

* Switch to PHP 5.6.19

# Version 1.1.1

## Bugfixes

* Add --enable-rpath configuration parameter when building libicu to add hardcoded dependencies (Mac OS X)

## Features

* Switch to PHP 5.6.17
* Enable intl extension by default on Linux
* Raise default value for max_execution_time in php-fpm-fcgi.ini from 30 to 300, e. g. to don't break Magento 2 installation

# Version 1.1.0

## Bugfixes

* None

## Features

* Switch to PHP 5.6.16
* Enable intl extension by default on Mac OS X

# Version 1.1.0-alpha2

## Bugfixes

* Remove unnecessary PHP 5.5 libs and binaries
* Fixed [#701](https://github.com/appserver-io/appserver/issues/701) - composer.phar is missing in Mac OS X/Linux build

## Features

* Unified build artifact placement

# Version 1.1.0-alpha1

## Bugfixes

* None

## Features

* Switched to PHP 5.6

# Version 1.0.4

## Bugfixes

* Fixed a naming bug by renaming Raspbian packages which where misspelled

## Features

* None

# Version 1.0.3

## Bugfixes

* Added "make test" testing of built libraries

## Features

* None

# Version 1.0.2

## Bugfixes

* Fixed [#618](https://github.com/appserver-io/appserver/issues/618) - Segfault on Mac OS X when restarting after a new installation

## Features

* None

# Version 1.0.1

## Bugfixes

* Fixed [#612](https://github.com/appserver-io/appserver/issues/612) - php_opcache.dll failed to load if installed in non default path (Windows)

## Features

* Closed [#593](https://github.com/appserver-io/appserver/issues/593) - Update PHP/PECL version + upload_tmp_dir configuration directive

# Version 1.0.0

## Bugfixes

* None

## Features

* Switched to stable dependencies due to version 1.0.0 release

# Version 1.0.0-rc1

## Bugfixes

* None

## Features

* Add release.version property to build.default.properies
* Remove unnecessary opcache-blacklist.txt from var/tmp directory and referencing .ini files

# Version 1.0.0-beta3

## Bugfixes

* None

## Features

* Add missing FPM php.ini files for Windows/Linux builds

# Version 1.0.0-beta2

## Bugfixes

* None

## Features

* Switch to PHP 5.5.19
* Switch pthreads-1.0.5
* Add preparation of pthreads sources for Windows build
* Fixed #235 - Add separate php.ini for PHP-FPM

# Version 1.0.0-alpha

## Bugfixes

* None

## Features

* Move var/tmp/opcache-blacklist.txt to from build
* Switch to build process on new CI server
* Switch Windows binaries php directory instead of bin
* Apps included in a default environment will now be downloaded in their latest, or a specified version

# Version 1.0.0

## Bugfixes

* Make upload_tmp_dir relative application server base directory and set default value to var/tmp for UNIX/Windows systems
* Add --with-mysql-sock=/var/run/mysqld/mysqld.sock to configure statement for linux build in build.linux.properties
* Set sendmail_path = /usr/sbin/sendmail -t -i as default for Linux/Mac OS X in etc/php.ini
* Bugfix for message "start-stop-daemon: warning: this system is not able to track process names longer than 15 characters, please use --exec instead of --name." in PHP-FPM start/stop script

## Features

* Add simple test script var/www/sendmail-test.php to test sending mails
* Update PHP to version 5.5.16
* Add more detailed description in Debian Linux control file under buildfiles/debian/DEBIAN/control
* Add possibility to set custom status code for redirects in techdivision/rewritemodule package => analog apache mod_rewrite
* Add support for relative redirects in techdivision/rewritemodule package => analog apache mod_rewrite

# Version 0.9.1

## Bugfixes

* None

## Features

* Allow bean lookup with short class name only
* Now use InitialContext class for bean lookup in techdivision/persistencecontainer and techdivision/messagequeue package

# Version 0.9.0

## Bugfixes

* Replace BeanContext::class with class name for PHP 5.4 compatibility in package techdivision/persistencecontainerprotocol

## Features

* Close Timer service #185
* Add new package techdivision/naming to allow JNDI like bean lookup
* Messages of techdivision/messagequeueprotocol now implements \Serializable interface
* Switch to new version of techdivision/properties providing interface PropertiesInterface
* Add userland interfaces for reflection annotation, class and method in package techdivision/lang
* String, Integer, Float and Boolean class now implements \Serializable interface in package techdivision/lang
* Switch from \Stackable to GenericStackable when extending QueueManager in package techdivision/messagequeue
* Refactoring + Optimizing package techdivision/persistencecontainerclient for usage with techdivision/naming package
* Make techdivision/enterprisebeans part of the techdivision/persistencecontainer package (not a require-dev dependency only)
* Remove BeanContext::getBeanAnnotation() method => method has been moved to BeanUtils in package techdivision/persistencecontainerprotocol

# Version 0.8.4

## Bugfixes

* None

## Features

* Switch to beta status for 0.8.x version
* Switch to new TechDivision_PersistenceContainer implementation supporting @Startup, @PostConstruct and @PreDestruct functionality for session beans

# Version 0.8.3

## Bugfixes

* None

## Features

* Add xsl + bcmath extension + .ini files to builds
* Add new configuration nodes to allow configuration of extractors + provisioners incl. XSD schema validation
* Remove configuration for context, loggers, extractors + provisioners from appserver.xml because of default programmatical values

# Version 0.8.2

## Bugfixes

* Bugfix invalid path to appserver.xml in DEBIAN conffiles
* Bugfix schema validation when calling copy-runtime target

## Features

* Restructure README.md, switch ANT project name to techdivision/runtime
* Switch to new [TechDivision_ApplicationServer](https://github.com/techdivision/TechDivision_ApplicationServer) version 0.9.*
* [Issue #178](https://github.com/techdivision/TechDivision_ApplicationServer/issues/178) App-based context configuration
* Use DirectoryKeys to create path to appserver.xml + appserver.xsd in server.php
* Create new configuration directory structure etc/appserver + etc/appserver/conf.d
* Move appserver.xml to new configuration directory etc/appserver and context.xml to etc/appserver/conf.d

# Version 0.8.1

## Bugfixes

* Set environment variable LOGGER_ACCESS=Access in all servers

## Features

* Switch from techdivision/appserver minor version 0.7.* to 0.8.*
* Switch from techdivision/persistencecontainer minor version 0.7.* to 0.8.*
* Switch from techdivision/messagequeue minor version 0.6.* to 0.7.*
* Switch from techdivision/servletengine minor version 0.6.* to 0.7.*
* Switch from techdivision/websocketserver minor version 0.2.* to 0.3.*
* Add latest admin.phar and example.phar
* Add default context.xml configuration to etc/appserver.d directory
* Add mandatory name attribute for servers in XSD schema
* Replace vhost node with context node in XSD schema

%define         _unpackaged_files_terminate_build 0
%define        __spec_install_post %{nil}
%define          debug_package %{nil}
%define        __os_install_post %{_dbpath}/brp-compress

Name:		appserver
Version:	${appserver.version}
Release:	${appserver.version.suffix}${build.number}.${os.qualified.name}
Summary:	Multithreaded Application Server für PHP, geschrieben in PHP
Group:		System Environment/Base
License:	OSL 3.0
URL:		www.appserver.io
requires:   git, libmcrypt
Provides:   appserver

%description
%{summary}

%prep

%build

%clean

%files
/opt/appserver/*
/etc/init.d/*

%changelog

%post
# Reload shared library list
ldconfig

# Set needed files as accessable for the configured user
chown -R ${appserver.user}:${appserver.group} /opt/appserver/var
chown -R ${appserver.user}:${appserver.group} /opt/appserver/webapps
chown -R ${appserver.user}:${appserver.group} /opt/appserver/deploy

# Create composer symlink
ln -s /opt/appserver/bin/composer.phar /opt/appserver/bin/composer

# Set the permissions
chmod 775 /etc/init.d/appserver
chmod 775 /etc/init.d/appserver-watcher
chmod 775 /etc/init.d/appserver-php5-fpm

/etc/init.d/appserver start
/etc/init.d/appserver-watcher start
/etc/init.d/appserver-php5-fpm start
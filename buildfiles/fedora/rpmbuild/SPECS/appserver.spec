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
/lib/systemd/system/*

%changelog

%post
# Reload shared library list
ldconfig

# Set needed files as accessable for the configured user
chown -R ${appserver.user}:${appserver.group} /opt/appserver/var
chown -R ${appserver.user}:${appserver.group} /opt/appserver/webapps
chown -R ${appserver.user}:${appserver.group} /opt/appserver/deploy

# Make the link to our system systemd file
ln -s /lib/systemd/system/appserver.service /etc/systemd/system/appserver.service
ln -s /lib/systemd/system/appserver-watcher.service /etc/systemd/system/appserver-watcher.service
ln -s /lib/systemd/system/appserver-php5-fpm.service /etc/systemd/system/appserver-php5-fpm.service

# Create composer symlink
ln -s /opt/appserver/bin/composer.phar /opt/appserver/bin/composer

# Reload the systemd daemon
systemctl daemon-reload

# Start the appserver + watcher
systemctl start appserver.service
systemctl start appserver-watcher.service
systemctl start appserver-php5-fpm.service

# Make them autostartable for the current runlevel
systemctl enable appserver.service
systemctl enable appserver-watcher.service
systemctl enable appserver-php5-fpm.service
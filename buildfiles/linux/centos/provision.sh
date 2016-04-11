#!/bin/sh

# install build dependencies
yum -y install epel-release;
yum -y install git autoconf curl bison libxml2 libxml2-devel openssl-devel bzip2-devel libjpeg-turbo-devel libpng-devel freetype-devel libmcrypt-devel libXpm-devel pcre-devel libpng-devel libcurl-devel libevent-devel gcc-c++ openldap-devel libicu-devel libxslt-devel;

# get the latest version of ant
wget -q ${ant.download.url};
tar -xzf ./${ant.package.name}-bin${ant.package.extension} -C ${ant.vagrant.basedir} >> /dev/null;
sudo ln -sf ${ant.vagrant.basedir}/${ant.package.name}/bin/ant /usr/bin/ant;

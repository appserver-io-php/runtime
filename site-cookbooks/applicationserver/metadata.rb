name             'applicationserver'
maintainer       'Robert Lemke'
maintainer_email 'rl@robertlemke.com'
license          'All rights reserved'
description      'Installs/Configures applicationserver'
long_description IO.read(File.join(File.dirname(__FILE__), 'README.md'))
version          '0.1.0'

depends 'php'
depends 'apache2'
depends 'database'
depends 'mysql'
depends 'git'
depends 'logrotate'

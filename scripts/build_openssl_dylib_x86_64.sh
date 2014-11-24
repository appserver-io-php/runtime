#!/bin/bash
OPENSSL_VERSION="${openssl.version}"

tar -xvzf ../lib/openssl-$OPENSSL_VERSION.tar.gz
cd openssl-$OPENSSL_VERSION
./Configure darwin64-x86_64-cc -shared --prefix=/opt/appserver
make && make install
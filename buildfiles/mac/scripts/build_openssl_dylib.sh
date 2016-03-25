#!/bin/bash
OPENSSL_VERSION="${openssl.version}"
 
#curl -O http://www.openssl.org/source/openssl-$OPENSSL_VERSION.tar.gz
tar -xvzf ../lib/openssl-$OPENSSL_VERSION.tar.gz
mv openssl-$OPENSSL_VERSION openssl_i386
tar -xvzf ../lib/openssl-$OPENSSL_VERSION.tar.gz
mv openssl-$OPENSSL_VERSION openssl_x86_64
cd openssl_i386
./Configure darwin-i386-cc -shared --prefix=${runtime.compile.prefix}
make
cd ../
cd openssl_x86_64
./Configure darwin64-x86_64-cc -shared --prefix=${runtime.compile.prefix}
make
cd ../
lipo -create openssl_i386/libcrypto.1.0.0.dylib openssl_x86_64/libcrypto.1.0.0.dylib -output libcrypto.1.0.0.dylib
lipo -create openssl_i386/libssl.1.0.0.dylib openssl_x86_64/libssl.1.0.0.dylib -output libssl.1.0.0.dylib
#rm openssl-$OPENSSL_VERSION.tar.gz
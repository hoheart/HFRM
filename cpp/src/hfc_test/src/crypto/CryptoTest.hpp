#ifndef __CRYPTO_TEST_HPP__
#define __CRYPTO_TEST_HPP__

#include "../ITest.hpp"

#include "Md5EncryptionTest.hpp"
#include "RsaEncryptionTest.hpp"

class CryptoTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new Md5EncryptionTest() , new RsaEncryptionTest() };

		for( int i = 0; i < sizeof(a) / sizeof(a[0]) ; ++ i ){
			if( ! a[i]->test() ){
				logError( "\r\n************************************\r\n" );
				return false;
			}
		}

		return true;
	}
};

#endif
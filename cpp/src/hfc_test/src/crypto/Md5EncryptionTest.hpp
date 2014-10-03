#ifndef __MD5_ENCRYPTION_TEST_HPP__
#define __MD5_ENCRYPTION_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/crypto/Md5Encryption.hpp"
using namespace hfc;
using namespace hfc::crypto;

class Md5EncryptionTest : public ITest{

public :

	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: Md5Encryption error." );
		
		return false;
	}
	
	/**
	* 在还没有线程池的情况下，这个得手工测
	*/
	bool testAll(){
		Md5Encryption e;
		const char* szPlainText = "asdfllk+90d";
		String s = e.encrypt(  szPlainText , String::Strlen(szPlainText) );
		
		char tmp[3] = { 0 };
		String ret;
		for (int i = 0; i < 16; i++) {
			unsigned char c = s.charAt(i);
			sprintf(tmp, "%2.2x", c);
			ret += tmp;
		}
		if( "f397f86f507b9f2a83d8d4291447206b" != ret ){
			return false;
		}

		return true;
	}

	


};


#endif
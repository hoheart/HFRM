#ifndef __RSA_ENCRYPTION_TEST_HPP__
#define __RSA_ENCRYPTION_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/crypto/RsaEncryption.hpp"
using namespace hfc;
using namespace hfc::net;

class RsaEncryptionTest : public ITest{

public :

	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: RsaEncryption error." );
		
		return false;
	}


	bool testAll(){
		String strPlainText("a\0bcdefgh" , 9 );
		if( ! testCore( strPlainText )){
			return false;
		}

		String strPlainText1( "asdfasdfkjalsjflsakjfaslfdjalskjfsalkfdjsaldfkjsaljf;oiuwperou2u4oiusfnnvasdfj"
			"asdfjlasjfdlasdkjfoueurhjsdnfnksandfksahfouoewurowjasnflasnfdlaksdfjuoiuoasfdasdfjojlk"
			"1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz"
			"1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1111113132139808");
		if( ! testCore( strPlainText1 )){
			return false;
		}

		return true;
	}
	
	/**
	* 在还没有线程池的情况下，这个得手工测
	*/
	bool testCore( const String& strPlainText ){
		RsaEncryption e;

		char* szPublicKey = "-----BEGIN PUBLIC KEY-----\n"
			"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDgr6OOWw6ht136yyOT6wGCbky5\n"
			"Fk7IaR7rfaDPfFQ01io9DXmkM0sMKRYiLi7S8Nur+yqnco0tTPgAaIZgSqqFG2cp\n"
			"3twWeD3aXx/7tjThbjDARz+7qUVcoRG11pxxgUCgZP+QILNmqJ7qc5Q6fKY8Pi1X\n"
			"s9CiiEyAF2+oKeh4DQIDAQAB\n"
			"-----END PUBLIC KEY-----";
		e.setPublicKey( szPublicKey , String::Strlen(szPublicKey) );

		char* szPrivateKey = "-----BEGIN RSA PRIVATE KEY-----\n"
			"MIICXAIBAAKBgQDgr6OOWw6ht136yyOT6wGCbky5Fk7IaR7rfaDPfFQ01io9DXmk\n"
			"M0sMKRYiLi7S8Nur+yqnco0tTPgAaIZgSqqFG2cp3twWeD3aXx/7tjThbjDARz+7\n"
			"qUVcoRG11pxxgUCgZP+QILNmqJ7qc5Q6fKY8Pi1Xs9CiiEyAF2+oKeh4DQIDAQAB\n"
			"AoGBAJ0x5LDk/fCgVGzxEFwdj7nMJ3taoxXNjeddxlNDfbpVl8mNEuyRQlEosaaK\n"
			"PxIvltDU8XujRihKcDwA5PqbYn5bqmY3bd9Be0yMopOTNXChdemdVoYvNaKOConr\n"
			"9m7sRW3+2zcCIJ9FsE4qVYMqHnjq2zLhzo9Cxa9J8eaemJ0VAkEA/Jpe6kct1rlv\n"
			"CrciLH8wXO+Ae9DemmjTChBJMhW4dGWXtlKh2Gv3cU0c0EjhWx91EYeqXjwtmw81\n"
			"3FdQQ7YiFwJBAOO1KMoR5fXLSyRxVc/xJ5zVm7jrBaZwH0kcxYgZhQmurE+Bxzg6\n"
			"putBRwsP0mFIw5rWrXfgK+l6AKDTz41cAXsCQDgcmoT0NsyB+Jaqw56aykV6tlHK\n"
			"vggSzP5pS7kidgz1HFRV2rnWkerLNGFYLycsv7abKk8wkvjk0hQWGTBRXxUCQFR/\n"
			"5aMXAT81Ma1UYYPI+eo5WpBEptQcx39aLfNThQsSxt66SjOTkF2826A/PtLbLbtH\n"
			"7xeNGL6Lh7Am6d6LRgECQB1XjXSO4MgyUgl55Gjb0sFWKNv+HzADJRY19qmEN/Uc\n"
			"gkSrJ+16vV/sSuwAp4IEXtQd64dlBmR8NaCNRglWzBw=\n"
			"-----END RSA PRIVATE KEY-----";
		e.setPrivateKey( szPrivateKey , String::Strlen(szPrivateKey) );

		String strPubEn = e.publicEncrypt( strPlainText );		
		String strPriDe = e.privateDecrypt( strPubEn );
		if( strPriDe != strPlainText ){
			return false;
		}

		String strPriEn = e.privateEncrypt( strPlainText );
		String strPubDe = e.publicDecrypt( strPriEn );
		if( strPlainText != strPubDe ){
			return false;
		}
		

		return true;
	}

	


};


#endif
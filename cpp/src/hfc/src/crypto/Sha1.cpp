#include "../../include/crypto/Sha1.hpp"
using namespace hfc::crypto;

#include <openssl/sha.h>

Sha1::Sha1()
{
	
}

Sha1::~Sha1()
{
	
}

String Sha1::Encrypt( const String::t_char* szSrc , const int iLen ){
	//ctx的初始化参照php-5.3.13，保证和php的输出一致
	SHA_CTX ctx = {0x67452301 , 0xefcdab89 , 0x98badcfe , 0x10325476 , 0xc3d2e1f0 , 0 , 0 , 0 };
	SHA1_Init(&ctx);
	SHA1_Update(&ctx , szSrc , iLen );

	unsigned char digest[20];
	SHA1_Final(digest ,&ctx);


	String ret( (char*)digest , sizeof(digest));

	return ret;
}

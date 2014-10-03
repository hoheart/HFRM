#ifndef __RSA_HPP__
#define __RSA_HPP__

#include "../hfc_def.hpp"

#include <openssl/rsa.h>

#include "../io/File.hpp"
using namespace hfc::io;

namespace hfc {
namespace crypto {

/**
* 该类采用PKCS #1 v1.5 padding。
* 
1 生成RSA私钥：
openssl genrsa -out rsa_private_key.pem 1024

2 生成RSA公钥
openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem

3 将RSA私钥转换成PKCS8格式
openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out rsa_pub_pk8.pem
*/
class HFC_API Rsa {

public:

	Rsa();

	virtual ~Rsa();

public:
	
	void setPublicKey(const String::t_char* szKey , const int iKeyLen );
	void setPrivateKey(const String::t_char* szKey , const int iKeyLen );
	
	String publicEncrypt(const String& src) const {
		return operation( PublicEncrypt , src );
	}
	
	String privateDecrypt(const String& src) const {
		return operation( PrivateDecrypt , src );
	}
	
	String privateEncrypt( const String& src ) const {
		return operation( PrivateEncrypt , src );
	}
	
	String publicDecrypt(const String& src) const {
		return operation( PublicDecrypt , src );
	}

protected:

	typedef enum RsaOpType {
		PublicEncrypt, PrivateDecrypt , PrivateEncrypt , PublicDecrypt
	} RsaOpType;

protected:

	int rsaOperation( RsaOpType opType , const String& src , unsigned char* out ) const ;
	String operation( RsaOpType opType , const String& src ) const ;

protected:

	RSA* m_pPrivateRsa;
	RSA* m_pPublicRsa;

};

}
}
#endif

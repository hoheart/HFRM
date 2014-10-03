#include "../../include/crypto/Rsa.hpp"
using namespace hfc::crypto;

#include <openssl/rsa.h>
#include <openssl/pem.h>
#include <openssl/evp.h>

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::io;

Rsa::Rsa() : m_pPublicRsa(NULL) , m_pPrivateRsa(NULL){
}

Rsa::~Rsa() {
}

void Rsa::setPublicKey(const String::t_char* szKey , const int iKeyLen ) {
	BIO* pBio = BIO_new_mem_buf( (void*)szKey , iKeyLen );
	EVP_PKEY* pKey = PEM_read_bio_PUBKEY(pBio , NULL , NULL , NULL );

	if( NULL != m_pPublicRsa ){
		RSA_free( m_pPublicRsa );
	}
	m_pPublicRsa = EVP_PKEY_get1_RSA( pKey );
	if (m_pPublicRsa == NULL) {
		throw InvalidParameterException();
	}

	EVP_PKEY_free( pKey );
	BIO_vfree(pBio);
}

void Rsa::setPrivateKey(const String::t_char* szKey , const int iKeyLen ) {
	BIO* pBio = BIO_new_mem_buf( (void*)szKey , iKeyLen );
	if( NULL != m_pPrivateRsa ){
		RSA_free( m_pPrivateRsa );
	}
	m_pPrivateRsa = PEM_read_bio_RSAPrivateKey(pBio, NULL, NULL, NULL);
	if (m_pPrivateRsa == NULL) {
		throw InvalidParameterException();
	}

	BIO_vfree(pBio);
}

String Rsa::operation( RsaOpType opType , const String& src ) const {
	int iMaxLenOnce = 0;
	String desc;
	int iLen = src.length();
	unsigned char* out;
	int iOutLen;

	switch( opType ){
	case PublicEncrypt:
		iOutLen = RSA_size( m_pPublicRsa );
		iMaxLenOnce = iOutLen - 11;//因为都用RSA_PKCS1_PADDING，所以-11，参看文档
		
		break;
	case PrivateDecrypt:
		iOutLen = RSA_size( m_pPrivateRsa );
		iMaxLenOnce = iOutLen;
		break;
	case PrivateEncrypt:
		iOutLen = RSA_size( m_pPrivateRsa );
		iMaxLenOnce = iOutLen - 11;
		break;
	case PublicDecrypt:
		iOutLen = RSA_size( m_pPublicRsa );
		iMaxLenOnce = iOutLen;
		break;
	}

	out = new unsigned char[iOutLen];
	for (int i = 0; i < ( iLen + iMaxLenOnce - 1 ) / iMaxLenOnce; ++i) {
		int iProcessLen = iMaxLenOnce;
		int iLeftLen = iLen - (i * iMaxLenOnce );
		if( iProcessLen > iLeftLen ){
			iProcessLen = iLeftLen;
		}
		String e = src.substr(i * iMaxLenOnce, iProcessLen);
		const void* p = e;
		
		int ret = rsaOperation( opType , e , out );
		if (ret < 0) {
			delete out;
			throw Exception("private decrypt error.");
		}

		desc.append((const char*) out, ret);
	}
	delete out;

	return desc;
}

int Rsa::rsaOperation( RsaOpType opType , const String& src , unsigned char* out ) const {
	const char* p = src;
	RSA* pKey = NULL;

	switch( opType ){
	case PublicEncrypt:
		pKey = m_pPublicRsa;
		return RSA_public_encrypt(src.length(), (const unsigned char*) p,
				out, pKey, RSA_PKCS1_PADDING);
		break;
	case PrivateDecrypt:
		pKey = m_pPrivateRsa;
		return RSA_private_decrypt(src.length(), (const unsigned char*) p,
				out, pKey, RSA_PKCS1_PADDING);
		break;
	case PrivateEncrypt:
		pKey = m_pPrivateRsa;
		return RSA_private_encrypt(src.length(), (const unsigned char*) p,
				out, pKey, RSA_PKCS1_PADDING);
		break;
	case PublicDecrypt:
		pKey = m_pPublicRsa;
		return RSA_public_decrypt(src.length(), (const unsigned char*) p,
				out, pKey, RSA_PKCS1_PADDING);
		break;
	}

	return -1;
}



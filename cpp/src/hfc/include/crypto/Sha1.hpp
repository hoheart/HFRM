#ifndef __SHA1_HPP__
#define __SHA1_HPP__

#include "../hfc_def.hpp"

#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc{
namespace crypto{

class HFC_API Sha1  
{

public:

	Sha1();
	virtual ~Sha1();

public:

	/**
	* 与函数采取的SHA_CTX初始值与php-5.3.13一致，所以，输出也与php-5.3.13的一致。
	* 加密之后返回的是20个没有进行16进制编码的字符。可以用
	* char tmp[3] = { 0 };
	* String ret;
	* for (int i = 0; i < 20; i++) {
	* 	sprintf(tmp, "%2.2x", md[i]);
	* 	ret += tmp;
	* }
	* 进行转换。
	*/
	static String Encrypt(const String::t_char* szSrc , const int iLen );

};

}
}

#endif // !defined(AFX_SHA1_HPP__8168AFF5_BA34_4F95_AB32_E306B860B5D2__INCLUDED_)

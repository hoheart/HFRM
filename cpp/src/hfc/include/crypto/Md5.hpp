#ifndef __MD5_HPP__
#define __MD5_HPP__

#include "../hfc_def.hpp"

#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc {
namespace crypto {

class HFC_API Md5 {

public:

	Md5();

	virtual ~Md5();

public:

	/**
	* 该函数与php结果一致。
	*
	* 加密之后返回的是16个没有进行16进制编码的字符。可以用
	* char tmp[3] = { 0 };
	* String ret;
	* for (int i = 0; i < 16; i++) {
	* 	sprintf(tmp, "%2.2x", md[i]);
	* 	ret += tmp;
	* }
	* 进行转换。
	*/
	static String Encrypt(const String::t_char* szSrc , const int iLen );


};

}
}
#endif

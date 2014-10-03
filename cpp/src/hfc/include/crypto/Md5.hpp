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
	* �ú�����php���һ�¡�
	*
	* ����֮�󷵻ص���16��û�н���16���Ʊ�����ַ���������
	* char tmp[3] = { 0 };
	* String ret;
	* for (int i = 0; i < 16; i++) {
	* 	sprintf(tmp, "%2.2x", md[i]);
	* 	ret += tmp;
	* }
	* ����ת����
	*/
	static String Encrypt(const String::t_char* szSrc , const int iLen );


};

}
}
#endif

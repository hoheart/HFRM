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
	* �뺯����ȡ��SHA_CTX��ʼֵ��php-5.3.13һ�£����ԣ����Ҳ��php-5.3.13��һ�¡�
	* ����֮�󷵻ص���20��û�н���16���Ʊ�����ַ���������
	* char tmp[3] = { 0 };
	* String ret;
	* for (int i = 0; i < 20; i++) {
	* 	sprintf(tmp, "%2.2x", md[i]);
	* 	ret += tmp;
	* }
	* ����ת����
	*/
	static String Encrypt(const String::t_char* szSrc , const int iLen );

};

}
}

#endif // !defined(AFX_SHA1_HPP__8168AFF5_BA34_4F95_AB32_E306B860B5D2__INCLUDED_)

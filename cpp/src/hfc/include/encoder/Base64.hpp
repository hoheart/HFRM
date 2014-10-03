#ifndef __BASE64_HPP__
#define __BASE64_HPP__

#include "../hfc_def.hpp"

#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc{
namespace encoder{

/**
* 用php-5.3.13的base函数改写。
*/
class HFC_API Base64
{

public:

	Base64();
	virtual ~Base64();

protected:

	static const char base64_table[];

	static const char base64_pad;

	static const short base64_reverse_table[256];

public :

	static String Encode( const String& str );

	static String Decode( const String& str );
	
};
		
}
}

#endif

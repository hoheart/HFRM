#ifndef __HEX_STRING_HPP__
#define __HEX_STRING_HPP__

#include "../hfc_def.hpp"

#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc{
namespace encoder{

/**
* 用php-5.3.13的base函数改写。
*/
class HFC_API HexString
{

public:

	HexString(){}
	virtual ~HexString(){}


public :

	static String Encode( const String& str );

	static String Decode( const String& str );
	
};
		
}
}

#endif

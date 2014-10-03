#ifndef __OS_ERROR_EXCEPTION_HPP__
#define __OS_ERROR_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace util{
	
	/**
	* 操作系统错误，调用操作系统API是出错。
	*/
	class OSErrorException: public Exception {
		
	public:
		
		OSErrorException(const String& errstr) {
			m_iErrno = OSError;
			m_strErrstr = errstr;
		}
	};
	
}
}
#endif

#ifndef __OS_ERROR_EXCEPTION_HPP__
#define __OS_ERROR_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace util{
	
	/**
	* ����ϵͳ���󣬵��ò���ϵͳAPI�ǳ���
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

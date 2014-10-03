#ifndef __INVALID_PARAMETER_EXCEPTION_HPP__
#define __INVALID_PARAMETER_EXCEPTION_HPP__

#include "Exception.hpp"
using namespace hfc;

#include "../Error.hpp"

namespace hfc {
namespace lang{

	class InvalidParameterException: public Exception {

	public:

		InvalidParameterException( ) {
			m_iErrno = InvalidParameter;
		}
	};
	
}
}
#endif

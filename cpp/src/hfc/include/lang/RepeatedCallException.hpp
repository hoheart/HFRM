#ifndef __REPEATED_CALL_EXCEPTION_HPP__
#define __REPEATED_CALL_EXCEPTION_HPP__

#include "Exception.hpp"
using namespace hfc;

#include "error.hpp"

namespace hfc {
namespace lang{

	class HFC_API RepeatedCallException: public Exception {

	public:

		RepeatedCallException() {
			m_iErrno = RepeatedCall;
		}
	};
}
}
#endif

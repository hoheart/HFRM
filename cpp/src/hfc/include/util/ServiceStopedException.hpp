#ifndef __SERVICE_STOPED_EXCEPTION_HPP__
#define __SERVICE_STOPED_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc;

#include "../Error.hpp"

namespace hfc {
namespace lang{

	class HFC_API ServiceStopedException: public Exception {

	public:

		ServiceStopedException() {
			m_iErrno = ServiceStoped;
		}
	};
}
}
#endif

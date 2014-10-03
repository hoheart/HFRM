#ifndef __TIMEOUT_EXCEPTION_HPP__
#define __TIMEOUT_EXCEPTION_HPP__

#include "../hfc_def.hpp"

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace concurrent {

class HFC_API TimeoutException: public Exception {

public:

	TimeoutException() {
		m_iErrno = Timeout;
	}
};

}
}
#endif

#ifndef __NOT_DIGIT_EXCEPTION_HPP__
#define __NOT_DIGIT_EXCEPTION_HPP__

#include "../hfc_def.hpp"
#include "Exception.hpp"
using namespace hfc;

#include "../Error.hpp"

namespace hfc {
namespace lang{

class HFC_API NotDigitException: public Exception {

public:

	NotDigitException() {
		m_iErrno = NotDigit;
	}
};

}
}
#endif

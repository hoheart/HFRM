#ifndef __EXCEPTION_HPP__
#define __EXCEPTION_HPP__

#include "../hfc_def.hpp"

#include "String.hpp"

namespace hfc {
namespace lang{

class HFC_API Exception {

public:

	int m_iErrno;
	String m_strErrstr;

public:

	Exception() {
	}

	Exception(const int errno) {
		m_iErrno = errno;
	}

	Exception(const String& errstr) {
		m_strErrstr = errstr;
	}

	Exception(const int errno, const String& errstr) {
		m_iErrno = errno;
		m_strErrstr = errstr;
	}
};
}
}
#endif

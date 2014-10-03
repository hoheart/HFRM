#ifndef __NOT_IMPLEMENT_EXCEPTION_HPP__
#define __NOT_IMPLEMENT_MEMORY_EXCEPTION_HPP__

#include "../hfc_def.hpp"

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace lang {

class HFC_API NotImplementException: public Exception {

public:

	NotImplementException() {
		m_iErrno = NotImplement;
	}
};

}
}
#endif

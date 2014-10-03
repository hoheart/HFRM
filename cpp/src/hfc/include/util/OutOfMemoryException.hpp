#ifndef __OUT_OF_MEMORY_EXCEPTION_HPP__
#define __OUT_OF_MEMORY_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc;

#include "../Error.hpp"

namespace hfc {
namespace util{

	class HFC_API OutOfMemoryException: public Exception {

	public:

		OutOfMemoryException() {
			m_iErrno = OutOfMemory;
		}
	};
	
}
}
#endif

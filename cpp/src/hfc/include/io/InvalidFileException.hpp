#ifndef __INVALID_FILE_EXCEPTION_HPP__
#define __INVALID_FILE_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace io {

/**
 * 文件不存在或文件格式不正确。
 */
class HFC_API InvalidFileException: public Exception {

public:

	InvalidFileException() {
		m_iErrno = InvalidFile;
	}
};

}
}
#endif

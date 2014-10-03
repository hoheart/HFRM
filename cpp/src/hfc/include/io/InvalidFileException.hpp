#ifndef __INVALID_FILE_EXCEPTION_HPP__
#define __INVALID_FILE_EXCEPTION_HPP__

#include "../lang/Exception.hpp"
using namespace hfc::lang;

#include "../Error.hpp"

namespace hfc {
namespace io {

/**
 * �ļ������ڻ��ļ���ʽ����ȷ��
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

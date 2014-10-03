#ifndef __REQUEST_PROCESSOR_HPP__
#define __REQUEST_PROCESSOR_HPP__

#include <hfc/lang/String.hpp>
using namespace hfc::lang;

namespace hfrm {
namespace NetServiceFrame {

class IRequestProcessor {

public:

	virtual ~IRequestProcessor() {
	}

public:
	
	virtual void onConnect(int fd) = 0;

	virtual void onRead(int fd, String& str) = 0;

	virtual void onWrite(int fd) = 0;
	
	virtual void onClose( int fd) = 0;
};

}
}

#endif

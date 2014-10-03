#ifndef __NET_SERVICE_HPP__
#define __NET_SERVICE_HPP__

#include <map>
using namespace std;

#include <hfc/net/TCPServer.hpp>
using namespace hfc::net;

#include "EPoll.hpp"
#include "FrameProcessor.hpp"
using namespace hfrm::NetServiceFrame;

namespace hfrm {
namespace NetServiceFrame {

/**
 * 网络服务器，提供tcp服务。
 */
class NetService {

protected:

	TCPServer m_oTcpServer;
	EPoll m_oPoll;
	FrameProcessor m_oProcessor;

public:

	NetService();
	virtual ~NetService();

public:

	void init(const char* szIp, unsigned short usPort);
	void start();
	void stop();

	void setReqContentProcessor(IRequestProcessor& processor) {
		m_oProcessor.setContentProcessor(processor);
	}

};
}
}

#endif

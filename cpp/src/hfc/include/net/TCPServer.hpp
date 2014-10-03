#ifndef __TCP_SERVER_HPP__
#define __TCP_SERVER_HPP__

#include "../hfc_def.hpp"

#include "../net/Connection.hpp"
using namespace hfc::net;

namespace hfc {
namespace net {

class HFC_API TCPServer: public Socket {

public:

	TCPServer();
	virtual ~TCPServer();

public:

	void create(const String& strIp, const unsigned short sPort) {
		Socket::create(Socket::Tcp, strIp, sPort);
	}
	void listen();
	void accept(Connection& c);

};

}
}

#endif

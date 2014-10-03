#ifndef __UDP_SOCKET_HPP__
#define __UDP_SOCKET_HPP__

#include "../hfc_def.hpp"

#include "Socket.hpp"
using namespace hfc::net;

namespace hfc {
namespace net {

class HFC_API UDPSocket: public Socket {

public:

	static const int SHUTDOWN;
	static const int SOCKET_ERR;

public:

	UDPSocket();
	virtual ~UDPSocket();


public:

	void create(const String& strIp, const unsigned short sPort) {
		Socket::create(Socket::Udp, strIp, sPort);
	}

	int recv(String& strIp , unsigned short& usPort , void* p, const int len);

	int send(const String::t_char* szIp , const unsigned short usPort , const void* p, const int len);
};

}
}
#endif

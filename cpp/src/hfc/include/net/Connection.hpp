#ifndef __CONNECTION_HPP__
#define __CONNECTION_HPP__

#include "../hfc_def.hpp"

#include "Socket.hpp"
using namespace hfc::net;

namespace hfc {
namespace net {

class HFC_API Connection: public Socket {

public:

	static const int CLOSED;
	static const int SOCKET_ERR;


public:

	Connection();
	virtual ~Connection();


public:

	bool connect(const char* szIp, const unsigned short sPort);

	int recv(void* p, const int len);

	int send(const void* p, const int len);
};

}
}
#endif

#ifndef __SOCKET_HPP__
#define __SOCKET_HPP__

#include "../hfc_def.hpp"

#include "../lang/String.hpp"
using namespace hfc::lang;

#ifdef _WIN32
#include <winsock2.h>
#else
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/unistd.h>
#include <sys/socket.h>
#endif

namespace hfc {
namespace net {

class HFC_API Socket {

public:
	typedef enum Type {
		Tcp, Udp
	} Type;

#ifdef _WIN32
	typedef SOCKET OSSocket;
#else
	typedef int OSSocket;
#endif

public:

	Socket();
	virtual ~Socket();

public:

	void attach(const OSSocket socket, const String& strIp,
			const unsigned short sPort) {
		m_sock = socket;
		m_sPort = sPort;
		m_strIp = strIp;
	}
	void attach(const OSSocket socket);

	void detach();


	void create(const Type t, const String& strIp, const unsigned short sPort);

	String getIp() const {
		return m_strIp;
	}

	unsigned short getPort() const {
		return m_sPort;
	}

	void close();

	void setNonblocking( bool bBlocking );

public:

	operator const OSSocket() const {
		return m_sock;
	}

protected:

	void setAddr(struct sockaddr_in& addr, const String::t_char* szIp,
			unsigned short usPort) {
		addr.sin_family = AF_INET;
		addr.sin_port = htons(usPort);
		if (NULL == szIp || '\0' == szIp[0] || '*' == szIp[0]) {
			addr.sin_addr.s_addr = INADDR_ANY;
		} else {
			addr.sin_addr.s_addr = inet_addr(szIp);
		}
	}

protected:

	static const OSSocket InvalidSocket;

protected:

	String m_strIp;
	unsigned short m_sPort;

	OSSocket m_sock;

};

}
}

#endif

#include "../../include/net/Socket.hpp"
using namespace hfc::net;
#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;
#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

#ifndef _WIN32
#include <unistd.h>
#include <fcntl.h>
#endif

#ifdef _WIN32
const Socket::OSSocket Socket::InvalidSocket = INVALID_SOCKET;
#else
const Socket::OSSocket Socket::InvalidSocket = -1;
#endif

Socket::Socket() :
	m_sock(InvalidSocket), m_sPort(0) {
#ifdef _WIN32
	WSADATA wd;
	WSAStartup( 0x0101 , &wd );
#endif
}

Socket::~Socket() {
#ifdef _WIN32
	WSACleanup();
#endif

	close();
}

void Socket::attach(OSSocket socket) {
	m_sock = socket;

	struct sockaddr_in peeraddr;
#ifndef _WIN32
	socklen_t len = sizeof(peeraddr);
#else
	int len = sizeof(peeraddr);
#endif
	int ret = getpeername(socket, (struct sockaddr *) &peeraddr, &len);
	if (ret < 0) {
		throw OSErrorException("can not get peer name.");
	}

	m_sPort = ntohs(peeraddr.sin_port);
	m_strIp = inet_ntoa(peeraddr.sin_addr);
}

void Socket::detach() {
	m_sock = InvalidSocket;
}

void Socket::create(Type t, const String& strIp, const unsigned short sPort) {
	m_strIp = strIp;
	m_sPort = sPort;

	//1.创建socket描述符
	if (Tcp == t) {
		m_sock = ::socket(AF_INET, SOCK_STREAM, 0);
	} else {
		m_sock = ::socket(AF_INET, SOCK_DGRAM, 0);
	}
	if (InvalidSocket == m_sock) {
		throw OSErrorException("can't create socket.");
	}

	//2. Allows the socket to be bound to an address that is already in use.
	//only the TIME_WAIT status. it means u can use the port when it closed immediately.
	int val = 1;
	if (0 != ::setsockopt(m_sock, SOL_SOCKET, SO_REUSEADDR, (const char*) &val,
			sizeof(val))) {
		close();

		throw OSErrorException("call setsockopt error.");
	}

	//3.绑定IP和端口
	sockaddr_in addr = { 0 };
	setAddr(addr, m_strIp.c_str(), m_sPort);

	if (0 != ::bind(m_sock, (sockaddr*) &addr, sizeof(addr))) {
		close();

		throw OSErrorException("can't bind.");
	}
}

void Socket::close() {
	if (InvalidSocket != m_sock) {
#ifdef _WIN32
		::closesocket( m_sock );
#else
		::close(m_sock);
#endif
	}
}

void Socket::setNonblocking(bool bBlocking) {
#ifdef _WIN32
	int iMode = bBlocking ? 1 : 0;
	if( 0 != ioctlsocket( m_sock , FIONBIO, (u_long FAR*) &iMode) ) {
		throw OSErrorException( "can not set nonblocking.");
	}
#else
	int opts = opts = fcntl(m_sock, F_GETFL);
	if (opts < 0) {
		throw 1;
	}
	if (bBlocking) {
		opts = opts | O_NONBLOCK;
	} else {
		opts = opts & ~O_NONBLOCK;
	}
	if (fcntl(m_sock, F_SETFL, opts) < 0) {
		throw 2;
	}
#endif
}

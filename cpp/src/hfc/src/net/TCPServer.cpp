#include "../../include/net/TCPServer.hpp"
using namespace hfc::net;

#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

TCPServer::TCPServer() {
}

TCPServer::~TCPServer() {
}

void TCPServer::listen() {
	if (0 != ::listen(m_sock, 0)) {
		throw OSErrorException("can't listen.");
	}
}

void TCPServer::accept(Connection& c) {
	sockaddr_in addr = { 0 };
	int iClientAddrLen = sizeof(addr);
#ifdef _WIN32
	Socket::OSSocket clientSock = ::accept(m_sock, (sockaddr*) &addr,
			&iClientAddrLen);
#else
	Socket::OSSocket clientSock = ::accept(m_sock, (sockaddr*) &addr,
			(socklen_t*) &iClientAddrLen);
#endif

	if (Socket::InvalidSocket == clientSock) {
		throw OSErrorException("accept invalid client socket.");
	}

	c.attach(clientSock, inet_ntoa(addr.sin_addr), ntohs(addr.sin_port));

	//	//设置：如果socket关闭，让recv函数不阻塞。
	//	bool bDontLinger = true;
	//	setsockopt(m_sock, SOL_SOCKET, SO_DONTLINGER, (const char*) &bDontLinger,
	//			sizeof(bDontLinger));
}


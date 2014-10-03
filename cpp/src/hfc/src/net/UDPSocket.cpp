#include "../../include/net/UDPSocket.hpp"
using namespace hfc::net;

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;
#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

const int UDPSocket::SHUTDOWN = 0;

#ifdef _WIN32
const int UDPSocket::SOCKET_ERR = SOCKET_ERROR;
#else
const int UDPSocket::SOCKET_ERR = -1;
#endif

UDPSocket::UDPSocket() {
}

UDPSocket::~UDPSocket() {
}

int UDPSocket::recv(String& strIp, unsigned short& usPort, void* p,
		const int len) {
	sockaddr_in addr = { 0 };
	int addrLen = sizeof(addr);
	int ret;
#ifdef _WIN32
	ret = ::recvfrom( m_sock, (char*)p, len , 0, (struct sockaddr *) &addr , &addrLen );
#else
	ret = ::recvfrom(m_sock, p, len, 0, (struct sockaddr *) &addr,
			(socklen_t*) &addrLen);
#endif
	if (0 == ret) {
		return SHUTDOWN;
	} else if (-1 == ret) {
		throw OSErrorException("UDP socket error.");
	} else {
		strIp = inet_ntoa(addr.sin_addr);
		usPort = htons(addr.sin_port);

		return ret;
	}
}

int UDPSocket::send(const String::t_char* szIp, const unsigned short usPort,
		const void* p, const int len) {
	sockaddr_in addr = { 0 };
	int addrLen = sizeof(addr);
	setAddr(addr, szIp, usPort);
	int ret;

#ifdef _WIN32
	ret = ::sendto( m_sock , (char*)p , len , 0 , (struct sockaddr*)&addr , addrLen );
#else
	ret = ::sendto(m_sock, p, len, 0, (struct sockaddr*) &addr,
			(socklen_t) addrLen);
#endif
	if (SOCKET_ERR == ret) {
		throw OSErrorException("UDP socket error on send.");
	} else {
		return ret;
	}
}

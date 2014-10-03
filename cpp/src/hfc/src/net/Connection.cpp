#include "../../include/net/Connection.hpp"
using namespace hfc::net;

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;
#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;


const int Connection::CLOSED = 0;
const int Connection::SOCKET_ERR = -1;

Connection::Connection() {
}

Connection::~Connection() {
}

int Connection::recv(void* p, const int len) {
	if (NULL == p || 0 >= len) {
		throw InvalidParameterException();
	}

#ifdef _WIN32
	int ret = ::recv(m_sock, (char*)p, len, 0);
	if( 0 == ret ){
		return CLOSED;
	}else if( SOCKET_ERROR == ret ){
		return SOCKET_ERR;
	}else{
		return ret;
	}
#else
	int ret = ::recv(m_sock, p, len, 0);
	if( 0 == ret ){
		return CLOSED;
	}else if( -1 == ret ){
		return SOCKET_ERR;
	}else{
		return ret;
	}
#endif
}

int Connection::send(const void* p, const int len) {
	if (NULL == p || 0 >= len) {
		throw InvalidParameterException();
	}

#ifdef _WIN32
	return ::send(m_sock, (char*)p, len, 0);
#else
	return ::send(m_sock, p, len, 0);
#endif

}

bool Connection::connect(const char* szIp, const unsigned short sPort) {
	if (NULL == szIp || 0 == sPort) {
		throw InvalidParameterException();
	}

	if (InvalidSocket == m_sock) {
		create(Socket::Tcp, "", 0);
	}

	//	BOOL bDontLinger = true;
	//	setsockopt(m_sock, SOL_SOCKET, SO_DONTLINGER, (const char*) &bDontLinger,
	//			sizeof(bDontLinger));

	struct sockaddr_in addr = { 0 };
	addr.sin_addr.s_addr = inet_addr(szIp);
	addr.sin_port = htons(sPort);
	addr.sin_family = AF_INET;

	int ret = ::connect(m_sock, (sockaddr*) &addr, sizeof(addr));
	if (0 != ret) {
		return false;
	}

	return true;
}

#include "../../include/asynchronous/AsynchronousInputServer.hpp"
using namespace hfc::asynchronous;

#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

#include "../../include/concurrent/AutoLocker.hpp"
using namespace hfc::concurrent;

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;

AsynchronousInputServer::AsynchronousInputServer(){
}

AsynchronousInputServer::~AsynchronousInputServer(){
}

int AsynchronousInputServer::read( t_fd fd , void* buf , int len ){
#ifdef _WIN32
	DWORD readLen = 0;
	DWORD flag = 0;
	OVERLAPPED ol = {0};
	int ret = ::ReadFile( fd , buf , len, &readLen , &ol );
	if( 0 == ret ){
		throw OSErrorException( "WriteFile error." );
	}
#else
	//linuxֻ���¼�û����󣬲���Ҫ����ġ���֪ͨ�ɶ�ʱ�Ŷ���
#endif

	return addReq( fd , buf , len );
}
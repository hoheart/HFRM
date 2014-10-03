#ifndef __ASYNCHRONOUS_INPUT_SERVER_HPP__
#define __ASYNCHRONOUS_INPUT_SERVER_HPP__

#include "AsynchronousServer.hpp"

namespace hfc{
namespace asynchronous{

class HFC_API AsynchronousInputServer : public AsynchronousServer
{

public:

	AsynchronousInputServer();

	virtual ~AsynchronousInputServer();

public:

	void onRet( t_fd fd , int seq , void* buf , int iCompleteLen , int iReqLen ){
		if( NULL != m_pProcessor ){
			m_pProcessor->onReadRet( fd , seq , buf , iCompleteLen , iReqLen );
		}
	}

	int read( t_fd fd , void* buf , int len );
	
};

}
}

#endif 

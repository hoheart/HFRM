#ifndef __ASYNCHRONOUS_OUTPUT_SERVER_HPP__
#define __ASYNCHRONOUS_OUTPUT_SERVER_HPP__

#include "../lang/String.hpp"
using namespace hfc::lang;

#include "AsynchronousServer.hpp"

namespace hfc{
namespace asynchronous{

class HFC_API AsynchronousOutputServer : public AsynchronousServer
{

public:

	AsynchronousOutputServer();

	virtual ~AsynchronousOutputServer();

public:

	void onRet( t_fd fd , int seq , void* buf , int iCompleteLen , int iReqLen ){
		if( NULL != m_pProcessor ){
			m_pProcessor->onWriteRet( fd , seq , buf , iCompleteLen , iReqLen );
		}
	}

	int write( t_fd fd , void* buf , int len );
	
};

}
}

#endif 

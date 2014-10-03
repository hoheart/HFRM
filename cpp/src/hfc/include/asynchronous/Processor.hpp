#ifndef __PROCESSOR_HPP__
#define __PROCESSOR_HPP__

#include "../hfc_def.hpp"
using namespace hfc;

namespace hfc{
namespace asynchronous{

class HFC_API IProcessor  
{

public:

	virtual ~IProcessor(){}

public:

	virtual void onClosed( t_fd fd ) = 0;
	virtual void onReadRet( t_fd fd , int seq , void* buf , int iCompleteLen , int reqLen ) = 0;
	virtual void onWriteRet( t_fd fd , int seq , void* buf , int iCompleteLen , int reqLen ) = 0;
	virtual void onError( t_fd fd ) = 0;

};

}
}

#endif

#ifndef __ASYNCHRONOUS_SERVER_HPP__
#define __ASYNCHRONOUS_SERVER_HPP__

#include "../lang/Runnable.hpp"
using namespace hfc::lang;

#include "../concurrent/Locker.hpp"
using namespace hfc::concurrent;

#ifdef _WIN32
#include <windows.h>
#endif

#include "Processor.hpp"

#include <list>
#include <map>
using namespace std;

/**
* 由于windows的IOCP在同时监听读写是否完成时，无法辨别完成事件是读还是写。所以把input和output分开。
*/

namespace hfc{
namespace asynchronous{

class HFC_API AsynchronousServer : public IRunnable
{

protected:

	struct ReqInfo{
		void* buf;
		int len;
		int seqNo;
		int completeLen;
	};

	typedef list<ReqInfo*> ReqList;

	struct FdInfo{
		ReqList oReqList;
		Locker oReqListLocker;
		int iReqGenerator;
		Locker oSeqGeneratorLocker;
		//true表示有数据可读或有空闲可写。
		bool bReady;
		Locker oReadyLocker;
	};

	typedef map<t_fd , FdInfo*> FdMap;

protected:

	t_fd m_tServer;

	bool m_bStoped;

	IProcessor* m_pProcessor;

	FdMap m_oFdMap;
	Locker m_oFdMapLocker;

#ifndef _WIN32
	struct epoll_event m_oPollEvent;
#endif

public:

	AsynchronousServer();

	virtual ~AsynchronousServer();

public:

	void add(t_fd fd);
	void del(t_fd fd);

	void start();
	void stop();

	void run(void* pParam = NULL);

	virtual void onRet( t_fd fd , int seq , void* buf , int iCompleteLen , int iReqLen ) = 0;

	void setProcessor( IProcessor* pProcessor ){
		m_pProcessor = pProcessor;
	}

protected:

	int addReq( t_fd fd , void* buf , int len );
	
};

}
}

#endif 

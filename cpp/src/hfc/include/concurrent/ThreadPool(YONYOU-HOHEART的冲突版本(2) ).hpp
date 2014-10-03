#ifndef __THREAD_POOL_HPP__
#define __THREAD_POOL_HPP__

#include "../hfc_def.hpp"


#ifdef _WIN32
#include <windows.h>
#else
#include <pthread.h>
#endif

#include "../lang/Runnable.hpp"
using namespace hfc::lang;
#include "Locker.hpp"
#include "Condition.hpp"
using namespace hfc::concurrent;

#include <map>
#include <list>
using namespace std;


namespace hfc{
namespace concurrent{
		
class HFC_API ThreadPool{

public:

	static const int DEFAULT_MAX_THREAD;
		
protected:
	
	ThreadPool();
	
public:
	
	/**
	* 取得该类的唯一实例。
	*/
	static ThreadPool* Instance(){
		static ThreadPool oMe; 
		return &oMe;
	}
	
	virtual ~ThreadPool();	

public:

	static void Sleep( int milliseconds );
	
	
public:
	
	/**
	* 向线程池里加任务，让线程池启动线程去执行。
	* @return void
	*/
	void addTask( IRunnable& oTask , void* pParam = NULL );
	
	void startPool(){
		m_bStop = false;
	}
	
	/**
	* 让所有线程执行完当前任务后退出，并停止对外服务，即不能再addJob.该函数会阻塞。
	*/
	void stopPool();
	
	/**
	* 设置线程池中可以容纳的最大线程数
	*/
	void setMaxThreadCount( int iCount );
	
protected:
	
#ifdef _WIN32
	typedef HANDLE t_thread;
#else
	typedef pthread_t t_thread;
#endif
	
	typedef struct TaskInfo
	{
		IRunnable* pTask ;
		void* pParam ;//参数
	}TaskInfo;
	
	typedef map<IRunnable* , TaskInfo*> TaskMap;
	
	typedef list<t_thread> ThreadList;
	
protected:
	
	TaskMap m_oTaskMap;
	Locker m_oTaskMapLocker;
	
	int m_iThreadCount;
	ThreadList m_oThreadList;
	Locker m_oThreadListLocker;

	int m_iIdleThreadCount;
	Locker m_oIdleThreadCountLocker;
	
	int m_iMaxThreadCount;
	
	/**
	* 所有线程没事时都等待这个condition,一旦notify就起来执行任务
	*/
	Condition m_oThreadNotice;

	/**
	* 线程停止时，主线程等待通知的condition。
	*/
	Condition m_oStopNotice;
	
	
	bool m_bStop;
	
protected:
	
#ifdef _WIN32
	static DWORD WINAPI ThreadProc( LPVOID pParam );
#else
	static void* ThreadProc( void* pParam );
#endif
	
	t_thread createThread();
	void releaseThread(t_thread t);

	void pushTask(IRunnable& oTask , void* pParam = NULL );
};

}
}

#endif
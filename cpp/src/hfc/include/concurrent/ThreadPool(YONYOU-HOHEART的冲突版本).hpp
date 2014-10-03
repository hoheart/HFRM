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
	* ȡ�ø����Ψһʵ����
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
	* ���̳߳�����������̳߳������߳�ȥִ�С�
	* @return void
	*/
	void addTask( IRunnable& oTask , void* pParam = NULL );
	
	void startPool(){
		m_bStop = false;
	}
	
	/**
	* �������߳�ִ���굱ǰ������˳�����ֹͣ������񣬼�������addJob.�ú�����������
	*/
	void stopPool();
	
	/**
	* �����̳߳��п������ɵ�����߳���
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
		void* pParam ;//����
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
	* �����߳�û��ʱ���ȴ����condition,һ��notify������ִ������
	*/
	Condition m_oThreadNotice;

	/**
	* �߳�ֹͣʱ�����̵߳ȴ�֪ͨ��condition��
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
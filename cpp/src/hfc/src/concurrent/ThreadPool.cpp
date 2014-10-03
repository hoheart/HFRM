#include "../../include/concurrent/ThreadPool.hpp"
using namespace hfc;

#include "../../include/concurrent/AutoLocker.hpp"
#include "../../include/concurrent/TimeoutException.hpp"
using namespace hfc::concurrent;

#include "../../include/util/ServiceStopedException.hpp"
#include "../../include/util/OutOfMemoryException.hpp"
#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

#include <string.h>

#ifndef _WIN32
#include <unistd.h>
#endif

const int ThreadPool::DEFAULT_MAX_THREAD = 10;

ThreadPool::ThreadPool() {
	m_iMaxThreadCount = DEFAULT_MAX_THREAD;
	m_iIdleThreadCount = 0;
	m_bStop = true;
}

ThreadPool::~ThreadPool() {
	stopPool();
}

void ThreadPool::pushTask(IRunnable& oTask, void* pParam) {
	TaskInfo* pTaskInfo = new TaskInfo;
	if (NULL == pTaskInfo) {
		throw OutOfMemoryException();
	}
	pTaskInfo->pTask = &oTask;
	pTaskInfo->pParam = pParam;

	try {
		AutoLocker oTML(m_oTaskMapLocker);
		if (m_bStop) {
			throw ServiceStopedException();
		}
		m_oTaskMap.insert(TaskMap::value_type(pTaskInfo->pTask, pTaskInfo));
		m_oTaskMapLocker.unlock();
	} catch (Exception e) {
		delete pTaskInfo;
		throw e;
	}
}

void ThreadPool::addTask(IRunnable& oTask, void* pParam) {
	pushTask(oTask, pParam);

	AutoLocker oAuto(m_oIdleThreadCountLocker);
	AutoLocker oAT(m_oThreadListLocker);
	if (m_iIdleThreadCount <= 0) {
		if (m_oThreadList.size() < m_iMaxThreadCount) {
			t_thread thread;
			//不能先创建线程再push_back，因为线程创建成功后，必须成功记录到列表中，因为他已经去执行代码去了，不能中断。
			//所以，先开辟一块内存存放thread，等线程创建成功后，再修改这个地址。
			try {
				m_oThreadList.push_back(thread);
			} catch (Exception e) {
				throw OSErrorException("can not put thread to thread list.");
			}

			thread = createThread();
			if (0 == thread) {
				ThreadList::iterator i = m_oThreadList.end();
				--i;
				m_oThreadList.erase(i);

				throw OSErrorException("can not create new thread.");
			}

			++m_iThreadCount;

			ThreadList::iterator i = m_oThreadList.end();
			--i;
			memcpy(&(*i), &thread, sizeof(thread));
		}
	}

	m_oThreadNotice.notify();//如果有空余线程，直接把他唤醒，他会一直工作到没有任务
}

#ifdef _WIN32
DWORD WINAPI ThreadPool::ThreadProc( LPVOID pParam ) {
#else
void* ThreadPool::ThreadProc(void* pParam) {
#endif
	ThreadPool* pMe = (ThreadPool*) pParam;

	while (true) {
		if (pMe->m_bStop) {
			AutoLocker(pMe->m_oThreadListLocker);
			--pMe->m_iThreadCount;
			if (0 == pMe->m_iThreadCount) {
				pMe->m_oThreadListLocker.unlock();
				pMe->m_oStopNotice.notify();
			} else {
				//退出前通知下一个线程
				pMe->m_oThreadNotice.notify();
			}
			pMe->m_oThreadListLocker.unlock();

			return 0;
		}

		AutoLocker(pMe->m_oTaskMapLocker);
		if (pMe->m_oTaskMap.begin() == pMe->m_oTaskMap.end()) {
			//只有当没有任务时，才空闲。
			AutoLocker(pMe->m_oIdleThreadCountLocker);
			++pMe->m_iIdleThreadCount;
			pMe->m_oIdleThreadCountLocker.unlock();

			pMe->m_oTaskMapLocker.unlock();

			//一空闲就开始等待新任务
			pMe->m_oThreadNotice.wait();

			//一旦线程被唤醒，就不空闲了。
			//m_iIdleThreadCount的增减一定要在同一个条件下进行，否则容易出错。
			AutoLocker(pMe->m_oIdleThreadCountLocker);
			--pMe->m_iIdleThreadCount;
			pMe->m_oIdleThreadCountLocker.unlock();

			continue;
		}

		TaskMap::iterator iter = pMe->m_oTaskMap.begin();
		TaskInfo* pTaskInfo = iter->second;
		pMe->m_oTaskMap.erase(iter);

		pMe->m_oTaskMapLocker.unlock();

		try {
			pTaskInfo->pTask->run(pTaskInfo->pParam);
		} catch (Exception e) {
			//线程运行时，是不能出任何错误的。
		}

		delete pTaskInfo;
	}
}

void ThreadPool::stopPool() {
	if (m_bStop) {
		return;
	}

	AutoLocker oTML(m_oTaskMapLocker);//在发送停止线程的数据前，不让再addJob
	m_bStop = true;
	m_oTaskMapLocker.unlock();

	AutoLocker oAuto(m_oIdleThreadCountLocker);
	for (int i = 0; i < m_iIdleThreadCount; ++i) {
		//先用这种方式唤醒尽可能多的线程，线程退出时，也会发送消息唤醒其他线程。用这种传递的方式结束线程。
		m_oThreadNotice.notify();
	}
	m_oIdleThreadCountLocker.unlock();

	while (true) {
		try {
			m_oThreadNotice.notify();
			int a =0;
			a ++;
			m_oStopNotice.wait(1);
		} catch (TimeoutException e) {
			continue;
		}

		break;
	}

	AutoLocker ai(m_oThreadListLocker);
	while (m_oThreadList.size() > 0) {
		m_oThreadList.pop_front();
	}
}

ThreadPool::t_thread ThreadPool::createThread() {
#ifdef _WIN32
	return ::CreateThread( NULL , 0 , ThreadProc , this , NULL , NULL );
#else
	t_thread thread;
	int ret = pthread_create(&thread, NULL, ThreadProc, this);
	if (0 != ret) {
		return NULL;
	} else {
		return thread;
	}
#endif
}

void ThreadPool::releaseThread(t_thread t) {
#ifdef _WIN32
	::CloseHandle(t);
#else

#endif
}

void ThreadPool::setMaxThreadCount(int iCount) {
	if (iCount > 0) {
		m_iMaxThreadCount = iCount;
	}
}


void ThreadPool::Sleep( int milliseconds ){
#ifdef _WIN32
	::Sleep( milliseconds );
#else
	if( milliseconds >= 1000 ){
		if( 0 != ::sleep( milliseconds / 1000 ) ){
			throw OSErrorException( "call sleep error." );
		}
	}

	if( 0 != ::usleep( ( milliseconds % 1000 ) * 1000 ) ){
		throw OSErrorException( "call usleep error." );
	}
#endif
}
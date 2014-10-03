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
			//�����ȴ����߳���push_back����Ϊ�̴߳����ɹ��󣬱���ɹ���¼���б��У���Ϊ���Ѿ�ȥִ�д���ȥ�ˣ������жϡ�
			//���ԣ��ȿ���һ���ڴ���thread�����̴߳����ɹ������޸������ַ��
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

	m_oThreadNotice.notify();//����п����̣߳�ֱ�Ӱ������ѣ�����һֱ������û������
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
				//�˳�ǰ֪ͨ��һ���߳�
				pMe->m_oThreadNotice.notify();
			}
			pMe->m_oThreadListLocker.unlock();

			return 0;
		}

		AutoLocker(pMe->m_oTaskMapLocker);
		if (pMe->m_oTaskMap.begin() == pMe->m_oTaskMap.end()) {
			//ֻ�е�û������ʱ���ſ��С�
			AutoLocker(pMe->m_oIdleThreadCountLocker);
			++pMe->m_iIdleThreadCount;
			pMe->m_oIdleThreadCountLocker.unlock();

			pMe->m_oTaskMapLocker.unlock();

			//һ���оͿ�ʼ�ȴ�������
			pMe->m_oThreadNotice.wait();

			//һ���̱߳����ѣ��Ͳ������ˡ�
			//m_iIdleThreadCount������һ��Ҫ��ͬһ�������½��У��������׳���
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
			//�߳�����ʱ���ǲ��ܳ��κδ���ġ�
		}

		delete pTaskInfo;
	}
}

void ThreadPool::stopPool() {
	if (m_bStop) {
		return;
	}

	AutoLocker oTML(m_oTaskMapLocker);//�ڷ���ֹͣ�̵߳�����ǰ��������addJob
	m_bStop = true;
	m_oTaskMapLocker.unlock();

	AutoLocker oAuto(m_oIdleThreadCountLocker);
	for (int i = 0; i < m_iIdleThreadCount; ++i) {
		//�������ַ�ʽ���Ѿ����ܶ���̣߳��߳��˳�ʱ��Ҳ�ᷢ����Ϣ���������̡߳������ִ��ݵķ�ʽ�����̡߳�
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
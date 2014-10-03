#ifndef __EPOLL_HPP__
#define __EPOLL_HPP__

#include <sys/epoll.h>

#include <hfc/lang/Runnable.hpp>
using namespace hfc::lang;

namespace hfrm {
namespace NetServiceFrame {

/**
 * ��linux��epollһ����ʵ�ֶԶ���������ƣ��ļ����������п�д�ɶ����¼��ļ�ء�
 * ʵ��ԭ�����캯�����̳߳������һ����������poll�������¼��������������̳߳������poll����
 * ֮�����̳߳�����Ӵ����¼�������
 */
class EPoll: public IRunnable {

public:

	class PollData {

	public:
		int fd;
		int type;

	public:

		void destroy();
	};

public:

	static const int POLL_TYPE_READ;
	static const int POLL_TYPE_WRITE;
	static const int POLL_TYPE_CLOSE;
	static const int POLL_TYPE_ERROR;

protected:

	static const int LISTEND_EVENTS;

protected:

	int m_iEPollFd;
	bool m_bStoped;
	IRunnable* m_pProcessor;
	struct epoll_event m_oPollEvent;

public:

	EPoll();
	virtual ~EPoll();

public:

	void add(int fd);
	void del(int fd);

	void run(void* pParam = NULL);

	void setProcessor(IRunnable& processor);

	void startPoll();
	void stopPoll();

protected:

	void setNonblocking(int fd);

};
}
}

#endif

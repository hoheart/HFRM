#ifndef __EPOLL_HPP__
#define __EPOLL_HPP__

#include <sys/epoll.h>

#include <hfc/lang/Runnable.hpp>
using namespace hfc::lang;

namespace hfrm {
namespace NetServiceFrame {

/**
 * 与linux的epoll一样，实现对多个（无限制）文件描述符进行可写可读等事件的监控。
 * 实现原理：构造函数向线程池中添加一个任务用于poll，当有事件发生后，重新向线程池中添加poll任务。
 * 之后，向线程池中添加处理事件的任务。
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

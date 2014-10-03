#ifndef __FRAME_PROCESSOR_HPP__
#define __FRAME_PROCESSOR_HPP__

#include <hfc/lang/Runnable.hpp>
using namespace hfc::lang;

#include <hfc/net/TCPServer.hpp>
using namespace hfc::net;

#include "RequestProcessor.hpp"
#include "EPoll.hpp"

namespace hfrm {
namespace NetServiceFrame {

/**
 * 框架处理epoll事件的类。主要封装了新建连接、关闭连接、连接出错等操作，是连接的管理类。
 * 为节约一个长期等待线程，把服务器的等待accept线程放入poll里和客户端的等待read和write的线程放一起epoll。
 */
class FrameProcessor: public IRunnable {

protected:

	EPoll* m_pEPoll;

	TCPServer* m_pTcpServer;

	/**
	 * 外部的内容请求处理
	 */
	IRequestProcessor* m_pContentProcessor;

public:

	FrameProcessor();

	virtual ~FrameProcessor() {
	}

public:

	void run(void* pParam = NULL);

	void setPoll(EPoll& poll) {
		m_pEPoll = &poll;
	}

	void setTcpServer(TCPServer& server) {
		m_pTcpServer = &server;
	}

	void setContentProcessor(IRequestProcessor& processor) {
		m_pContentProcessor = &processor;
	}

protected:

	void processAccept();

	void processTrans();
};
}
}
#endif

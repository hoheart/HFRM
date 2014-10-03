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
 * ��ܴ���epoll�¼����ࡣ��Ҫ��װ���½����ӡ��ر����ӡ����ӳ���Ȳ����������ӵĹ����ࡣ
 * Ϊ��Լһ�����ڵȴ��̣߳��ѷ������ĵȴ�accept�̷߳���poll��Ϳͻ��˵ĵȴ�read��write���̷߳�һ��epoll��
 */
class FrameProcessor: public IRunnable {

protected:

	EPoll* m_pEPoll;

	TCPServer* m_pTcpServer;

	/**
	 * �ⲿ������������
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

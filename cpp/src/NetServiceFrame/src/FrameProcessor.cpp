#include "../include/FrameProcessor.hpp"
using namespace hfrm::NetServiceFrame;

#include "../include/EPoll.hpp"

#include <stdio.h>

FrameProcessor::FrameProcessor() :
	m_pContentProcessor(NULL) {
}

void FrameProcessor::run(void* pParam) {
	EPoll::PollData* pData = (EPoll::PollData*) pParam;

	if (pData->fd == (*m_pTcpServer)) {
		Connection c;
		m_pTcpServer->accept(c);

		m_pEPoll->add(c);

		//不让connection的析构函数关闭连接。
		c.detach();
	} else if (EPoll::POLL_TYPE_READ == pData->type) {
		Connection c;
		c.attach(pData->fd);

		char buf[10240] = { 0 };
		String str;
		//while (true) {
			int recvLen = c.recv(buf, sizeof(buf));
			if (recvLen <= 0) {
				m_pContentProcessor->onClose(c);

				m_pEPoll->del(c);

				return;
			}

			str.append(buf, recvLen);
		//}

		//调用处理器
		if (NULL != m_pContentProcessor) {
			m_pContentProcessor->onRead(pData->fd, str);
		}

		c.detach();
	} else if (EPoll::POLL_TYPE_WRITE == pData->type) {
		if (NULL != m_pContentProcessor) {
			m_pContentProcessor->onWrite(pData->fd);
		}
	} else if (EPoll::POLL_TYPE_ERROR == pData->type || EPoll::POLL_TYPE_CLOSE
			== pData->type) {
		printf("close one.\n");
		m_pEPoll->del(pData->fd);
	}
}

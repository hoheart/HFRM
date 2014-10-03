#include "../include/NetService.hpp"

#include <hfc/lang/Runnable.hpp>
using namespace hfc::lang;

#include <hfc/concurrent/ThreadPool.hpp>
using namespace hfc::concurrent;

NetService::NetService() {
}

NetService::~NetService() {
	stop();
}

void NetService::init(const char* szIp, unsigned short usPort) {
	m_oTcpServer.create(szIp, usPort);
}

void NetService::start() {
	ThreadPool::Instance()->startPool();

	m_oTcpServer.listen();

	m_oPoll.add(m_oTcpServer);

	m_oProcessor.setPoll(m_oPoll);
	m_oProcessor.setTcpServer(m_oTcpServer);
	m_oPoll.setProcessor(m_oProcessor);

	m_oPoll.startPoll();
}

void NetService::stop() {
	m_oPoll.stopPoll();

	ThreadPool* pThreadPool = ThreadPool::Instance();
	pThreadPool->stopPool();
}

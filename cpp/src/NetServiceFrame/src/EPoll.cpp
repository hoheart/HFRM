#include "../include/EPoll.hpp"
using namespace hfrm::NetServiceFrame;

#include <sys/epoll.h>

#include <hfc/util/OSErrorException.hpp>
using namespace hfc::util;

#include <hfc/concurrent/ThreadPool.hpp>
using namespace hfc::concurrent;

#ifndef _WIN32
#include <unistd.h>
#include <fcntl.h>
#endif

#include <memory.h>
#include <stdio.h>

const int EPoll::POLL_TYPE_READ = 1;
const int EPoll::POLL_TYPE_WRITE = 2;
const int EPoll::POLL_TYPE_CLOSE = 3;
const int EPoll::POLL_TYPE_ERROR = 4;

const int EPoll::LISTEND_EVENTS = EPOLLIN | EPOLLOUT | EPOLLRDHUP | EPOLLPRI
		| EPOLLERR | EPOLLHUP | EPOLLET;

EPoll::EPoll() :
	m_iEPollFd(-1), m_bStoped(false), m_pProcessor(NULL) {
	//Since Linux 2.6.8, the size argument is unused. 
	//( The kernel dynamically sizes the required data structures without needing this initial hint.)
	m_iEPollFd = epoll_create(1);
	if (m_iEPollFd <= 0) {
		throw OSErrorException("call epoll_create function error.");
	}
}

EPoll::~EPoll() {
	close(m_iEPollFd);
}

void EPoll::add(int fd) {
	memset(&m_oPollEvent, 0, sizeof(m_oPollEvent));
	m_oPollEvent.data.fd = fd;
	m_oPollEvent.events = LISTEND_EVENTS;

	//setNonblocking(fd);

	int ret = epoll_ctl(m_iEPollFd, EPOLL_CTL_ADD, fd, &m_oPollEvent);
	if (0 != ret) {
		throw OSErrorException("call epoll_ctl for add error. ");
	}
}

void EPoll::del(int fd) {
	m_oPollEvent.data.fd = fd;
	m_oPollEvent.events = LISTEND_EVENTS;

	int ret = epoll_ctl(m_iEPollFd, EPOLL_CTL_DEL, fd, &m_oPollEvent);
	if (0 != ret) {
		throw OSErrorException("call epoll_ctl for delete error. ");
	}
}

void EPoll::setProcessor(IRunnable& processor) {
	m_pProcessor = &processor;
}

/**
 * 启动一个线程任务去epoll_wait
 */
void EPoll::startPoll() {
	ThreadPool* pThreadPool = ThreadPool::Instance();
	pThreadPool->addTask(*this);
}

void EPoll::stopPoll() {
	m_bStoped = true;
}

void EPoll::run(void* pParam) {
	if (m_bStoped) {
		return;
	}

	const int iProcessCount = 5;
	struct epoll_event ev[iProcessCount] = { 0 };
	int iCount = epoll_wait(m_iEPollFd, ev, iProcessCount, -1);

	startPoll();

	if (NULL == m_pProcessor) {
		return;
	}

	ThreadPool* pThreadPool = ThreadPool::Instance();
	for (int i = 0; i < iCount; ++i) {
		PollData* pData = new PollData();
		pData->fd = ev[i].data.fd;

		if (ev[i].events & EPOLLIN || ev[i].events & EPOLLPRI) {
			pData->type = POLL_TYPE_READ;
		} else if (ev[i].events & EPOLLOUT) {
			pData->type = POLL_TYPE_WRITE;
		} else if (ev[i].events & EPOLLRDHUP || ev[i].events & EPOLLHUP) {
			pData->type = POLL_TYPE_CLOSE;
		} else if (ev[i].events & EPOLLERR) {
			pData->type = POLL_TYPE_ERROR;
		}

		pThreadPool->addTask(*m_pProcessor, pData);
	}
}

void EPoll::setNonblocking(int fd) {
	int opts = opts = fcntl(fd, F_GETFL);
	if (opts < 0) {
		throw OSErrorException("can not get socket options to set nonblock.");
	}
	opts = opts | O_NONBLOCK;
	if (fcntl(fd, F_SETFL, opts) < 0) {
		throw OSErrorException("can not set socket options for nonblock.");
	}
}


#include "../../include/asynchronous/AsynchronousServer.hpp"
using namespace hfc::asynchronous;

#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;

#include "../../include/concurrent/ThreadPool.hpp"
using namespace hfc::concurrent;

#include "../../include/concurrent/AutoLocker.hpp"
using namespace hfc::concurrent;

AsynchronousServer::AsynchronousServer():m_bStoped(true)
{
#ifdef _WIN32
	m_tServer = ::CreateIoCompletionPort( INVALID_HANDLE_VALUE , NULL , 0 , 0 );
	if( INVALID_HANDLE_VALUE == m_tServer ){
		throw OSErrorException( "call CreateIoCompletionPort function error." );
	}
#else
	m_tServer = ::epoll_create(1);
	if (m_tServer <= 0) {
		throw OSErrorException("call epoll_create function error.");
	}
#endif
}

AsynchronousServer::~AsynchronousServer()
{
#ifdef _WIN32
	::CloseHandle( m_tServer );
#else
	::close( m_tServer );
#endif
}

void AsynchronousServer::add(t_fd fd) {
#ifdef _WIN32
	ULONG key = (ULONG)fd;
	t_fd ret = ::CreateIoCompletionPort( fd , m_tServer , key , 0 );
	if( m_tServer != ret ){
		throw OSErrorException( "call CreateIoCompletionPort function error." );
	}
#else
	memset(&m_oPollEvent, 0, sizeof(m_oPollEvent));
	m_oPollEvent.data.fd = fd;
	m_oPollEvent.events = LISTEND_EVENTS;

	int ret = epoll_ctl(m_iEPollFd, EPOLL_CTL_ADD, fd, &m_oPollEvent);
	if (0 != ret) {
		throw OSErrorException("call epoll_ctl for add error. ");
	}
#endif

	FdInfo* pInfo = new FdInfo();
	pInfo->bReady = false;
	pInfo->iReqGenerator = 0;
	AutoLocker a( m_oFdMapLocker );
	m_oFdMap.insert( FdMap::value_type( fd , pInfo ) );
}

void AsynchronousServer::del(t_fd fd) {
#ifdef _WIN32
	//windows下不用删除，只要close了就行
#else
	m_oPollEvent.data.fd = fd;
	m_oPollEvent.events = LISTEND_EVENTS;

	int ret = epoll_ctl(m_iEPollFd, EPOLL_CTL_DEL, fd, &m_oPollEvent);
	if (0 != ret) {
		throw OSErrorException("call epoll_ctl for delete error. ");
	}
#endif

	AutoLocker a( m_oFdMapLocker );
	FdMap::iterator i = m_oFdMap.find( fd );
	if( m_oFdMap.end() != i ){
		m_oFdMap.erase( i );
	}
}

void AsynchronousServer::start() {
	if( m_bStoped ){
		m_bStoped = false;
	}

	ThreadPool* pThreadPool = ThreadPool::Instance();
	pThreadPool->addTask(*this);
}

void AsynchronousServer::stop(){
	m_bStoped = true;
}

void AsynchronousServer::run(void* pParam) {
	if (m_bStoped) {
		return;
	}
	
	//接着继续监听事件。本线程用来处理。
	start();
	
	if (NULL == m_pProcessor) {
		return;
	}
	
#ifdef _WIN32
	DWORD count = -1;
	ULONG fd = -1;
	OVERLAPPED* pol = NULL;
	BOOL ret =  ! ::GetQueuedCompletionStatus( m_tServer , &count , &fd , &pol , INFINITE );
	if( ERROR_SUCCESS == ret ){
		if( -1 == fd ){
			throw OSErrorException( "GetQueuedCompletionStatus error , can not get the key(fd)" );
		}
		
		if( 0 == count ){
			del( (t_fd)fd );

			m_pProcessor->onClosed( (t_fd)fd );
			
			return;
		}else{
			del( (t_fd)fd);

			m_pProcessor->onError( (t_fd)fd );
			
			return;
		}
	}
#else
	const int iProcessCount = 1;
	struct epoll_event ev[iProcessCount] = { 0 };
	int iProcessCount = epoll_wait(m_tServer, ev, iProcessCount, -1);
	if( 0 == iProcessCount ){
		return ;
	}

	t_fd fd = ev[i].data.fd;
	
	if (ev[i].events & EPOLLRDHUP || ev[i].events & EPOLLHUP) {
		if( NULL != m_pProcessor ){
			m_pProcessor->onClosed( fd );
		}

		return;
	} else if (ev[i].events & EPOLLERR) {
		if( NULL != m_pProcessor ){
			m_pProcessor->onError( fd );
		}

		return;
	}
#endif

	AutoLocker m( m_oFdMapLocker );
	FdMap::iterator i = m_oFdMap.find( (t_fd)fd );
	if( m_oFdMap.end() == i ){
		throw InvalidParameterException();
	}

	FdInfo* pFdInfo = i->second;
	m_oFdMapLocker.unlock();

	AutoLocker r( pFdInfo->oReqListLocker );
	ReqInfo* pReq = *(pFdInfo->oReqList.begin());
	pFdInfo->oReqList.pop_front();
	pFdInfo->oReqListLocker.unlock();

#ifndef _WIN32
	if (ev[i].events & EPOLLIN || ev[i].events & EPOLLPRI) {
		int count = ::read( fd , pReq->buf , pReq->len );
	} else if (ev[i].events & EPOLLOUT) {
		int count = ::write( fd , pReq->buf , pReq->len );
	}
	
	if( -1 == count ){
		if( NULL != m_pProcessor ){
			m_pProcessor->onError( fd );
		}
	}
#endif

	pReq->completeLen = count;

	onRet( (t_fd)fd , pReq->seqNo , pReq->buf , pReq->completeLen , pReq->len );
}

int AsynchronousServer::addReq( t_fd fd , void* buf , int len ){
	AutoLocker m( m_oFdMapLocker );
	FdMap::iterator i = m_oFdMap.find( fd );
	if( m_oFdMap.end() == i ){
		throw InvalidParameterException();
	}

	FdInfo* pFdInfo = i->second;
	m_oFdMapLocker.unlock();

	ReqInfo* pReq = new ReqInfo();
	pReq->buf = buf;
	pReq->completeLen = 0;
	pReq->len = len;

	AutoLocker s(pFdInfo->oSeqGeneratorLocker);
	pReq->seqNo = ++ pFdInfo->iReqGenerator;
	pFdInfo->oSeqGeneratorLocker.unlock();

	AutoLocker r( pFdInfo->oReqListLocker );
	pFdInfo->oReqList.push_back( pReq );

	return pReq->seqNo;
}



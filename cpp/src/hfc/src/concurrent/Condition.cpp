#include "../../include/concurrent/Condition.hpp"
using namespace hfc::concurrent;

#include "../../include/util/DateTime.hpp"
#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;

#include "../../include/concurrent/TimeoutException.hpp"
using namespace hfc::concurrent;

#ifndef _WIN32
#include <errno.h>
#endif

Condition::Condition() {
#ifdef _WIN32
	//使用event，主要是为了保持和linux行为一致。即同一个线程函数，
	//如果先把它设置为无信号，在调用WaitforSingleObject，还是会等待。而mutext不会。
	mCond = ::CreateEvent( NULL , FALSE , FALSE , NULL );
#else
	if (0 != ::pthread_mutex_init(&mMutex, NULL)) {
		throw OSErrorException("pthread_mutex_init error.");
	}

	if (0 != ::pthread_cond_init(&mCond, NULL)) {
		throw OSErrorException("pthread_cond_init error.");
	}
#endif
}

Condition::~Condition() {
#ifdef _WIN32
	::CloseHandle( mCond );
#else
	if (0 != ::pthread_mutex_destroy(&mMutex)) {
		throw OSErrorException("pthread_mutex_destroy error.");
	}

	if (0 != ::pthread_cond_destroy(&mCond)) {
		throw OSErrorException("pthread_cond_destroy error.");
	}
#endif
}

void Condition::wait() {
#ifdef _WIN32
	if( WAIT_OBJECT_0 != ::WaitForSingleObject(mCond , INFINITE) ) {
		throw OSErrorException( "Condition::wait , WaitForSingleObject INFINITE error." );
	}
#else
	if (0 != ::pthread_cond_wait(&mCond, &mMutex)) {
		throw OSErrorException("Condition::wait , pthread_cond_wait error.");
	}
#endif
}

void Condition::wait(int t) {
#ifdef _WIN32
	DWORD ret = ::WaitForSingleObject(mCond , t * 1000);
	if( WAIT_TIMEOUT == ret ) {
		throw TimeoutException();
	} else if( WAIT_OBJECT_0 != ret ) {
		throw OSErrorException( "Condition::wait , WaitForSingleObject time error." );
	}
#else
	DateTime dt;
	struct timespec abstime = { t + dt.getTimestamp(), 0 };

	int ret = ::pthread_cond_timedwait(&mCond, &mMutex, &abstime);
	if (ETIMEDOUT == ret) {
		throw TimeoutException();
	} else if (0 != ret) {
		throw OSErrorException(
				"Condition::wait , pthread_cond_timedwait error.");
	}
#endif
}

void Condition::notify() {
#ifdef _WIN32
	if( TRUE != ::SetEvent( mCond ) ) {
		throw OSErrorException( "SetEvent error." );
	}
#else
	if (0 != ::pthread_cond_signal(&mCond)) {
		throw OSErrorException("pthread_cond_signal error.");
	}
#endif
}

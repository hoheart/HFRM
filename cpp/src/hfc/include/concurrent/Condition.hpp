#ifndef __CONDITION_HPP__
#define __CONDITION_HPP__

#include "../hfc_def.hpp"

#ifdef _WIN32
#include <windows.h>
#else
#include <pthread.h>
#endif

namespace hfc {
namespace concurrent {

class HFC_API Condition {

protected:

#ifdef _WIN32
	HANDLE mCond;
#else
	pthread_mutex_t mMutex;
	pthread_cond_t mCond;
#endif

public:

	static const int TIME_OUT ;

public:

	Condition();
	virtual ~Condition();

public:

	void wait(int t );
	void wait();

	void notify();
};

}
}
#endif

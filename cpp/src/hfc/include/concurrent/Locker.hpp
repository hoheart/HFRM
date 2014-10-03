#ifndef __LOCKER_HPP__
#define __LOCKER_HPP__

#include "../hfc_def.hpp"

#ifdef _WIN32
#include <windows.h>
#else
#include <pthread.h>
#endif

namespace hfc {
namespace concurrent {

class HFC_API Locker {

public:

	Locker();
	virtual ~Locker();

protected:

	friend class AutoLocker;

	/**
	 * ����������Ϊ˽�У�Ҫ������AutoLocker��
	 */
	void lock(); 

public:

	/**
	 * ����
	 */
	void unlock();

private:

#ifdef _WIN32

	typedef CRITICAL_SECTION TMutex;

#else

	typedef pthread_mutex_t TMutex;

#endif

	TMutex mMutex;

};

}
}

#endif

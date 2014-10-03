#ifndef __SIGNAL_HANDLER_HPP__
#define __SIGNAL_HANDLER_HPP__

#include <hfc/concurrent/Condition.hpp>
using namespace hfc::concurrent;

#ifndef _WIN32

#include <signal.h>

class SignalHandler {

public:

	typedef void Sigfunc(int);

protected:

	static Condition s_oCondition;

public:

	SignalHandler();

	virtual ~SignalHandler() {
	}

public:

	void startHandle();

	void stopHandle();

	void setHandler(int signo);

protected:

	static void defaultHandler(int number);

};

#endif

#endif

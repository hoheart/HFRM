#include "../include/SignalHandler.hpp"

#include <hfc/util/OSErrorException.hpp>
using namespace hfc::util;

#ifndef _WIN32

Condition SignalHandler::s_oCondition;

SignalHandler::SignalHandler() {
}

void SignalHandler::startHandle() {
	s_oCondition.wait();
}

void SignalHandler::setHandler(int signo) {
	int flags = SA_NODEFER;
	struct sigaction act, oact;
	act.sa_handler = defaultHandler;
	sigemptyset(&act.sa_mask);
	act.sa_flags = 0;

	if (signo == SIGALRM) {
#ifdef SA_INTERRUPT
		act.sa_flags |= SA_INTERRUPT;
#endif
	} else {
#ifdef SA_RESTART
		act.sa_flags |= SA_RESTART;
#endif
	}
	act.sa_flags |= flags;

	if (sigaction(signo, &act, &oact) < 0) {
		throw OSErrorException("can not sigaction.");
	}
}

void SignalHandler::defaultHandler(int number) {
	static bool bStopping = false;

	switch (number) {
	case SIGSTOP:
	case SIGKILL:
	case SIGHUP:
	case SIGINT:
	case SIGQUIT:
	case SIGABRT:
	case SIGTERM: {
		if (!bStopping) {
			bStopping = true;
			SignalHandler::s_oCondition.notify();
		}

		break;
	}
	case SIGPIPE: {
		// 截获SIGPIPE信号，防止因为这个信号造成系统退出
		break;
	}
	default:
		break;
	}
}

#endif

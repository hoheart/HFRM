#include "../include/WebIm.hpp"

#include "../include/SignalHandler.hpp"

int main(int argc, char *argv[]) {
	WebIm s;
	s.start();

	SignalHandler h;
	h.setHandler(SIGHUP);
	h.setHandler(SIGINT);
	h.setHandler(SIGQUIT);
	h.setHandler(SIGABRT);
	h.setHandler(SIGTERM);
	h.setHandler(SIGALRM);
	h.setHandler(SIGPIPE);

	h.startHandle();

	return 0;
}

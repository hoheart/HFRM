#include "../include/NetService.hpp"
using namespace hfrm::NetServiceFrame;

#include <hfc/lang/Exception.hpp>
using namespace hfc::lang;

#include <stdio.h>

class ClientTask: public IRunnable {

protected:

	NetService* m_pNetService;
public:

	ClientTask(NetService& service) {
		m_pNetService = &service;
	}
public:

	void run(void* param) {
		Connection c;
		c.connect("127.0.0.1", 5801);
		c.send("asf", 3);
	}

};

int main() {
	try {
		NetService s;

		s.init("*", 5801);
		s.start();

		sleep(99999999);
	} catch (Exception& e) {
		printf("exception:%s", (const char*) (e.m_strErrstr));
	}

	return 0;
}

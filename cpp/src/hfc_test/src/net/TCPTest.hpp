#ifndef __TCP_TEST_HPP__
#define __TCP_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/net/TCPServer.hpp"
using namespace hfc::net;
#include "../../../include/lang/Runnable.hpp"
using namespace hfc::lang;
#include "../../../include/concurrent/ThreadPool.hpp"
using namespace hfc::net;

class TCPTest : public ITest{

protected:

	class Client : public IRunnable{

	public:

		void run(void* pParam = NULL){
			Connection c;
			c.connect( "127.0.0.1" , 18859 );
			c.send( "abcd" , 4 );

			char ch[1024];
			int len = c.recv(ch,sizeof(ch));
			String strC( ch , len );
			if( "efghi" != strC ){
				throw Exception( "receive error." );
			}

			c.send( "end" , 3 );
		}
	};

public :

	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: Tcp Test error." );
		
		return false;
	}
	
	/**
	* 在还没有线程池的情况下，这个得手工测
	*/
	bool testAll(){
		ThreadPool* tp = ThreadPool::Instance();
		tp->startPool();

		const unsigned short usPort = 18859;
		TCPServer oTcpServer;
		oTcpServer.create( "" , usPort);
		oTcpServer.listen();

		Client client;
		tp->addTask( client );

		Connection c;
		oTcpServer.accept(c);
		
		char ch[1024];
		int len = c.recv(ch,sizeof(ch));
		String strC( ch , len );
		if( "abcd" != strC ){
			return false;
		}
		
		c.send( "efghi" , 5 );

		len = c.recv(ch,sizeof(ch));
		strC.assign( ch , len );
		if( "end" != strC ){
			return false;
		}

		return true;
	}

};


#endif
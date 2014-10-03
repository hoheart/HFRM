#ifndef __UDP_TEST_HPP__
#define __UDP_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/net/UDPSocket.hpp"
using namespace hfc;
using namespace hfc::net;

class UDPTest : public ITest{

public :

	bool test(){
		if( testAll() 
			){
			return true;
		}
		
		logError( "test class: UDP Test error." );
		
		return false;
	}
	
	/**
	* 在还没有线程池的情况下，这个得手工测
	*/
	bool testAll(){return true;
		UDPSocket s;
		s.create( "*" , 5988 );

		char arr[1024] = {0};
		String strIp;
		unsigned short port = 0;
		while(true){
			int recvLen = s.recv( strIp , port , arr , sizeof(arr) );
			s.send( strIp , port , arr , recvLen );
		}


		return true;
	}
	


};


#endif
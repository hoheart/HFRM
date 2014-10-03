#ifndef __NET_TEST_HPP__
#define __NET_TEST_HPP__

#include "../ITest.hpp"

#include "TCPTest.hpp"
#include "UDPTest.hpp"

class NetTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new TCPTest() , new UDPTest() };

		for( int i = 0; i < sizeof(a) / sizeof(a[0]) ; ++ i ){
			if( ! a[i]->test() ){
				logError( "\r\n************************************\r\n" );
				return false;
			}
		}

		return true;
	}
};

#endif
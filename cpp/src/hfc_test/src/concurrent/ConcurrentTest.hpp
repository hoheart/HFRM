#ifndef __CONCURRENT_TEST_HPP__
#define __CONCURRENT_TEST_HPP__

#include "../ITest.hpp"

#include "ThreadPoolTest.hpp"

class ConcurrentTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new ThreadPoolTest() };

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
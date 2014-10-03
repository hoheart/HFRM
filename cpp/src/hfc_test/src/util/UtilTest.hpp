#ifndef __UTIL_TEST_HPP__
#define __UTIL_TEST_HPP__

#include "../ITest.hpp"

#include "RandomTest.hpp"
#include "DateTimeTest.hpp"

class UtilTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new RandomTest() , new DateTimeTest() };

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
#ifndef __IO_TEST_HPP__
#define __IO_TEST_HPP__

#include "../ITest.hpp"

#include "AbstractFileTest.hpp"
#include "FileTest.hpp"

class IOTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new AbstractFileTest() , new FileTest() };

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


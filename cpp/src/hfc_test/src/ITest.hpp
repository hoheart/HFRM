#ifndef __ITEST_HPP__
#define __ITEST_HPP__

#include <stdio.h>


class ITest{

public :

	virtual ~ITest(){
	}

	virtual bool test() = 0;

	void logError( const char* err ){
		printf( "%s error." , err );
	}
};

#endif
#ifndef __LANG_TEST_HPP__
#define __LANG_TEST_HPP__

#include "../ITest.hpp"

#include "CharacterTest.hpp"
#include "IntegerTest.hpp"
#include "StringTest.hpp"

class LangTest : public ITest{

public :

	bool test(){
		ITest*  a[] = { new CharacterTest() , new IntegerTest() , new StringTest() };

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
#ifndef __RANDOM_TEST_HPP__
#define __RANDOM_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/util/Random.hpp"
using namespace hfc;
using namespace hfc::util;

class RandomTest : public ITest{
	
public:
	
	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: Random error." );
		
		return false;
	}
	
	
	bool testAll()
	{
		Random r(1120);
		int iCount = 0;
		for( int i = 0; i < 1000; ++ i ){
			int ret = r.next( 1 , 1000 );
			if( 88 == ret || 900 == ret || 1 == ret || 1000 == ret || 3 == ret || 987 == ret ){
				++ iCount;
			}
		}

		//出现的概率
		if( iCount > 2 && iCount < 10 ){
			return true;
		}


		return false;
	}

};

#endif
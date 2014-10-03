#ifndef __THREAD_POOL_TEST_HPP__
#define __THREAD_POOL_TEST_HPP__

#include "../ITest.hpp"

//#include "../../../include/crypto/Md5Encryption.hpp"
//using namespace hfc;
//using namespace hfc::crypto;

class ThreadPoolTest : public ITest{

public :

	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: ThreadPool error." );
		
		return false;
	}
	
	bool testAll(){
	

		return true;
	}

	


};


#endif
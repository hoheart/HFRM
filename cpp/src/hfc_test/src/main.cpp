#include "ITest.hpp"

#include "lang/LangTest.hpp"
#include "net/NetTest.hpp"
#include "crypto/CryptoTest.hpp"
#include "util/UtilTest.hpp"
#include "io/IOTest.hpp"
#include "concurrent/ConcurrentTest.hpp"

int main(){
	ITest*  a[] = { new LangTest() , new NetTest() , new CryptoTest() , new UtilTest() , 
		new IOTest() , new ConcurrentTest() };

	for( int i = 0; i < sizeof(a) / sizeof(a[0]) ; ++ i ){
		a[i]->test();
	}



	return 0;
}
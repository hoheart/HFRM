#ifndef __INTEGER_TEST_HPP__
#define __INTEGER_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/lang/Integer.hpp"
#include "../../../include/lang/InvalidParameterException.hpp"
#include "../../../include/lang/NotDigitException.hpp"
using namespace hfc;
using namespace hfc::lang;

class IntegerTest : public ITest{

public :

	bool test(){
		if( testConstruct() && testConstruct1()
			&& testAddEqual() && testEqual()
			&& testParse() && testToString()
			){
			return true;
		}
		
		logError( "test class: Integer error." );
		
		return false;
	}
	
	bool testConstruct(){
		Integer i(2);
		if( 2 == (int)i){
			return true;
		}else{
			logError( "test construct error." );
			return false;
		}
	}
	
	bool testConstruct1(){
		Integer c(3);
		Integer c1(c);
		if( 3 == (int)c1 ){
			return true;
		}else{
			logError( "test construct1 error." );
			return false;
		}
	}

	bool testAddEqual(){
		Integer i(3);
		i += 4;
		if( 7 != i ){
			return false;
		}

		Integer i1(3);
		i += i1;
		if( 10 != i ){
			return false;
		}

		return true;
	}

	bool testEqual(){
		Integer i(3);
		if( 3 != i ){
			return false;
		}

		Integer i1(3);
		if( i != i1 ){
			return false;
		}

		return true;
	}

	bool testParse(){
		if( 89 != Integer::parse( "89" , 10 ) ){
			return false;
		}
		if( 599 != Integer::parse( "257" , 16 ) ){
			return false;
		}

		return true;
	}

	bool testToString(){
		if( "157" != Integer::toString( 157 , 10 ) ){
			return false;
		}

		if( "-9d" != Integer::toString( -157 , 16 ) ){
			return false;
		}

		if( "235" != Integer::toString( 157 , 8 ) ){
			return false;
		}

		return true;
	}

};


#endif
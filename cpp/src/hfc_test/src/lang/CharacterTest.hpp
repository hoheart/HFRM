#ifndef __CHARACTER_TEST_HPP__
#define __CHARACTER_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/lang/Character.hpp"
#include "../../../include/lang/InvalidParameterException.hpp"
#include "../../../include/lang/NotDigitException.hpp"
using namespace hfc;
using namespace hfc::lang;





class CharacterTest : public ITest{
	
public :
	
	bool test(){
		if( testConstruct() && testConstruct1()
			&& testDigit() && testAddEqual()
			&& testEqual()
			){
			return true;
		}
		
		logError( "test class: Character error." );
		
		return false;
	}
	
	bool testConstruct(){
		Character c('b');
		if( 'b' == (char)c){
			return true;
		}else{
			logError( "test construct error." );
			return false;
		}
	}
	
	bool testConstruct1(){
		Character c('b');
		Character c1(c);
		if( 'b' == (char)c1 ){
			return true;
		}else{
			logError( "test construct1 error." );
			return false;
		}
	}
	
	bool testDigit(){
		//测试参数检查报错
		bool ret = false;
		try{
			Character::digit( 'c' , Character::MIN_RADIX - 1 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch( Exception e ){
			return false;
		}
		if( ! ret ){
			return false;
		}
		
		try{
			Character::digit( 'c' , Character::MAX_RADIX + 1 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch( Exception e ){
			return false;
		}
		if( ! ret ){
			return false;
		}
		
		//检查数字转换范围
		char cArr1[] = {'0' - 1 , '9' + 1 , 'a' - 1 , 'z' + 1 , 'A' - 1 , 'Z' + 1};
		for( int i = 0; i < sizeof(cArr1) / sizeof(cArr1[0]); ++ i ){
			try{
				Character::digit( cArr1[i] , Character::MAX_RADIX );
			}catch(NotDigitException e){
				ret = true;
			}catch( Exception e ){
				return false;
			}

			if( ! ret ){
				return false;
			}
		}
		
		char cArr[] = {'0' , '1' , '2' , '3' , '4' , '5' , '6' , '7' , '8' , '9' , 'a' , 'b' , 'c' , 'd', 'e' , 'f' , 'g' , 'h', 'i' , 'j' , 'k' , 'l', 'm' , 'n' , 'o' , 'p', 'q' , 'r' , 's' , 't', 'u' , 'v' , 'w' , 'x', 'y' , 'z'} ;
		for( int r = Character::MIN_RADIX ; r <= Character::MAX_RADIX; ++ r ){//测16进制就行了
			for( int d = 0; d < r; ++ d ){
				if( d != Character::digit( cArr[d] , r )){
					char err[100] = {0};
					sprintf( err , "test digit error. when char: %d , radix: %d" , d , r );
					logError( err );
					
					return false;
				}
			}
		}
		
		return true;
	}
	
	bool testAddEqual(){
		Character c('b');
		c += 'a';
		if( 'b' + 'a' != (unsigned char)c ){
			return false;
		}
		
		c += 3;
		if( 'b' + 'a' + 3 != (unsigned char)c ){
			return false;
		}
		
		
		
		Character c1('a');
		Character c2('b');
		c1 += c2;
		if( 'a' + 'b' != (unsigned char)c1 ){
			return false;
		}
		
		return true;
	}

	bool testEqual(){
		Character c( 'a' );
		Character c1( 'a' );
		if( c != c1 ){
			return false;
		}

		if( c != 'a' ){
			return false;
		}

		return true;
	}
};

#endif
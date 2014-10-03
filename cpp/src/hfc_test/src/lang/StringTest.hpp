#ifndef __STRING_TEST_HPP__
#define __STRING_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/lang/Integer.hpp"
#include "../../../include/lang/InvalidParameterException.hpp"
#include "../../../include/lang/NotDigitException.hpp"
using namespace hfc;
using namespace hfc::lang;

class StringTest : public ITest{

public :

	bool test(){
		if( testConstruct() && testCharAt()
			&& testCopyOnWrite() && testFind()
			&& testMakeLower() && testMakeUpper()
			&& testOperationAssign() && testReplace()
			&& testRFind() && testSubstr()
			){
			return true;
		}
		
		logError( "test class: String error." );
		
		return false;
	}
	
	bool testConstruct(){
		String s;
		const char* sz = (const char*)s;
		if( 0 != sz[0] || 0 != s.length() ){
			return false;
		}

		String s1( "df" );
		sz = (const char*)s1;
		if( 'd' != sz[0] || 'f' != sz[1] || 2 != s1.length() ){
			return false;
		}

		String s2( "dffdfds" , 2 );
		sz = (const char*)s2;
		if( 'd' != sz[0] || 'f' != sz[1] || 2 != s2.length() ){
			return false;
		}

		//测试是否可以包含0
		String s4( "dffd\0fds" , 7 );
		sz = (const char*)s4;
		if( 'd' != sz[0] || 'f' != sz[1] || 7 != s4.length() ){
			return false;
		}

		String s3 = s2;
		sz = (const char*)s2;
		if( 'd' != sz[0] || 'f' != sz[1] || 2 != s3.length() ){
			return false;
		}

		return true;
	}

	bool testCharAt(){
		char arr[] = "1234567890abcdefghijklmnopqrstuvwxyz";
		String s(arr);
		for( int i = 0; i < sizeof(arr) / sizeof(arr[0]); ++ i ){
			if( arr[i] != s.charAt(i) ){
				return false;
			}
		}

		return true;
	}

	bool testCopyOnWrite(){
		//1.写时是否拷贝了；
		String* pS = new String( "hj" );
		String s1 = *pS;
		s1 += "a";

		if( 2 != pS->length() || 'h' != pS->charAt(0) || 'j' != pS->charAt(1) ){
			return false;
		}

		if( 3 != s1.length() || 'h' != s1.charAt(0) || 'j' != s1.charAt(1) || 'a' != s1.charAt(2) ){
			return false;
		}

		//2.不拷贝时，释放第一个，是否把内存删了
		String s2 = *pS;
		delete pS;
		if( 2 != s2.length() || 'h' != s2.charAt(0) || 'j' != s2.charAt(1) ){
			return false;
		}

		return true;
	}

	bool testFind(){
		String s( "abcd\0cdecdf" , 11 );
		if( 2 != s.find( "cd\0" , 3 , 0 ) ){
			return false;
		}

		if( String::npos != s.find( "cd\0" , 3 , 3 ) ){
			return false;
		}

		if( 8 != s.find( "cdff" , 2 , 6 ) ){
			return false;
		}

		return true;
	}

	bool testMakeLower(){
		String s( "AbCdD\0l23dFf00" , 14 );
		s.makeLower();
		if( 'c' != s.charAt(2) ){
			return false;
		}

		if( '\0' != s.charAt(5) ){
			return false;
		}

		if( 'f' != s.charAt(10) ){
			return false;
		}

		return true;
	}

	bool testMakeUpper(){
		String s( "AbCdD\0l23dFf00" , 14 );
		s.makeUpper();
		if( 'B' != s.charAt(1) ){
			return false;
		}

		if( '\0' != s.charAt(5) ){
			return false;
		}

		if( 'F' != s.charAt(11) ){
			return false;
		}

		return true;
	}

	bool testOperationAssign(){
		String s = "adf";
		String s1 = s;
		if( 'a' != s1.charAt(0) || 3 != s1.length() ){
			return false;
		}

		return true;
	}

	bool testReplace(){
		String s( "ab" );
		bool ret = false;
		try{
			s.replace( -1  , 0 , NULL , 0 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch(Exception e){
			return false;
		}
		if( ! ret ){
			return false;
		}

		try{
			s.replace( 0  , -2 , NULL , 0 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch(Exception e){
			return false;
		}
		if( ! ret ){
			return false;
		}

		try{
			s.replace( 0  , 0 , NULL , -1 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch(Exception e){
			return false;
		}
		if( ! ret ){
			return false;
		}

		try{
			s.replace( 0  , 3 , NULL , -1 );
		}catch(InvalidParameterException e){
			ret = true;
		}catch(Exception e){
			return false;
		}
		if( ! ret ){
			return false;
		}

		char c = 'c';
		s.replace( 0 , 0 , &c , 1 );
		if( 3 != s.length() || 'c' != s.charAt(0) ){
			return false;
		}
		s = "ab";
		s.replace( 0 , 1 , "cdef" , 2 );
		if( 3 != s.length() || 'c' != s.charAt(0) || 'd' != s.charAt(1) ){
			return false;
		}

		s = "abcdef";
		s.replace( 2 , 2 , "11" , 2 );
		if( 6 != s.length() || '1' != s.charAt(2) || '1' != s.charAt(3) ){
			return false;
		}

		s.replace( 6 , 4 , "22" , 1 );
		if( 7 != s.length() || '2' != s.charAt(6) ){
			return false;
		}

		return true;
	}

	bool testRFind(){
		String s( "abcd\0cd\0cdecdf" , 11 );
		if( 5 != s.rfind( "cd\0" , 3 , 0 ) ){
			return false;
		}

		if( String::npos != s.rfind( "cd\0" , 3 , 3 ) ){
			return false;
		}

		if( 5 != s.rfind( "cdff" , 2 , 6 ) ){
			return false;
		}

		return true;
	}

	bool testSubstr(){
		String s( "abcdefg" );
		String sub = s.substr( 2 , 2 );
		if( 'c' != sub.charAt( 0 ) || 'd' != sub.charAt( 1 )  || 2 != sub.length() ){
			return false;
		}

		return true;
	}

	bool testTrim(){
		String s( " \f\n\r\t\vabc \f\n\r\t\v" );
		s.trim();
		if( 3 != s.length() || "abc" != s ){
			return false;
		}

		return true;
	}
	


};


#endif
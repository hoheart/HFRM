#ifndef __FILE_TEST_HPP__
#define __FILE_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/io/File.hpp"
#include "../../include/io/InvalidFileException.hpp"
using namespace hfc;
using namespace hfc::io;

class FileTest : public ITest{
	
public:
	
	bool test(){
		if( testConstruct() && testOthers()
			){
			return true;
		}
		
		logError( "test class: File error." );
		
		return false;
	}
	
	
	bool testConstruct()
	{
#ifdef _WIN32
		const char* szPath = "c:\\windows";
#else
		const char* szPath = "/usr/lib";
#endif
		
		File f(szPath);
		if( szPath != f.getPath() ){
			return false;
		}

		File f1("");

		return true;
	}

	bool testOthers(){
#ifdef _WIN32
		const char* szPath = "c:\\windows\\a.txt";
#else
		const char* szPath = "/home/a.txt";
#endif
		
		File f(szPath);
		f.create();

		String s( "1234567890\0" , 11 );
		f.write( s );

		char data[1024];
		f.seek( 0 , File::Begin );
		int iReadLen = f.read( data , sizeof(data) );
		String strRead(data , iReadLen );
		if( strRead != s ){
			return false;
		}

		if( 11 != f.getLength() ){
			return false;
		}


		File f1( "abc" );
		try{
		f1.read( data , sizeof(data));
		}catch(InvalidFileException e){
		}catch(Exception e1){
			return false;
		}

		try{
		f1.read( "a" , 1);
		}catch(InvalidFileException e){
		}catch(Exception e1){
			return false;
		}

		return true;
	}

};

#endif
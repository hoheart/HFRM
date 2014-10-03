#ifndef __ABSTRACT_FILE_TEST_HPP__
#define __ABSTRACT_FILE_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/io/AbstractFile.hpp"
using namespace hfc;
using namespace hfc::io;

class AbstractFileTest : public ITest{
	
public:
	
	bool test(){
		if( testConstruct() && testOthers()
			){
			return true;
		}
		
		logError( "test class: AbstractFile error." );
		
		return false;
	}
	
	
	bool testConstruct()
	{
#ifdef _WIN32
		const char* szPath = "c:\\windows";
#else
		const char* szPath = "/usr/lib";
#endif
		
		AbstractFile f(szPath);
		if( szPath != f.getPath() ){
			return false;
		}

		AbstractFile f1("");

		return true;
	}

	bool testOthers(){
#ifdef _WIN32
		const char* szPath = "c:\\windows\\hh.exe";
		const char* szBaseName = "hh.exe";
		const char* szDirName = "c:\\windows\\";
		const char* szExtName = "exe";
#else
		const char* szPath = "/usr/lib/libmemusage.so";
		const char* szBaseName = "libmemusage.so";
		const char* szDirName = "/usr/lib/";
		const char* szExtName = "so";
#endif
		
		AbstractFile f(szPath);
		
		if( szBaseName != f.basename() ){
			return false;
		}

		if( szDirName != f.dirName() ){
			return false;
		}


		AbstractFile f1("asdfsf");
		if( "asdfsf" != f1.basename() ){
			return false;
		}
		if( "" != f1.dirName() ){
			return false;
		}
		if( f1.exists() ){
			return false;
		}

		if( ! f.exists() ){
			return false;
		}

		if( szExtName != f.extName() ){
			return false;
		}

		if( f.isDir() ){
			return false;
		}

		f.setPath( szDirName );
		if( ! f.isDir() ){
			return false;
		}

		return true;
	}

};

#endif
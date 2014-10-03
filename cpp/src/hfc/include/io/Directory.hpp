#ifndef __DIRECTORY_HPP__
#define __DIRECTORY_HPP__

#include "AbstractFile.hpp"
using hfc::io;

class Directory : public AbstractFile{

public:

	Directory(){
	}
	Directory( const String& strPath ){
	}
	~Directory();

public:


};
#endif
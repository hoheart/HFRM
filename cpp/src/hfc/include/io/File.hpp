#ifndef __FILE_HPP__
#define __FILE_HPP__

#include "../hfc_def.hpp"

#ifndef _WIN32
#include <unistd.h>
#include <fcntl.h>
#endif

#include "../io/AbstractFile.hpp"
using namespace hfc::io;
#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc {
namespace io {

class HFC_API File: public AbstractFile {

public:

	typedef enum PointerPosition {
#ifdef _WIN32
		Begin = FILE_BEGIN,
		Current = FILE_CURRENT ,
		End = FILE_END
#else
		Begin = SEEK_SET, Current = SEEK_CUR, End = SEEK_END
#endif
	}PointerPosition;

public:

	File(const String& strPath);
	virtual ~File();

public:

	static int GetLength(const String& strPath) {
		return GetLength(strPath, INVALID_FILE_DESCRIPTOR);
	}
	int getLength() {
		return GetLength(m_strPath);
	}

	int seek(const int iLen, const PointerPosition origin);

	int read(void* data, const int len);
	String read(const int len = -1){
		int iLen = len;
		if( -1 == iLen ){
			iLen = getLength();
		}
		char* p = new char[iLen];
		int readLen = read(p, len);
		
		String bin(p, readLen);
		delete p;
		
		return bin;
	}

	int write(const void* data, const int len);
	int write(const String& bin){
		return write(bin, bin.length());
	}

	static bool CopyFile(const String& strPath, const String& strNewPath);

	void create();

protected:

	typedef int OpenMode;

	static const OpenMode NotOpen;
	static const OpenMode Read;
	static const OpenMode Write;

protected:

	static int GetLength(const String::t_char* szPath, const FileDescriptor& d);

	static FileDescriptor CreateDescriptor(const String::t_char* szPath,
			const OpenMode& mode);

protected:

	OpenMode m_openMode;
};

}
}

#endif

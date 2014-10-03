#include "../../include/io/File.hpp"
using namespace hfc::io;

#include "../../include/util/OSErrorException.hpp"
using namespace hfc::util;
#include "../../include/util/OutOfMemoryException.hpp"
#include "../../include/io/InvalidFileException.hpp"
using namespace hfc::io;

const File::OpenMode File::NotOpen = 0;
#ifdef _WIN32
const File::OpenMode File::Read = GENERIC_READ;
const File::OpenMode File::Write = GENERIC_WRITE;
#else
const File::OpenMode File::Read = O_RDONLY;
const File::OpenMode File::Write = O_RDWR;
#endif


File::File(const String& strPath) :
	m_openMode(NotOpen), AbstractFile(strPath) {
}

File::~File() {
}

int File::seek(const int iLen, const PointerPosition origin) {
	if (INVALID_FILE_DESCRIPTOR == m_descriptor) {
		m_descriptor = CreateDescriptor(m_strPath, NotOpen);
	}

	int pos = 0;
#ifdef _WIN32
	pos = ::SetFilePointer( m_descriptor , iLen , 0 , origin );
	if( -1 == pos ) {
		throw OSErrorException( "seek failed." );
	}
#else
	pos = ::lseek(m_descriptor, iLen, origin);
	if (-1 == pos) {
		throw OSErrorException("seek failed.");
	}
#endif

	return pos;
}

int File::read(void* data, const int len) {
	//在重新创建handle前，要保留现在的指针位置。
	int iPos = 0;

	if (Read != (Read & m_openMode)) {
		if (INVALID_FILE_DESCRIPTOR != m_descriptor) {
			iPos = seek(0, Current);

			ReleaseDescriptor( m_descriptor);
		}

		OpenMode m = m_openMode | Read;
		m_descriptor = CreateDescriptor(m_strPath, m);
		m_openMode = m;

		//还原指针位置
		seek(iPos, Begin);
	}

	int iReadLen = len;
	if (iReadLen < 0) {
		iReadLen = getLength();
	}

#ifdef _WIN32
	::ReadFile( m_descriptor , data , iReadLen , (unsigned long*)&iReadLen , NULL );
#else
	iReadLen = ::read(m_descriptor, data, len);
#endif

	return iReadLen;
}

int File::write(const void* data, const int len) {
	//在重新创建handle前，要保留现在的指针位置。
	int iPos = 0;

	if (Write != (Write & m_openMode)) {
		if (INVALID_FILE_DESCRIPTOR != m_descriptor) {
			iPos = seek(0, Current);

			ReleaseDescriptor( m_descriptor);
		}

		OpenMode m = m_openMode | Write;
		m_descriptor = CreateDescriptor(m_strPath, m);
		m_openMode = m;

		//还原指针位置
		seek(iPos, Begin);
	}

	int iWriteLen = len;

#ifdef _WIN32
	::WriteFile( m_descriptor , data , iWriteLen , (unsigned long*)&iWriteLen , NULL );
#else
	iWriteLen = ::write(m_descriptor, data, iWriteLen);
#endif

	return iWriteLen;
}

bool File::CopyFile(const String& strPath, const String& strNewPath) {
#ifdef _WIN32
	return TRUE == ::CopyFile( strPath , strNewPath , FALSE );
#else
	File f(strPath);
	File fNew(strNewPath);
	fNew.create();

	char tmp[10240] = { 0 };

	while (true) {
		//一次性只有拷贝一部分拷贝成功，也视为成功。
		int iRead = f.read(tmp, sizeof(tmp));
		if (iRead < 0) {
			return false;
		}

		if (fNew.write(tmp, iRead) < 0) {
			return false;
		}
	}

	return true;
#endif
}

void File::create() {
#ifdef _WIN32
	m_descriptor = ::CreateFile(m_strPath, Read | Write, 0, NULL, OPEN_ALWAYS, 0, NULL);
#else
	m_descriptor = ::open(m_strPath, Read | Write);
#endif

	if (INVALID_FILE_DESCRIPTOR == m_descriptor) {
		throw InvalidFileException();
	}

	m_openMode = Read | Write;
}

int File::GetLength(const String::t_char* szPath, const FileDescriptor& d) {
	FileDescriptor h = d;
	if (INVALID_FILE_DESCRIPTOR == h) {
		h = AbstractFile::CreateDescriptor(szPath);
		if (INVALID_FILE_DESCRIPTOR == h) {
			throw InvalidFileException();
		}
	}
#ifdef _WIN32
	int iSize = ::GetFileSize( h , NULL );

	if( INVALID_FILE_DESCRIPTOR == d && INVALID_FILE_DESCRIPTOR != h ) {
		CloseHandle( h );
	}
#else
	lseek(h, 0, Begin);
	int iSize = lseek(h, 0, End);
	if (INVALID_FILE_DESCRIPTOR == d && INVALID_FILE_DESCRIPTOR != h) {
		ReleaseDescriptor(h);
	} else {
		lseek(h, 0, Begin);
	}
#endif

	return iSize;
}

File::FileDescriptor File::CreateDescriptor(const String::t_char* szPath,
		const OpenMode& openMode) {
#ifdef _WIN32
	FileDescriptor d = ::CreateFile(szPath, openMode, 0, NULL, OPEN_EXISTING, 0, NULL);
#else
	FileDescriptor d = ::open(szPath, openMode);
#endif

	if (INVALID_FILE_DESCRIPTOR == d ){
		throw InvalidFileException();
	}

	return d;
}

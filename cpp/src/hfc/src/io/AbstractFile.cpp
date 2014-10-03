#include "../../include/io/AbstractFile.hpp"
using namespace hfc::io;

#ifndef _WIN32
#include <fcntl.h>//for open use in CreateDescriptor
#include <sys/stat.h>//for fstat use in IsDir
#include <unistd.h>//for close use in ReleaseDescriptor
#endif

#include "../../include/io/InvalidFileException.hpp"

#ifdef _WIN32
const AbstractFile::FileDescriptor AbstractFile::INVALID_FILE_DESCRIPTOR = INVALID_HANDLE_VALUE;
#else
const AbstractFile::FileDescriptor AbstractFile::INVALID_FILE_DESCRIPTOR = -1;
#endif


AbstractFile::AbstractFile(const String& strPath) :
	m_descriptor(INVALID_FILE_DESCRIPTOR) {
	m_strPath = strPath;
}

AbstractFile::~AbstractFile() {
	ReleaseDescriptor( m_descriptor);
}

int AbstractFile::FindDirPos(const String& strPath) {
	int pos = strPath.find_last_of('/');
	if (String::npos == pos) {
		pos = strPath.find_last_of('\\');
	}

	return pos;
}

String AbstractFile::ExtName(const String& strPath) {
	int index1 = FindDirPos(strPath);
	int index2 = strPath.find_last_of(".");
	if (index2 < index1) {//可能文件夹也包含“.”
		return "";
	}

	return strPath.substr(index2 + 1);
}

String AbstractFile::Basename(const String& strPath) {
	return strPath.substr(FindDirPos(strPath) + 1);
}

String AbstractFile::Dirname(const String& strPath) {
	return strPath.substr(0, FindDirPos(strPath) + 1);
}

void AbstractFile::setPath(const String& strPath) {
	if (INVALID_FILE_DESCRIPTOR != m_descriptor) {
		ReleaseDescriptor( m_descriptor);
	}

	m_strPath = strPath;
}

bool AbstractFile::Exists(const String::t_char* szPath) {
	FileDescriptor h = CreateDescriptor(szPath);
	if (INVALID_FILE_DESCRIPTOR == h) {
		return false;
	}

	ReleaseDescriptor(h);

	return true;
}

bool AbstractFile::IsDir(const String::t_char* szPath) {
#ifdef _WIN32
	return FILE_ATTRIBUTE_DIRECTORY == (::GetFileAttributes(szPath)
			& FILE_ATTRIBUTE_DIRECTORY);
#else
	FileDescriptor d = CreateDescriptor(szPath);

	struct stat st;
	::fstat(d, &st);

	bool ret ;
	if (S_IFDIR == (S_IFDIR & st.st_mode)) {
		ret = true;
	}else{
		ret = false;
	}

	ReleaseDescriptor(d);

	return false;
#endif
}

AbstractFile::FileDescriptor AbstractFile::CreateDescriptor(const String::t_char* szPath) {
#ifdef _WIN32
	FileDescriptor d =
			::CreateFile(szPath, 0, 0, NULL, OPEN_EXISTING, 0, NULL);
#else
	FileDescriptor d = ::open(szPath, 0, 0);
#endif

	return d;
}

void AbstractFile::ReleaseDescriptor(AbstractFile::FileDescriptor d) {
#ifdef _WIN32
	::CloseHandle(d);
#else
	::close(d);
#endif
}

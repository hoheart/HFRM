#ifndef __ABSTRACT_FIlE_HPP__
#define __ABSTRACT_FIlE_HPP__

#include "../hfc_def.hpp"

#ifdef _WIN32
#include <windows.h>
#endif

#include "../lang/String.hpp"
using namespace hfc::lang;

namespace hfc {
namespace io {

class HFC_API AbstractFile {

public:

	AbstractFile(const String& szPath);
	virtual ~AbstractFile();

public:

	void setPath(const String& strPath);
	String getPath() const {
		return m_strPath;
	}

	/**
	* @param const String::t_char* szPath 文件路径。此处参数没用String类型，
	*    因为函数里没有用到String，所以为了节约字符串的时间，用char*
	*/
	static bool Exists(const String::t_char* szPath);
	bool exists() const {
		return Exists(m_strPath);
	}

	static bool IsDir(const String::t_char* szPath);
	bool isDir() {
		return IsDir(m_strPath);
	}

	/**
	 * 取得路径中的文件名部份，不包含文件夹。
	 *
	 * @param const String& strPath 文件路径。此处参数用String类型，因为函数里用到了subString，
	 *    所以，如果外界传入也用String类型，就直接引用，省去了转成char*在转成String拷贝字符串的时间。
	 */
	static String Basename(const String& strPath);
	String basename() const {
		return Basename(m_strPath);
	}

	/**
	 * 取得路径中的文件夹名部份。
	 */
	static String Dirname(const String& strPath);
	String dirName() const {
		return Dirname(m_strPath);
	}

	static String ExtName(const String& strPath);
	String extName() const {
		return ExtName(m_strPath);
	}

protected:

#ifdef _WIN32
	typedef HANDLE FileDescriptor;
#else
	typedef int FileDescriptor;
#endif

	static FileDescriptor CreateDescriptor(const String::t_char* szPath);

	static void ReleaseDescriptor(FileDescriptor d);

	/**
	 * 找出路径里文件夹符号所在的位置（当前文件夹）。
	 */
	static int FindDirPos(const String& strPath);

protected:

	static const FileDescriptor INVALID_FILE_DESCRIPTOR;

protected:

	String m_strPath;

	FileDescriptor m_descriptor;
};

}
}

#endif

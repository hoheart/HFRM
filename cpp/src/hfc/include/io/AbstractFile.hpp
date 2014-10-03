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
	* @param const String::t_char* szPath �ļ�·�����˴�����û��String���ͣ�
	*    ��Ϊ������û���õ�String������Ϊ�˽�Լ�ַ�����ʱ�䣬��char*
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
	 * ȡ��·���е��ļ������ݣ��������ļ��С�
	 *
	 * @param const String& strPath �ļ�·�����˴�������String���ͣ���Ϊ�������õ���subString��
	 *    ���ԣ������紫��Ҳ��String���ͣ���ֱ�����ã�ʡȥ��ת��char*��ת��String�����ַ�����ʱ�䡣
	 */
	static String Basename(const String& strPath);
	String basename() const {
		return Basename(m_strPath);
	}

	/**
	 * ȡ��·���е��ļ��������ݡ�
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
	 * �ҳ�·�����ļ��з������ڵ�λ�ã���ǰ�ļ��У���
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

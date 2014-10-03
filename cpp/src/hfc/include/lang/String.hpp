#ifndef __STRING_HPP__
#define __STRING_HPP__

#include "../hfc_def.hpp"

namespace hfc {
namespace lang{

/**
 * ����д�ɶ��󴮵ģ�û���뵽Ч�ʱȽϸߵķ�ʽ�����Ǳ����ʱ��ȷ���ַ����
 * ����ĳ����ͷţ�release��������������ڴ�Ĳ�������replace�replace������init��copyOnWrite�������������ܵ���init��copyOnWrite��
 * �ַ����п��԰���0
 */
class HFC_API String {

public:

#ifdef  UNICODE
	typedef short t_char;
#else
	typedef char t_char;
#endif

#ifdef  UNICODE
	typedef unsigned short t_uchar;
#else
	typedef unsigned char t_uchar;
#endif

public:

	static const int npos;
	static const t_char WhiteSpaceCharArr[];

public:

	String();
	String(const t_char c);
	String(const t_char* sz);
	String(const t_char* sz, const int len);
	String(const String& str);

	virtual ~String();

public:

	static int Strlen(const t_char* sz);

public:

	int length() const {
		return m_pInfo->iStrSize;
	}
	int size() const {
		return length();
	}

	const t_char* c_str() const {
		return m_pInfo->szStr;
	}

	String substr(const int iOff, const int iCount) const;
	String substr(const int iOff) const {
		return substr(iOff, m_pInfo->iStrSize - iOff);
	}

	char charAt (const int i) const{
		return m_pInfo->szStr[i];
	}
	char at(const int i) const{
		return charAt( i );
	}

	/**
	* const t_char* des Ŀ���ַ���
	* const int len Ŀ���ַ�������
	* const int iOff ���ַ����п�ʼ���ҵ�λ��
	*/
	int find(const t_char* des, const int len, const int iOff) const ;
	int find(const t_char* des, const int iOff = 0) const{
		int len = Strlen(des);
		return find( des , len , iOff );
	}
	int find(const t_char c, const int iOff = 0) const {
		return find(&c, sizeof(c), iOff);
	}

	int rfind(const t_char* des , const int len , const int iOff ) const;
	int rfind(const t_char* des, const int iOff = 0) const{
		return rfind( des , Strlen( des ) , iOff );
	}
	int rfind(const t_char c, const int iOff = 0) const {
		return rfind(&c , sizeof(c) , iOff);
	}
	int find_last_of(const t_char* des, const int iOff = -1) const {
		return rfind(des, Strlen( des ) , iOff);
	}
	int find_last_of(const t_char c, const int iOff = -1) const {
		return rfind(&c , sizeof(c) , iOff );
	}

	/**
	* const int iPos ��ʼ�滻��λ��
	* const int iCount Ҫ�滻���ַ�����
	* const t_char* str �滻֮����ַ���
	* const int iSize �滻֮����ַ�������
	*/
	String& replace(const int iPos, const int iCount, const t_char* str,
			const int iSize);
	String& replace(const int iPos, const int iCount, const t_char* str) {
		return replace(iPos, iCount, str, Strlen(str));
	}
	String& replace(const int iPos, const int iCount, const t_char c) {
		return replace(iPos, iCount, &c, sizeof(c));
	}
	String& replace(const t_char* oldStr, const t_char* newStr){
		int iPos ;
		while( npos != ( iPos = find( oldStr , iPos ) ) ){
			replace(iPos, Strlen(oldStr), newStr, Strlen(newStr));
		}

		return *this;
	}

	void init(const t_char* str, const int iStrLen);

	String& assign(const t_char* sz, const int iSize) {
		return replace(0, m_pInfo->iStrSize, sz, iSize);
	}

	String& insert(const int pos, const t_char* ptr) {
		return replace(pos, 0, ptr, Strlen(ptr));
	}
	String& insert(const int pos, const t_char c) {
		return replace(pos, 0, &c, sizeof(c));
	}
	String& insert(const int pos , const t_char* ptr, const int len) {
		return replace(pos , 0, ptr, len);
	}

	String& append( const t_char* ptr , const int len ){
		return replace( m_pInfo->iStrSize , 0 , ptr , len );
	}

	String& makeLower();
	String& makeUpper();

	String& trim();
	String& trimLeft();
	String& trimRight();

public:

	operator const t_char*() const {
		return c_str();
	}

	String& operator +=(const t_char c) {
		return replace(m_pInfo->iStrSize, 0, &c, sizeof(c));
	}
	/**
	* ��iת���ַ������൱��sprintf��%d
	*/
	String& operator +=(const int i);
	String& operator +=(const t_char* sz) {
		return replace(m_pInfo->iStrSize, 0, sz, Strlen(sz));
	}
	String& operator +=(const String& str) {
		return replace(m_pInfo->iStrSize, 0, str.m_pInfo->szStr, str.m_pInfo->iStrSize);
	}

	String& operator =(const String& str);

	bool operator ==(const t_char* sz) const{
		return *this == String(sz);
	}
	bool operator !=(const t_char* sz) const{
		return !(*this==sz);
	}

	bool operator ==(const String& str) const;
	bool operator ==(const t_char c) const{
		return 1 == m_pInfo->iStrSize && c == m_pInfo->szStr[0];
	}
	bool operator !=(const String& str) const{
		return !(*this==str);
	}
	bool operator !=(const t_char c) const{
		return 1 != m_pInfo->iStrSize || c != m_pInfo->szStr[0];
	}

	bool operator < ( const String& str ) const;

	bool operator > ( const String& str ) const;

protected:

	typedef struct StrInfo {
		int iStrSize;
		int iMemSize;
		int iRefCount;
		t_char szStr[1];
	} StrInfo;

protected:

	void release();
	bool copyOnWrite();

protected:

	/**
	 *�õ���copy on write���������ԣ��õ�ַ����ָ����string���󴴽����ڴ��ϡ�
	 */
	StrInfo* m_pInfo;
};

inline bool HFC_API operator ==(const String::t_char* sz, const hfc::lang::String& str) {
	return str == sz;
}
inline bool HFC_API operator ==(const String::t_char c , const hfc::lang::String& str){
	return str == c;
}
inline bool HFC_API operator !=(const String::t_char* sz, const hfc::lang::String& str) {
	return str != sz;
}
inline bool HFC_API operator !=(const String::t_char c, const hfc::lang::String& str) {
	return str != c;
}

inline String HFC_API operator +(const String& str, const String& str1) {
	String s = str;
	return s += str1;
}

}
}
#endif

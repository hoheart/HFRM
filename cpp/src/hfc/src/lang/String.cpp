#include "../../include/lang/String.hpp"
using namespace hfc::lang;

#include <stdlib.h>
#include <string.h>
#include <stdio.h>

#include "../../include/util/OutOfMemoryException.hpp"
using namespace hfc::util;
#include "../../include/lang/InvalidParameterException.hpp"
#include "../../include/lang/Integer.hpp"

const int String::npos = -1;

const String::t_char String::WhiteSpaceCharArr[] = { ' ', '\f', '\n', '\r',
		'\t', '\v' };

String::String() :
	m_pInfo(NULL) {
	replace(0, 0, NULL, 0);
}

String::String(const t_char c) :
	m_pInfo(NULL) {
	replace(0, 0, &c, sizeof(t_char));
}

String::String(const t_char* sz) :
	m_pInfo(NULL) {
	replace(0, 0, sz, Strlen(sz));
}

String::String(const t_char* sz, const int len) :
	m_pInfo(NULL) {
	replace(0, 0, sz, len);
}

String::String(const String& str) {
	m_pInfo = str.m_pInfo;

	++m_pInfo->iRefCount;
}

String::~String() {
	release();
}

void String::release() {
	if (NULL != m_pInfo) {
		if (1 == m_pInfo->iRefCount) {
			::free(m_pInfo);

			m_pInfo = NULL;
		} else {
			--m_pInfo->iRefCount;
		}
	}
}

inline int String::Strlen(const t_char* str) {
	int maxlen = 0x1ffffff;
	const char *char_ptr, *end_ptr = str + maxlen;
	const unsigned long int *longword_ptr;
	unsigned long int longword, himagic, lomagic;

	if (maxlen == 0)
		return 0;
#ifdef _WIN32
	if (end_ptr < str)	
#else
	if (__builtin_expect(end_ptr < str, 0))
#endif
		end_ptr = (const char *) ~0UL;

	/* Handle the first few characters by reading one character at a time.
	 Do this until CHAR_PTR is aligned on a longword boundary.  */
	for (char_ptr = str; ((unsigned long int) char_ptr & (sizeof(longword) - 1))
			!= 0; ++char_ptr)
		if (*char_ptr == '\0') {
			if (char_ptr > end_ptr)
				char_ptr = end_ptr;
			return char_ptr - str;
		}

	/* All these elucidatory comments refer to 4-byte longwords,
	 but the theory applies equally well to 8-byte longwords.  */

	longword_ptr = (unsigned long int *) char_ptr;

	/* Bits 31, 24, 16, and 8 of this number are zero.  Call these bits
	 the "holes."  Note that there is a hole just to the left of
	 each byte, with an extra at the end:

	 bits:  01111110 11111110 11111110 11111111
	 bytes: AAAAAAAA BBBBBBBB CCCCCCCC DDDDDDDD

	 The 1-bits make sure that carries propagate to the next 0-bit.
	 The 0-bits provide holes for carries to fall into.  */
	himagic = 0x80808080L;
	lomagic = 0x01010101L;
	if (sizeof(longword) > 4) {
		/* 64-bit version of the magic.  */
		/* Do the shift in two steps to avoid a warning if long has 32 bits.  */
		himagic = ((himagic << 16) << 16) | himagic;
		lomagic = ((lomagic << 16) << 16) | lomagic;
	}
	if (sizeof(longword) > 8)
		abort();

	/* Instead of the traditional loop which tests each character,
	 we will test a longword at a time.  The tricky part is testing
	 if *any of the four* bytes in the longword in question are zero.  */
	while (longword_ptr < (unsigned long int *) end_ptr) {
		/* We tentatively exit the loop if adding MAGIC_BITS to
		 LONGWORD fails to change any of the hole bits of LONGWORD.

		 1) Is this safe?  Will it catch all the zero bytes?
		 Suppose there is a byte with all zeros.  Any carry bits
		 propagating from its left will fall into the hole at its
		 least significant bit and stop.  Since there will be no
		 carry from its most significant bit, the LSB of the
		 byte to the left will be unchanged, and the zero will be
		 detected.

		 2) Is this worthwhile?  Will it ignore everything except
		 zero bytes?  Suppose every byte of LONGWORD has a bit set
		 somewhere.  There will be a carry into bit 8.  If bit 8
		 is set, this will carry into bit 16.  If bit 8 is clear,
		 one of bits 9-15 must be set, so there will be a carry
		 into bit 16.  Similarly, there will be a carry into bit
		 24.  If one of bits 24-30 is set, there will be a carry
		 into bit 31, so all of the hole bits will be changed.

		 The one misfire occurs when bits 24-30 are clear and bit
		 31 is set; in this case, the hole at bit 31 is not
		 changed.  If we had access to the processor carry flag,
		 we could close this loophole by putting the fourth hole
		 at bit 32!

		 So it ignores everything except 128's, when they're aligned
		 properly.  */

		longword = *longword_ptr++;

		if ((longword - lomagic) & himagic) {
			/* Which of the bytes was the zero?  If none of them were, it was
			 a misfire; continue the search.  */

			const char *cp = (const char *) (longword_ptr - 1);

			char_ptr = cp;
			if (cp[0] == 0)
				break;
			char_ptr = cp + 1;
			if (cp[1] == 0)
				break;
			char_ptr = cp + 2;
			if (cp[2] == 0)
				break;
			char_ptr = cp + 3;
			if (cp[3] == 0)
				break;
			if (sizeof(longword) > 4) {
				char_ptr = cp + 4;
				if (cp[4] == 0)
					break;
				char_ptr = cp + 5;
				if (cp[5] == 0)
					break;
				char_ptr = cp + 6;
				if (cp[6] == 0)
					break;
				char_ptr = cp + 7;
				if (cp[7] == 0)
					break;
			}
		}
		char_ptr = end_ptr;
	}

	if (char_ptr > end_ptr)
		char_ptr = end_ptr;
	return char_ptr - str;
}

String String::substr(const int iOff, const int iCount) const {
	if (iOff < 0 || iOff > m_pInfo->iStrSize || iCount < 0 || m_pInfo->iStrSize
			- iOff < iCount) {
		throw InvalidParameterException();
	}

	String s;
	if (iOff >= 0 && iOff < m_pInfo->iStrSize) {
		int iNewCount = iCount;
		if (iCount > m_pInfo->iStrSize - iOff || iCount < 0) {
			iNewCount = m_pInfo->iStrSize - iOff;
		}

		s.replace(0, 0, m_pInfo->szStr + iOff, iNewCount);
	}

	return s;
}

//从iPos开始，用整个str替换iCount指定的字符。
String& String::replace(int iPos, int iCount, const t_char* str, int iSize) {
	if (iPos < 0 || iCount < 0 || iSize < 0) {
		throw InvalidParameterException();
	}

	//初始化
	if (NULL == m_pInfo) {
		init(str, iSize);

		return *this;
	}

	if (iPos > m_pInfo->iStrSize) {
		throw InvalidParameterException();
	}

	//要重新计算iCount,因为如果iPos指定的位置在最后，那么iCount就应该为0了。
	if (iCount > m_pInfo->iStrSize - iPos) {
		iCount = m_pInfo->iStrSize - iPos;
	}

	if (!copyOnWrite()) {
		return *this;
	}

	int iAddLen = iSize - iCount;

	//如果没有内存存储新添加的，就重新开辟内存
	StrInfo* pNew = m_pInfo;
	if (iAddLen > m_pInfo->iMemSize - (int) sizeof(StrInfo) - m_pInfo->iStrSize) {
		pNew = (StrInfo*) ::realloc(m_pInfo, m_pInfo->iMemSize + iAddLen
				* sizeof(t_char));
		if (NULL == pNew) {
			throw OutOfMemoryException();
		}
	}

	if (iCount != iSize) {
		//把从iPos + iCount起的内存往后移iAddLen. memmove第一个参数是目的，第二个参数是源，最后是源的长
		::memmove(pNew->szStr + (iPos + iSize) * sizeof(t_char), pNew->szStr
				+ (iPos + iCount) * sizeof(t_char), (pNew->iStrSize - iPos
				- iCount + 1) * sizeof(t_char));//+1是为了把字符的结尾0往后移。

		m_pInfo = pNew;
	}

	m_pInfo->iMemSize += iAddLen;
	m_pInfo->iStrSize += iAddLen;

	//拷贝新的字符串
	memcpy(m_pInfo->szStr + iPos * sizeof(t_char), str, iSize * sizeof(t_char));

	return *this;
}

void String::init(const t_char* str, const int iStrLen) {
	int iMemSize = sizeof(StrInfo) + sizeof(t_char) * iStrLen;
	m_pInfo = (StrInfo*) ::malloc(iMemSize);
	if (NULL == m_pInfo) {
		throw OutOfMemoryException();
	}

	m_pInfo->iMemSize = iMemSize;
	m_pInfo->iStrSize = iStrLen;
	m_pInfo->szStr[iStrLen] = 0;
	m_pInfo->iRefCount = 1;

	::memcpy(m_pInfo->szStr, str, sizeof(t_char) * iStrLen);
}

bool String::copyOnWrite() {
	if (m_pInfo->iRefCount > 1) {
		StrInfo* pNew = (StrInfo*) ::malloc(m_pInfo->iMemSize);
		if (NULL == pNew) {
			throw OutOfMemoryException();
		}

		//拷贝
		memcpy(pNew, m_pInfo, m_pInfo->iMemSize);
		pNew->iRefCount = 1;

		//移动
		StrInfo* pOld = m_pInfo;
		m_pInfo = pNew;

		//移完再把原来的引用删除
		--pOld->iRefCount;
	}

	return true;
}

//采用SUNDAY模式匹配算法
int String::find(const t_char* des, const int len, const int iOff) const {
	if (NULL == des) {
		return npos;
	}

	const t_char* src = m_pInfo->szStr + iOff;

	int len_d = len;
	t_uchar next[1 << (8 * sizeof(t_char))] = { 0 };
	::memset(next, len_d + 1, 1 << (8 * sizeof(t_char)));

	int j = 0;
	for (j = 0; j < len_d; ++j)
		next[des[j]] = len_d - j; //建立反向索引数组

	register int pos = 0;
	while (pos < (m_pInfo->iStrSize - len_d - iOff + 1)) {//末端对齐
		register int i = pos;
		for (j = 0; j < len_d; ++j, ++i) {
			if (src[i] != des[j]) {
				pos += next[(t_uchar) (src[pos + len_d])];
				//不等于就跳跃,跳跃是核心
				break;
			}
		}

		if (j >= len_d)
			return pos + iOff;
	}

	return npos;
}

int String::rfind(const t_char* des, const int len, const int iOff) const {
	if (NULL == des) {
		return npos;
	}

	const t_char* src = m_pInfo->szStr;

	int len_d = len;
	t_char next[1 << (8 * sizeof(t_char))] = { 0 };
	::memset(next, len_d + 1, 1 << (8 * sizeof(t_char)));

	int j = 0;
	for (j = 0; j < len_d; ++j)
		next[des[len_d - j - 1]] = len_d - j; //建立反向索引数组

	register int pos = m_pInfo->iStrSize - 1;
	if (iOff > 0 && iOff < m_pInfo->iStrSize) {
		pos = iOff;
	}
	while (pos > 0) { //末端对齐
		register int i = pos;
		for (j = len_d - 1; j >= 0; --j, --i) {
			if (src[i] != des[j]) {
				pos -= next[src[pos - len_d]];
				//不等于就跳跃,跳跃是核心
				break;
			}
		}

		if (j < 0)
			return pos - len_d + 1;
	}

	return npos;
}

String& String::makeLower() {
	register t_char cMin = 'A';
	register t_char cA = 'a';
	register t_char cMax = 'Z';

	register t_char* sz = m_pInfo->szStr;
	for (int i = 0; i < m_pInfo->iStrSize; ++i) {
		if (sz[i] <= cMax && sz[i] >= cMin) {
			replace(i, 1, sz[i] + cA - cMin);
		}
	}

	return *this;
}

String& String::makeUpper() {
	register t_char cMin = 'a';
	register t_char cA = 'A';
	register t_char cMax = 'z';

	register t_char* sz = m_pInfo->szStr;
	for (int i = 0; i < m_pInfo->iStrSize; ++i) {
		if (sz[i] <= cMax && sz[i] >= cMin) {
			replace(i, 1, sz[i] - cMin + cA);
		}
	}

	return *this;
}

String& String::trim() {
	trimLeft();
	trimRight();
	return *this;
}

String& String::trimLeft() {
	int i = 0;
	for (i = 0; i < m_pInfo->iStrSize; ++i) {
		int j = 0;
		for (j = 0; j < sizeof(WhiteSpaceCharArr) / sizeof(t_char); ++j) {
			if (m_pInfo->szStr[i] == WhiteSpaceCharArr[j]) {
				break;
			}
		}

		if (j == sizeof(WhiteSpaceCharArr) / sizeof(t_char)) {
			break;
		}
	}

	if (0 != i) {
		replace(0, i, NULL, 0);
	}

	return *this;
}

String& String::trimRight() {
	int i;
	for (i = m_pInfo->iStrSize - 1; i >= 0; --i) {
		int j = 0;
		for (j = 0; j < sizeof(WhiteSpaceCharArr) / sizeof(t_char); ++j) {
			if (m_pInfo->szStr[i] == WhiteSpaceCharArr[j]) {
				break;
			}
		}

		if (j == sizeof(WhiteSpaceCharArr) / sizeof(t_char)) {
			break;
		}
	}

	if (i != m_pInfo->iStrSize - 1) {
		replace(i + 1, m_pInfo->iStrSize - i, NULL, 0);
	}

	return *this;
}

String& String::operator =(const String& str) {
	if (m_pInfo == str.m_pInfo) {
		return *this;
	}

	release();

	m_pInfo = str.m_pInfo;
	++m_pInfo->iRefCount;

	return *this;
}

bool String::operator ==(const String& str) const {
	int iLen = str.length();
	if (iLen == m_pInfo->iStrSize) {
		if (0 == memcmp(m_pInfo->szStr, str.m_pInfo->szStr, m_pInfo->iStrSize)) {
			return true;
		}
	}

	return false;
}

String& String::operator +=(const int i) {
	String str = Integer::toString(i, 10);
	return replace(m_pInfo->iStrSize, 0, str.m_pInfo->szStr,
			str.m_pInfo->iStrSize);
}

bool String::operator <(const String& str) const {
	return strcmp(m_pInfo->szStr, str.m_pInfo->szStr) < 0;
}

bool String::operator >(const String& str) const {
	return strcmp(m_pInfo->szStr, str.m_pInfo->szStr) > 0;
}

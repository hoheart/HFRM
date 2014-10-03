#ifndef __INTEGER_HPP__
#define __INTEGER_HPP__

#include "../hfc_def.hpp"

#ifndef _WIN32
#include <inttypes.h>
#endif

#include "String.hpp"
using namespace hfc::lang;

namespace hfc {
namespace lang {

class HFC_API Integer {

public:

	t_int m_iInt;

public:

	static const t_int MAX_VALUE;

	/**
	 * Constant for the minimum {@code int} value, -2<sup>31</sup>.
	 */
	static const t_int MIN_VALUE;

public:

	Integer(const int i);
	Integer(const Integer& i);

	virtual ~Integer();

public:

	static int parseInt(const char* str);

	static int parse(const String::t_char* string, const int radix);

	static String toString( const int iInt , const int iRadix );

	String toString(){
		return toString(m_iInt , 10 );
	}

public:
	/**
	 * ²Ù×÷·û
	 */

	operator const int() const {
		return m_iInt;
	}

	Integer& operator +=(const int i) {
		m_iInt += i;
		return *this;
	}
	Integer& operator +=(const Integer& i) {
		m_iInt += i.m_iInt;
		return *this;
	}

	bool operator ==(const int i) {
		return m_iInt == i;
	}
	bool operator ==(const Integer& i) {
		return m_iInt == i.m_iInt;
	}
	bool operator !=(const int i) {
		return m_iInt != i;
	}
	bool operator !=(const Integer& i) {
		return m_iInt != i.m_iInt;
	}

};

}
}
#endif

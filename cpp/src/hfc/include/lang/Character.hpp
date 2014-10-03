#ifndef __CHARACTER_HPP__
#define __CHARACTER_HPP__

#include "../hfc_def.hpp"

namespace hfc {
namespace lang {

class HFC_API Character {

protected:

	unsigned char m_ucChar;

public:

	static const int MIN_RADIX ;
	static const int MAX_RADIX ;

public:

	Character(const char z);
	Character(const Character& c);

	virtual ~Character();

public:

	static int digit(const char c, const int radix);

public:
	/**
	 * ²Ù×÷·û
	 */

	operator const char() const {
		return m_ucChar;
	}

	operator const unsigned char() const{
		return m_ucChar;
	}

	Character& operator +=(const char z) {
		m_ucChar += z;
		return *this;
	}
	Character& operator +=(const Character& c) {
		m_ucChar += c.m_ucChar;
		return *this;
	}
	Character& operator +=(const int number) {
		m_ucChar += number;
		return *this;
	}

	bool operator ==(const char z) {
		return m_ucChar == z;
	}
	bool operator ==(const Character& z) {
		return m_ucChar == z.m_ucChar;
	}
	bool operator !=(const char z) {
		return m_ucChar != z;
	}
	bool operator !=(const Character& z) {
		return m_ucChar != z.m_ucChar;
	}

};

}
}
#endif

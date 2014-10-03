#include "../../include/lang/Character.hpp"
using namespace hfc::lang;

#include "../../include/lang/InvalidParameterException.hpp"
#include "../../include/lang/NotDigitException.hpp"

const int Character::MIN_RADIX = 2;
const int Character::MAX_RADIX = 36;

Character::Character(const char z) {
	m_ucChar = z;
}

Character::Character(const Character& c) {
	m_ucChar = c.m_ucChar;
}

Character::~Character() {
}

int Character::digit(const char codePoint, const int radix) {
	if (radix < MIN_RADIX || radix > MAX_RADIX) {
		throw InvalidParameterException();
	}

	if (codePoint < 128) {
		// Optimized for ASCII
		int result = -1;
		if ('0' <= codePoint && codePoint <= '9') {
			result = codePoint - '0';
		} else if ('a' <= codePoint && codePoint <= 'z') {
			result = 10 + (codePoint - 'a');
		} else if ('A' <= codePoint && codePoint <= 'Z') {
			result = 10 + (codePoint - 'A');
		}

		if (result < radix) {
			return result;
		} else {
			throw NotDigitException();
		}
	} else {
		throw NotDigitException();
	}
}

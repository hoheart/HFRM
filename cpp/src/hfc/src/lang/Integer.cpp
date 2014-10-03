#include "../../include/lang/Integer.hpp"
using namespace hfc::lang;
	
#include "../../include/lang/Character.hpp"
#include "../../include/lang/InvalidParameterException.hpp"

const hfc::t_int Integer::MAX_VALUE = 0x7FFFFFFF;
const hfc::t_int Integer::MIN_VALUE = 0x80000000;

Integer::Integer(const int i) {
	m_iInt = i;
}

Integer::Integer(const Integer& i) {
	m_iInt = i.m_iInt;
}

Integer::~Integer() {
}

int Integer::parseInt(const char* str) {
	return parse(str, 10);
}

int Integer::parse(const String::t_char* string, const int radix) {
	if (NULL == string ) {
		throw InvalidParameterException();
	}

	int max = Integer::MIN_VALUE / radix;
	int result = 0, length = String::Strlen( string ) , offset = 0;

	while (offset < length) {
		int digit = Character::digit(string[offset++], radix);

		result = result * radix + digit;
	}

	return result;
}

String Integer::toString( const int iInt , const int iRadix ){
	int radix = iRadix;
	if (radix < Character::MIN_RADIX || radix > Character::MAX_RADIX) {
		radix = 10;
    }
	
	static const char DIGITS[] = {'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
		'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
		'u', 'v', 'w', 'x', 'y', 'z'
    };
	
	int i = iInt;
	if( i < 0 ){ 
		i = -i;
	}
	
	int bufLen = radix < 8 ? 33 : 12;  // Max chars in result (conservative)
	char* buf = new char[bufLen];
	int cursor = bufLen;
	
	do {
		int q = i / radix;
		buf[--cursor] = DIGITS[i - radix * q];
		i = q;
	} while (i != 0);
	
	if (iInt < 0) {
		buf[--cursor] = '-';
	}
	String s( buf + cursor , bufLen - cursor );
	delete buf;

	return s;
}

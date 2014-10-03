#include "../../include/encoder/HexString.hpp"
using namespace hfc::encoder;

#include <stdio.h>

String HexString::Encode(const String& str){
	char tmp[3] = { 0 };
	String ret;
	for (int i = 0; i < str.length(); i++) {
		unsigned char c = str.charAt(i);
		sprintf(tmp, "%02x", c);
		ret += tmp;
	}

	return ret;
}

String HexString::Decode( const String& str ){
	char tmp[1] = { 0 };
	String ret;
	String oneChar;
	for (int i = 0; i < str.length(); i+=2) {
		oneChar = str.substr( i , 2 );
		sscanf(oneChar, "%02x", tmp);
		ret.append( tmp , 1 );
	}

	return ret;
}
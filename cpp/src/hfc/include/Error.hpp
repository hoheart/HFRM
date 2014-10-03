#ifndef __ERROR_HPP__
#define __ERROR_HPP__

namespace hfc {

typedef enum Error {
	OK,
	NotDigit,
	InvalidParameter,
	OutOfMemory,
	RepeatedCall,
	OSError,
	InvalidFile,
	NotImplement,
	Timeout,
	ServiceStoped
} Error;
}

#endif

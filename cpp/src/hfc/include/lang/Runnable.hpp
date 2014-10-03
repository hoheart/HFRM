#ifndef __IRUNNABLE_HPP__
#define __IRUNNABLE_HPP__

#include "../hfc_def.hpp"

namespace hfc {
namespace lang {

class HFC_API IRunnable {

public:

	virtual ~IRunnable(){
	}

public:

	virtual void run( void* pParam = NULL ) = 0;
};

}
}

#endif
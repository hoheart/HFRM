#ifndef __RANDOM_HPP__
#define __RANDOM_HPP__

#include "../hfc_def.hpp"

namespace hfc {
namespace util{

/**
* 采用Mersenne Twister算法，比普通libc提供的快4倍，且更均匀。 参看http://www.bedaux.net/mtrand
*/
class HFC_API Random {
	
public:

	// default constructor: uses default seed only if this is the first instance
	Random() { if (!init) seed(5489UL); init = true; }
	// constructor with 32 bit int as seed
	Random(unsigned long s) { seed(s); init = true; }
	// the two seed functions
	void seed(unsigned long); // seed with 32 bit integer
	void seed(const unsigned long*, int size); // seed with array
	// overload operator() to make this a generator (functor)
	unsigned long next() { return rand_int32(); }
	unsigned long next( int iMin , int iMax ){
		int number = next() >> 1;
		return (iMin) + (long) ((double) ( (double) (iMax) - (iMin) + 1.0) * ((number) / (((long) (0x7FFFFFFF)) + 1.0)));
	}

	// 2007-02-11: made the destructor virtual; thanks "double more" for pointing this out
	virtual ~Random() {} // destructor

protected: // used by derived classes, otherwise not accessible; use the ()-operator
	unsigned long rand_int32(){ // generate 32 bit random integer
		if (p == n) gen_state(); // new state vector needed
		// gen_state() is split off to be non-inline, because it is only called once
		// in every 624 calls and otherwise irand() would become too big to get inlined
		unsigned long x = state[p++];
		x ^= (x >> 11);
		x ^= (x << 7) & 0x9D2C5680UL;
		x ^= (x << 15) & 0xEFC60000UL;
		return x ^ (x >> 18);
	}

private:

	static const int n; // compile time constants
	static const int m;
	// the variables below are static (no duplicates can exist)
	static unsigned long state[624]; //624是取值n，但vc6似乎不让直接写n  // state vector array
	static int p; // position in state array
	static bool init; // true if init function is called
	// private functions used to generate the pseudo random numbers
	unsigned long twiddle(unsigned long u, unsigned long v){ // used by gen_state()
		return (((u & 0x80000000UL) | (v & 0x7FFFFFFFUL)) >> 1) ^ ((v & 1UL) ? 0x9908B0DFUL : 0x0UL);
	}

	void gen_state(); // generate new state
	// make copy constructor and assignment operator unavailable, they don't make sense

};

}
}

#endif

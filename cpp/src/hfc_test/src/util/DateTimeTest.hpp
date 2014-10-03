#ifndef __DATE_TIME_TEST_HPP__
#define __DATE_TIME_TEST_HPP__

#include "../ITest.hpp"

#include "../../../include/util/DateTime.hpp"
using namespace hfc;
using namespace hfc::util;

#include <time.h>

class DateTimeTest : public ITest{
	
public:
	
	bool test(){
		if( testAll()
			){
			return true;
		}
		
		logError( "test class: DateTime error." );
		
		return false;
	}
	
	
	bool testAll()
	{
		DateTime dt;
		long tt = time(0);
		long tt1 = (long)dt.getTimestamp();
		if( time(0) + 8 * 60 * 60 != dt.getTimestamp() ){
			return false;//应该不会刚好过一秒吧？
		}
		int iMicrosecond = dt.get( DateTime::Microsecond );
		if( iMicrosecond < 0 || iMicrosecond > 999999 ){
			return false;
		}

		//从时间戳转时间
		dt.setTimestamp( 1356076923 );
		if( 2012 != dt.get(DateTime::Year) || 12 != dt.get(DateTime::Month) 
			|| 21 != dt.get(DateTime::Day) || 8 != dt.get(DateTime::Hour) 
			|| 2 != dt.get(DateTime::Minute) || 3 != dt.get(DateTime::Second) ){
			return false;
		}
		if( 5 != dt.get(DateTime::DayOfWeek) ){
			return false;
		}


		//从时间转时间戳
		dt.set( 2008 , 8 , 8 , 20 , 0 , 13 , 0 );
		if( 1218225613 != dt.getTimestamp() ){
			return false;
		}
		if( 5 != dt.get(DateTime::DayOfWeek) ){
			return false;
		}

		
	
		long t = 23434324;
		struct tm* pTm = localtime( &t );

		return true;
	}

};

#endif
#ifndef __DATE_TIME_HPP__
#define __DATE_TIME_HPP__

#include "../hfc_def.hpp"

namespace hfc {
namespace util {

class HFC_API DateTime {

public:

	typedef t_long Timestamp;
	typedef enum Field {
		Year, Month, Day, Hour, Minute, Second, Microsecond, DayOfWeek
	} Field;

public:

	/**
	 * 默认构造函数，返回的是格林威治标准时间
	 */
	DateTime();
	virtual ~DateTime();

public:

	static Timestamp MakeTimestamp(const int iYear, const char iMonth,
			const char iDay, const char iHour, const char iMin,
			const char iSecs);

	static bool IsLeap(const int y);

	static char MakeDayOfWeek(Timestamp t);
	static char MakeDayOfWeek(const int iYear, const char iMonth,
			const char iDay);

public:

	void makeTimestamp(const int iYear, const char iMonth,
			const char iDay, const char iHour, const char iMin,
			const char iSecs) {
		m_lTimestamp = MakeTimestamp(iYear, iMonth, iDay, iHour, iMin, iSecs);

		m_bTranslatedTimestamp = true;
	}

	Timestamp getTimestamp();
	Timestamp time() {
		return getTimestamp();
	}

	int get(const Field& f);

	void set(const int year, const char month, const char day, const char hour,
			const char minute, const char second, const int microseconds);
	void set(const int year, const char month, const char day, const char hour,
			const char minute, const char second, const int microseconds,
			const char dayOfWeek) {
		set(year, month, day, hour, minute, second, microseconds);
		set(DayOfWeek, dayOfWeek);
	}

	void set(const Field& f, const int val);

	void setTimestamp(const Timestamp& timestamp);

protected:

	void transalteFromTimestamp(const Timestamp& timestamp);

	/**
	 * 把指定时间戳的时分秒计算出来，其他字段不做处理。
	 */
	void translate2Hour(const Timestamp& timestamp);

	void translateDays(const Timestamp& days);

	void transalte2DayOfWeek();

protected:

	void construct();

protected:

	int m_iYear;
	char m_sMonth;
	char m_sDay;
	char m_sHour;
	char m_sMinute;
	char m_sSecond;
	int m_iMicrosecond;
	char m_sDayOfWeek;

	Timestamp m_lTimestamp;

	int m_iTimezone;

	bool m_bTranslatedTimestamp;
	bool m_bTranslatedDateTime;
	bool m_bTranslatedDayOfWeek;
};

}
}

#endif

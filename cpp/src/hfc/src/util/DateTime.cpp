#include "../../include/util/DateTime.hpp"
using namespace hfc::util;

#ifdef _WIN32
#include <windows.h>
#else
#include <sys/time.h>
#endif

#include "../../include/lang/InvalidParameterException.hpp"
using namespace hfc::lang;

DateTime::DateTime() {
	construct();

#ifdef _WIN32
	SYSTEMTIME tm = {0};
	::GetLocalTime(&tm);

	set(tm.wYear, (const char)tm.wMonth, (const char)tm.wDay, (const char)tm.wHour,
			(const char)tm.wMinute, (const char)tm.wSecond, tm.wMilliseconds * 1000,
			(const char)tm.wDayOfWeek);
#else
	struct timeval tp;
	//linux似乎从操作系统取到的就只能是时间戳
	::gettimeofday(&tp, NULL);//man gettimeofday上说：The use of the timezone structure is obsolete; the tz argument should normally be  specified  as  NULL.

	m_bTranslatedTimestamp = true;
	m_lTimestamp = tp.tv_sec;

	m_iMicrosecond = tp.tv_usec;
#endif
}

void DateTime::construct() {
	m_bTranslatedTimestamp = false;
	m_bTranslatedDateTime = false;
	m_bTranslatedDayOfWeek = false;
}

void DateTime::set(int year, char month, char day, char hour, char minute,
		char second, int microseconds) {
	set(Year, year);
	set(Month, month);
	set(Day, day);
	set(Hour, hour);
	set(Minute, minute);
	set(Second, second);
	set(Microsecond, microseconds);
}

DateTime::~DateTime() {
}

DateTime::Timestamp DateTime::getTimestamp() {
	if (!m_bTranslatedTimestamp) {
		m_lTimestamp = MakeTimestamp(m_iYear, m_sMonth, m_sDay, m_sHour,
				m_sMinute, m_sSecond);

		m_bTranslatedTimestamp = true;
	}

	return m_lTimestamp;
}

int DateTime::get(const Field& f) {
	if (!m_bTranslatedDateTime) {
		transalteFromTimestamp(m_lTimestamp);

		m_bTranslatedDateTime = true;
	}

	switch (f) {
	case Year:
	case Month:
	case Day:
	case Hour:
	case Minute:
	case Second:
	case Microsecond:
		if (!m_bTranslatedDateTime) {
			transalteFromTimestamp(m_lTimestamp);

			m_bTranslatedDateTime = true;
		}

		break;
	case DayOfWeek:
		if (!m_bTranslatedDayOfWeek) {
			transalte2DayOfWeek();

			m_bTranslatedDayOfWeek = true;
		}

		break;
	}

	int ret;
	switch (f) {
	case Year:
		ret = m_iYear;
		break;
	case Month:
		ret = m_sMonth;
		break;
	case Day:
		ret = m_sDay;
		break;
	case Hour:
		ret = m_sHour;
		break;
	case Minute:
		ret = m_sMinute;
		break;
	case Second:
		ret = m_sSecond;
		break;
	case Microsecond:
		ret = m_iMicrosecond;
		break;
	case DayOfWeek:
		ret = m_sDayOfWeek;
		break;
	}

	return ret;
}

char DateTime::MakeDayOfWeek(Timestamp t) {
	//1970年1月1日是星期四
	return (char) ((t / (24 * 60 * 60)) % 7 + 4);
}

char DateTime::MakeDayOfWeek(const int iYear, const char iMonth,
		const char iDay) {
	int year = iYear;
	int month = iMonth;
	int day = iDay;

	if (month == 1 || month == 2) {
		month += 12;
		--year;
	}

	int week = -1;
	week = (day + 2 * month + 3 * (month + 1) / 5 + year + year / 4 - year
			/ 100 + year / 400) % 7;

	return week; // 输出-1为错误
}

void DateTime::transalte2DayOfWeek() {
	if (m_bTranslatedTimestamp) {
		m_sDayOfWeek = MakeDayOfWeek(m_lTimestamp);
		m_bTranslatedDayOfWeek = true;
	} else if (m_bTranslatedDateTime) {
		m_sDayOfWeek = MakeDayOfWeek(m_iYear, m_sMonth, m_sDay);
		m_bTranslatedDayOfWeek = true;
	}
}

void DateTime::set(const Field& f, const int val) {
	m_bTranslatedTimestamp = false;
	m_bTranslatedDateTime = false;
	m_bTranslatedDayOfWeek = false;

	switch (f) {
	case Year:
		m_iYear = val;
		m_bTranslatedDateTime = true;
		break;
	case Month:
		if (val < 1 || val > 12) {
			throw InvalidParameterException();
		}
		m_sMonth = val;
		m_bTranslatedDateTime = true;
		break;
	case Day:
		if (val < 1 || val > 31) {
			throw InvalidParameterException();
		}
		m_sDay = val;
		m_bTranslatedDateTime = true;
		break;
	case Hour:
		if (val < 0 || val > 23) {
			throw InvalidParameterException();
		}
		m_sHour = val;
		m_bTranslatedDateTime = true;
		break;
	case Minute:
		if (val < 0 || val > 59) {
			throw InvalidParameterException();
		}
		m_sMinute = val;
		m_bTranslatedDateTime = true;
		break;
	case Second:
		if (val < 0 || val > 59) {
			throw InvalidParameterException();
		}
		m_sSecond = val;
		m_bTranslatedDateTime = true;
		break;
	case Microsecond:
		if (val < 0 || val > 999999) {
			throw InvalidParameterException();
		}
		m_iMicrosecond = val;
		m_bTranslatedDateTime = true;
		break;
	case DayOfWeek:
		if (val < 0 || val > 6) {
			throw InvalidParameterException();
		}
		m_sDayOfWeek = val;
		m_bTranslatedDayOfWeek = true;
		break;
	}
}

/**
 * 取得自1970以来的秒数,Gauss算法.
 * 算法解释请参见本文件目录下的guass.html
 */
DateTime::Timestamp DateTime::MakeTimestamp(const int iYear, const char iMonth,
		const char iDay, const char iHour, const char iMin, const char iSecs) {
	char iMon = iMonth;
	int iY = iYear;
	if (0 >= (iMon -= 2)) {
		iMon += 12;
		iY -= 1;
	}

	return ((((Timestamp) (iY / 4 - iY / 100 + iY / 400 + 367 * iMon / 12
			+ iDay) + iY * 365 - 719499) * 24 + iHour) * 60 + iMin) * 60
			+ iSecs;
}

bool DateTime::IsLeap(int y) {
	return y % 4 == 0 && (y % 100 != 0 || y % 400 == 0);
}

void DateTime::setTimestamp(const Timestamp& timestamp) {
	m_lTimestamp = timestamp;

	m_bTranslatedTimestamp = true;
	;
	m_bTranslatedDateTime = false;
	m_bTranslatedDayOfWeek = false;
}

void DateTime::translate2Hour(const Timestamp& timestamp) {
	Timestamp t = timestamp;
	if (t < 0) {
		//如果小于，把其换成大于来算：那么前一天就经过了86400减去倒着经过的秒数。
		t = 86400 + t % 86400;
	}

	m_sSecond = (char) (t % 60);
	m_sMinute = (char) (t / 60 % 60);
	m_sHour = (char) (t / 3600 % 24);
}

void DateTime::translateDays(const Timestamp& days) {
	Timestamp d = days;

	//只读,可以设置为static.
	static const int daysSinceYear[] = { 0, 31, 59, 90, 120, 151, 181, 212,
			243, 273, 304, 334, 365 };
	static const int daysSinceLeapYear[] = { 0, 31, 60, 91, 121, 152, 182, 213,
			244, 274, 305, 335, 366 };

	m_sDayOfWeek = (char) ((d + 4) % 7);

	//根据year/4 - year/100 + year/400 + 365*year 可以算出平均一年的天数为：(365*400 + 100 - 4 + 1)/400
	m_iYear = (int) (d * 400 / 146097 + 1969);

	d = d + 719162 - (m_iYear / 4 - m_iYear / 100 + m_iYear / 400 + m_iYear
			* 365);
	m_iYear = d >= 0 ? m_iYear + 1 : m_iYear;//算当年

	if (!IsLeap(m_iYear)) {
		d = d < 0 ? d + 365 : d;

		//把每个月都当成31天，那么最多少算一个月
		m_sMonth = (char) (d / 31);

		m_sDay = (char) (d - daysSinceYear[m_sMonth]);
		if (m_sDay >= daysSinceYear[m_sMonth + 1] - daysSinceYear[m_sMonth]) {
			m_sMonth += 1;
			m_sDay = (char) (d - daysSinceYear[m_sMonth]);
		}
	} else {
		d = d < 0 ? d + 366 : d;

		//把每个月都当成31天，那么最多少算一个月
		m_sMonth = (char) (d / 31);

		m_sDay = (char) (d - daysSinceLeapYear[m_sMonth]);
		if (m_sDay >= daysSinceLeapYear[m_sMonth + 1]
				- daysSinceLeapYear[m_sMonth]) {
			m_sMonth += 1;
			m_sDay = (char) (d - daysSinceLeapYear[m_sMonth]);
		}
	}

	if (12 == m_sMonth) {
		++m_iYear;
		m_sMonth = 0;
	}

	++m_sMonth;
	++m_sDay;
}

void DateTime::transalteFromTimestamp(const Timestamp& timestamp) {
	translate2Hour(timestamp);

	if (timestamp < 0 && (m_sSecond != 0 || m_sMinute != 0 || m_sHour != 0)) {
		translateDays(timestamp / 86400 - 1);
	} else {
		translateDays(timestamp / 86400);
	}
}

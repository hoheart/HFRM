#ifndef __HTTP_PROTOCAL_HPP__
#define __HTTP_PROTOCAL_HPP__

#include <hfc/hfc_def.hpp>

#include <hfc/lang/String.hpp>
using namespace hfc::lang;

#include <map>
using namespace std;



class HttpProtocal {

public:

	static const char* REQ_METHOD_GET;
	static const char* REQ_METHOD_POST;

public:

	HttpProtocal();
	virtual ~HttpProtocal();

public:

	class Request {

	public:

		String method;
		String resource;
		String protocalVersion;
		map<String, String> headerMap;

	public:

		String get(const char* key) {
			return headerMap.find(key)->second;
		}
	};

public:

	/**
	* ���������ͷ����
	* @return int ��������strContent�ַ�����ͷ��������ռ�ĳ��ȡ���������\r\n��֮���λ�á�
	*/
	static int ParseHeader(Request& req, String& strContent);
};
#endif

#include "../include/HttpProtocal.hpp"

const char* HttpProtocal::REQ_METHOD_GET = "GET";
const char* HttpProtocal::REQ_METHOD_POST = "POST";

HttpProtocal::HttpProtocal() {
}

HttpProtocal::~HttpProtocal() {
}

int HttpProtocal::ParseHeader(Request& req, String& strContent) {
	//解析http请求的方法
	int pos = strContent.find(' ');
	if( String::npos == pos ){
		return -1;
	}
	req.method = strContent.substr(0, pos);
	if (req.method != REQ_METHOD_GET && req.method != REQ_METHOD_POST) {
		req.method = "";
		//非法请求，不再解析
		return -1;
	}
	
	//解析请求的资源路径
	int offset = pos + 1;
	pos = strContent.find(' ', offset);
	req.resource = strContent.substr(offset, pos - offset);
	
	//解析请求的协议
	offset = pos + 1;
	pos = strContent.find("\r\n", offset);
	req.protocalVersion = strContent.substr(offset, pos - offset);
	
	//解析http header
	while (true) {
		offset = pos + 2;
		pos = strContent.find(':', offset);
		String key = strContent.substr(offset, pos - offset);
		
		offset = pos + 1;
		//处理冒号后的空格，可能有的浏览器没有发冒号后的空格////
		if (' ' == strContent.at(offset)) {
			++offset;
		}
		
		pos = strContent.find("\r\n", offset);
		String val = strContent.substr(offset, pos - offset);
		
		req.headerMap.insert(pair<String, String> (key, val));
		
		offset = pos + 2;
		//如果紧接着又是一个回车换行，表示头部解析完毕。
		if ('\r' == strContent.at(offset) && '\n' == strContent.at(offset + 1)) {
			offset += 2;
			break;
		}
	}
	
	return offset;
}


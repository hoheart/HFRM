#include "../include/HttpProtocal.hpp"

const char* HttpProtocal::REQ_METHOD_GET = "GET";
const char* HttpProtocal::REQ_METHOD_POST = "POST";

HttpProtocal::HttpProtocal() {
}

HttpProtocal::~HttpProtocal() {
}

int HttpProtocal::ParseHeader(Request& req, String& strContent) {
	//����http����ķ���
	int pos = strContent.find(' ');
	if( String::npos == pos ){
		return -1;
	}
	req.method = strContent.substr(0, pos);
	if (req.method != REQ_METHOD_GET && req.method != REQ_METHOD_POST) {
		req.method = "";
		//�Ƿ����󣬲��ٽ���
		return -1;
	}
	
	//�����������Դ·��
	int offset = pos + 1;
	pos = strContent.find(' ', offset);
	req.resource = strContent.substr(offset, pos - offset);
	
	//���������Э��
	offset = pos + 1;
	pos = strContent.find("\r\n", offset);
	req.protocalVersion = strContent.substr(offset, pos - offset);
	
	//����http header
	while (true) {
		offset = pos + 2;
		pos = strContent.find(':', offset);
		String key = strContent.substr(offset, pos - offset);
		
		offset = pos + 1;
		//����ð�ź�Ŀո񣬿����е������û�з�ð�ź�Ŀո�////
		if (' ' == strContent.at(offset)) {
			++offset;
		}
		
		pos = strContent.find("\r\n", offset);
		String val = strContent.substr(offset, pos - offset);
		
		req.headerMap.insert(pair<String, String> (key, val));
		
		offset = pos + 2;
		//�������������һ���س����У���ʾͷ��������ϡ�
		if ('\r' == strContent.at(offset) && '\n' == strContent.at(offset + 1)) {
			offset += 2;
			break;
		}
	}
	
	return offset;
}


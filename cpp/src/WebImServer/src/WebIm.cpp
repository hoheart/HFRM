#include "../include/WebIm.hpp"

#include <hfc/encoder/Base64.hpp>
#include <hfc/encoder/HexString.hpp>
using namespace hfc::encoder;

#include <hfc/crypto/Sha1.hpp>
#include <hfc/crypto/Md5.hpp>
using namespace hfc::crypto;

#include <hfc/lang/Integer.hpp>
using namespace hfc::lang;

#include <hfc/concurrent/AutoLocker.hpp>
using namespace hfc::concurrent;

#include <stdio.h>

const char* WebIm::YYKEY =
		"9hsJlPlbKdKeb64aa5Q1o3f1C2m84dW8n8I4C5t4qageLacet00f88o8TerbTbQe";

WebIm::WebIm() {
}

WebIm::~WebIm() {
}

void WebIm::onConnect(int fd) {
}

void WebIm::start() {
	m_oNetService.init("", 8069);
	m_oNetService.setReqContentProcessor(*this);
	m_oNetService.start();
}

void WebIm::stop() {
	for (UserIdMap::iterator i = m_oUserIdMap.begin(); i != m_oUserIdMap.end(); ++i) {
		int fd = i->first;
		close(fd);
	}
	m_oNetService.stop();
}

void WebIm::onRead(int fd, String& req) {
	if ("" == req) {
		delUser(fd);

		return;
	}

	HttpProtocal::Request r;
	if (processConnect(req, r, fd)) {
		return;
	}

	String data = unwrap(req);
	if (String::npos != data.find("\"to\"")) {
		//有to，表示注册连接
		ConnectReq req;
		parseConnectReq(data, req);

		String strU;
		strU += req.u;
		if (!checkMsg(strU, req.s, req.st)) {
			return;
		}

		if ('1' == req.to) {
			addUser(req, fd);
		} else {
			//似乎不用了
			//updateUserStatus( req );
		}
	} else {
		//发送消息
		SendMsgReq req;
		parseSendMsgReq(data, req);

		String strF;
		strF += req.f;
		String strId;
		strId += req.id;
		if (!checkMsg(strF, strId, req.vc)) {
			return;
		}

		forward(req);
	}

}

void WebIm::updateUserStatus(ConnectReq& req) {
	/*String json;
	 json += "{\"u\":";
	 json += req.u;
	 json += ",\"f\":\"";
	 json += req.to;
	 json += "\"}";

	 for(UserMap::iterator i = m_oUserMap.begin(); i != m_oUserMap.end(); ++ i ){
	 if( i->first == req.u ){
	 continue;
	 }

	 Connection c;
	 c.attach(i->second->socket);
	 int ret = c.send( json , json.size() );
	 c.detach();
	 }*/
}

void WebIm::forward(SendMsgReq& req) {
	String json;
	json += "{\"u\":";
	json += req.u;
	json += ",\"f\":";
	json += req.f;
	json += ",\"id\":";
	json += req.id;
	json += "}";

	UserMap::iterator i = m_oUserMap.find(req.f);
	if (m_oUserMap.end() == i) {
		return;
	}

	for (SocketList::iterator ii = i->second->begin(); ii != i->second->end(); ++ii) {
		Connection c;
		c.attach(ii->first);
		String de = wrap(json);
		int ret = c.send(de, de.size());
		int len = de.size();
		const char* ip = c.getIp();
		unsigned short port = c.getPort();
		if (ret == len) {
			printf(
					"Forward Msg Success. MessageId:%d, From:%d, To:%d, ToHost:%s, ToPort:%d.\n",
					req.id, req.u, req.f, ip, port);
		} else {
			printf(
					"Forward Msg Faild. MessageId:%d, From:%d, To:%d, ToHost:%s, ToPort:%d.\n",
					req.id, req.u, req.f, ip, port);
		}
		c.detach();
	}
}

void WebIm::addUser(ConnectReq& req, int fd) {
	AutoLocker l(m_oLocker);

	UserMap::iterator i = m_oUserMap.find(req.u);
	SocketList* pSocketList = NULL;
	if (m_oUserMap.end() == i) {
		pSocketList = new SocketList();
		m_oUserMap.insert(UserMap::value_type(req.u, pSocketList));
	} else {
		pSocketList = i->second;
	}

	pSocketList->insert(SocketList::value_type(fd, 0));

	m_oUserIdMap.insert(UserIdMap::value_type(fd, req.u));

	m_oLocker.unlock();

	Connection c;
	c.attach(fd);
	const char* ip = c.getIp();
	unsigned short port = c.getPort();

	printf("New Connection. User:%d, Host:%s, Port:%d.\n", req.u, ip, port);

	c.detach();
}

void WebIm::delUser(int fd) {
	AutoLocker l(m_oLocker);

	UserIdMap::iterator i = m_oUserIdMap.find(fd);
	if (m_oUserIdMap.end() == i) {
		return;
	}

	t_long userId = i->second;

	m_oUserIdMap.erase(i);

	UserMap::iterator ii = m_oUserMap.find(userId);
	if (m_oUserMap.end() == ii) {
		return;
	}

	SocketList::iterator iii = ii->second->find(fd);
	ii->second->erase(iii);
}

bool WebIm::checkMsg(String& str1, String& str2, String& checkCode) {
	String src = str1 + str2 + YYKEY;
	String md5US = Md5::Encrypt(src, src.size());

	String checkCodeBin = HexString::Decode(checkCode);

	return md5US.substr(3, 4) == checkCodeBin;
}

String WebIm::parseJson(String& data, String& key) {
	String jsonKey = "\"" + key + "\":";
	int pos = data.find(jsonKey);
	if (String::npos == pos) {
		return "";
	}

	bool isString = false;
	pos += jsonKey.size();
	if ('"' == data.at(pos)) {
		isString = true;
		++pos;
	}

	int pos1 = 0;
	if (isString) {
		while (true) {
			pos1 = data.find("\"", pos + 1);
			if ('\\' != data.at(pos1 - 1)) {
				break;
			}
		}

		pos1 = data.find(",", pos1);
	} else {
		pos1 = data.find(",", pos);
	}

	if (String::npos == pos1) {
		pos1 = data.find("}", pos);
	}

	if (isString) {
		//因为是字符串，前面有个引号
		--pos1;
	}

	return data.substr(pos, pos1 - pos);
}

void WebIm::parseConnectReq(String& data, ConnectReq& req) {
	String str = 'u';
	String u = parseJson(data, str);
	int iu = Integer::parseInt(u);
	req.u = iu;

	str = 's';
	req.s = parseJson(data, str);
	str = "st";
	req.st = parseJson(data, str);
	str = "to";
	req.to = parseJson(data, str);
}

void WebIm::parseSendMsgReq(String& data, SendMsgReq& req) {
	String str = 'u';
	String val = parseJson(data, str);
	int iVal = Integer::parseInt(val);
	req.u = iVal;

	str = 'f';
	val = parseJson(data, str);
	iVal = Integer::parseInt(val);
	req.f = iVal;

	str = "id";
	val = parseJson(data, str);
	iVal = Integer::parseInt(val);
	req.id = iVal;

	str = "vc";
	req.vc = parseJson(data, str);
}

bool WebIm::processConnect(String& data, HttpProtocal::Request& r, int fd) {
	String ack;
	if (-1 == HttpProtocal::ParseHeader(r, data)) {
		if (String::npos != data.find("policy-file-request")) {
			ack = getIESocketAck();
		}
	} else {
		if ('/' == r.resource) {
			ack = getConnectedAck(r);
		}
	}

	if (ack.size() > 0) {
		Connection c;
		c.attach(fd);
		int len = ack.length();
		c.send(ack.c_str(), len);
		//c.close();//根据协议，要关闭连接。
		c.detach();

		return true;
	}

	return false;
}

void WebIm::onWrite(int fd) {
}

void WebIm::onClose(int fd) {
	delUser(fd);
}

String WebIm::getConnectedAck(HttpProtocal::Request& header) {
	String origin = header.get("Origin");

	String host = header.get("Host");

	String resource = header.resource;

	String key = header.get("Sec-WebSocket-Key");
	key += "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
	key = Sha1::Encrypt(key, key.length());
	String base64Key = Base64::Encode(key);

	String upgrade = "HTTP/1.1 101 Switching Protocols\r\n"
		"Upgrade: websocket\r\n"
		"Connection: Upgrade\r\n"
		"WebSocket-Origin: ";
	upgrade += origin;
	upgrade += "\r\n"
		"WebSocket-Location: ws://";
	upgrade += host;
	upgrade += resource;
	upgrade += "\r\n"
		"Sec-WebSocket-Accept: ";
	upgrade += base64Key;
	upgrade += "\r\n\r\n";

	return upgrade;
}

String WebIm::getIESocketAck() {
	String policy = "<?xml version=\"1.0\"?>\n";
	policy
			+= "<!DOCTYPE cross-domain-policy SYSTEM \"http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd\">\n";
	policy += "<cross-domain-policy>\n";
	policy += "<allow-access-from domain=\"*\" to-ports=\"*\"/>\n";
	policy += "</cross-domain-policy>\n";

	return policy;
}

String WebIm::wrap(String& data) {
	String de = '\x81';
	int len = data.size();
	de += (char) len;

	de += data;

	return de;
}

String WebIm::unwrap(String& data) {
	if (0x81 != (unsigned char) (data.at(0))) {
		return "";
	}

	unsigned char mask[4] = { data.at(2), data.at(3), data.at(4), data.at(5) };

	String decodedData = data.substr(6);
	unsigned char* sz = (unsigned char*) (const char*) decodedData;
	int i = 0;
	for (i = 0; i < decodedData.size(); ++i) {
		sz[i] = sz[i] ^ mask[i % 4];
	}

	return decodedData;
}


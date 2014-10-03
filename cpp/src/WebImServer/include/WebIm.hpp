#ifndef __WEB_IM_HPP__
#define __WEB_IM_HPP__

#include <hfc/hfc_def.hpp>
using namespace hfc;



#include <map>
using namespace std;

#include <NetServiceFrame/NetService.hpp>
using namespace hfrm::NetServiceFrame;

#include "HttpProtocal.hpp"

#include <hfc/net/Connection.hpp>
using namespace hfc::net;

#include <hfc/concurrent/AutoLocker.hpp>
using namespace hfc::concurrent;

class WebIm: public IRequestProcessor {

public:

	static const char* YYKEY;

protected:

	class ConnectReq{

	public:

		int u;
		String s;
		String st;
		String to;
	};

	class SendMsgReq{

	public:

		//发送者
		int u;
		//接受者
		int f;
		//消息Id
		int id;
		//验证字符串
		String vc;
	};

protected:

	//用户多个socket连接的列表,为查找快速，用map。所以value字段不用，为节省存储空间，用char
	typedef map<int , char> SocketList;
	//以用户id为索引的用户表
	typedef map<t_long , SocketList*> UserMap;
	//以fd为索引的用户Id表
	typedef map<int , t_long> UserIdMap;

	UserMap m_oUserMap;
	UserIdMap m_oUserIdMap;

	Locker m_oLocker;
	
	NetService m_oNetService;

public:

	WebIm();
	virtual ~WebIm();
	
public:
	
	void start();

	void stop();

	void onConnect(int fd);
	
	void onRead(int fd, String& req);
	
	void onWrite(int fd);
	
	void onClose(int fd);	

	
protected:

	bool processConnect(String& data , HttpProtocal::Request& req , int fd);

	void parseConnectReq(String& data,ConnectReq& msgReq);

	void parseSendMsgReq( String& data , SendMsgReq& req );

	String parseJson(String& data , String& key);

	bool checkMsg(String& str1 , String& str2 , String& checkCode);

	void addUser(ConnectReq& req , int fd);

	void delUser(int fd);

	void updateUserStatus(ConnectReq& req);

	void forward(SendMsgReq& req);
	
	String getConnectedAck(HttpProtocal::Request& header);

	String getIESocketAck();

	String unwrap (String& data);

	String wrap(String& data);
};

#endif

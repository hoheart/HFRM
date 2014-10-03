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

		//������
		int u;
		//������
		int f;
		//��ϢId
		int id;
		//��֤�ַ���
		String vc;
	};

protected:

	//�û����socket���ӵ��б�,Ϊ���ҿ��٣���map������value�ֶβ��ã�Ϊ��ʡ�洢�ռ䣬��char
	typedef map<int , char> SocketList;
	//���û�idΪ�������û���
	typedef map<t_long , SocketList*> UserMap;
	//��fdΪ�������û�Id��
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

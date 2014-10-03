#ifndef __HFC_DEF_H__
#define __HFC_DEF_H__

	#ifndef NULL
		#define NULL 0
	#endif

	//windows�����е�һЩ����
	#ifdef _WIN32
		//4786��4251��Stl����ʱ���Ĵ�4275�Ǵӽӿڼ̳�ʱ�����������dll������ᱨ��
		#pragma warning(disable: 4786 4251 4275)

		#ifdef HFC_EXPORTS
			#define HFC_API _declspec(dllexport)
		#else
			#define HFC_API _declspec(dllimport)
		#endif
	#else
		#define HFC_API
	#endif

namespace hfc{

	#ifdef _WIN32
		typedef __int32 t_int;
		typedef __int64 t_long;
		//�ļ�������
		typedef void* t_fd;
	#else
		typedef int t_int;
		typedef long long t_long;
		//�ļ�������
		typedef int t_fd;
	#endif
}

#endif

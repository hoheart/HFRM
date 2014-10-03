#ifndef __HFC_DEF_H__
#define __HFC_DEF_H__

	#ifndef NULL
		#define NULL 0
	#endif

	//windows下特有的一些定义
	#ifdef _WIN32
		//4786和4251是Stl编译时报的错。4275是从接口继承时，如果子类是dll导出类会报错。
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
		//文件描述符
		typedef void* t_fd;
	#else
		typedef int t_int;
		typedef long long t_long;
		//文件描述符
		typedef int t_fd;
	#endif
}

#endif

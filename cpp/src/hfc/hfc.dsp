# Microsoft Developer Studio Project File - Name="hfc" - Package Owner=<4>
# Microsoft Developer Studio Generated Build File, Format Version 6.00
# ** DO NOT EDIT **

# TARGTYPE "Win32 (x86) Dynamic-Link Library" 0x0102

CFG=hfc - Win32 Debug
!MESSAGE This is not a valid makefile. To build this project using NMAKE,
!MESSAGE use the Export Makefile command and run
!MESSAGE 
!MESSAGE NMAKE /f "hfc.mak".
!MESSAGE 
!MESSAGE You can specify a configuration when running NMAKE
!MESSAGE by defining the macro CFG on the command line. For example:
!MESSAGE 
!MESSAGE NMAKE /f "hfc.mak" CFG="hfc - Win32 Debug"
!MESSAGE 
!MESSAGE Possible choices for configuration are:
!MESSAGE 
!MESSAGE "hfc - Win32 Release" (based on "Win32 (x86) Dynamic-Link Library")
!MESSAGE "hfc - Win32 Debug" (based on "Win32 (x86) Dynamic-Link Library")
!MESSAGE 

# Begin Project
# PROP AllowPerConfigDependencies 0
# PROP Scc_ProjName ""
# PROP Scc_LocalPath ""
CPP=cl.exe
MTL=midl.exe
RSC=rc.exe

!IF  "$(CFG)" == "hfc - Win32 Release"

# PROP BASE Use_MFC 0
# PROP BASE Use_Debug_Libraries 0
# PROP BASE Output_Dir "Release"
# PROP BASE Intermediate_Dir "Release"
# PROP BASE Target_Dir ""
# PROP Use_MFC 0
# PROP Use_Debug_Libraries 0
# PROP Output_Dir "Release"
# PROP Intermediate_Dir "Release"
# PROP Target_Dir ""
# ADD BASE CPP /nologo /MT /W3 /GX /O2 /D "WIN32" /D "NDEBUG" /D "_WINDOWS" /D "_MBCS" /D "_USRDLL" /D "HFC_EXPORTS" /YX /FD /c
# ADD CPP /nologo /MT /W3 /GX /O2 /D "WIN32" /D "NDEBUG" /D "_WINDOWS" /D "_MBCS" /D "_USRDLL" /D "HFC_EXPORTS" /YX /FD /c
# ADD BASE MTL /nologo /D "NDEBUG" /mktyplib203 /win32
# ADD MTL /nologo /D "NDEBUG" /mktyplib203 /win32
# ADD BASE RSC /l 0x804 /d "NDEBUG"
# ADD RSC /l 0x804 /d "NDEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /dll /machine:I386
# ADD LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /dll /machine:I386

!ELSEIF  "$(CFG)" == "hfc - Win32 Debug"

# PROP BASE Use_MFC 0
# PROP BASE Use_Debug_Libraries 1
# PROP BASE Output_Dir "hfc___Win32_Debug"
# PROP BASE Intermediate_Dir "hfc___Win32_Debug"
# PROP BASE Target_Dir ""
# PROP Use_MFC 0
# PROP Use_Debug_Libraries 1
# PROP Output_Dir "Debug"
# PROP Intermediate_Dir "Debug"
# PROP Ignore_Export_Lib 0
# PROP Target_Dir ""
# ADD BASE CPP /nologo /MTd /W3 /Gm /GX /ZI /Od /D "WIN32" /D "_DEBUG" /D "_WINDOWS" /D "_MBCS" /D "_USRDLL" /D "HFC_EXPORTS" /YX /FD /GZ /c
# ADD CPP /nologo /MTd /W3 /Gm /GX /ZI /Od /I "third_part\include" /D "WIN32" /D "_DEBUG" /D "_WINDOWS" /D "_MBCS" /D "_USRDLL" /D "HFC_EXPORTS" /FR /YX /FD /GZ /c
# ADD BASE MTL /nologo /D "_DEBUG" /mktyplib203 /win32
# ADD MTL /nologo /D "_DEBUG" /mktyplib203 /win32
# ADD BASE RSC /l 0x804 /d "_DEBUG"
# ADD RSC /l 0x804 /d "_DEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /dll /debug /machine:I386 /pdbtype:sept
# ADD LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib libeay32.lib Ws2_32.lib /nologo /dll /debug /machine:I386 /out:"bin_win32/hfc.dll" /implib:"lib_win32/hfc.lib" /pdbtype:sept /libpath:"third_part\lib_win32"
# SUBTRACT LINK32 /pdb:none

!ENDIF 

# Begin Target

# Name "hfc - Win32 Release"
# Name "hfc - Win32 Debug"
# Begin Group "concurrent"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=include\concurrent\AutoLocker.hpp
# End Source File
# Begin Source File

SOURCE=src\concurrent\Condition.cpp
# End Source File
# Begin Source File

SOURCE=include\concurrent\Condition.hpp
# End Source File
# Begin Source File

SOURCE=src\concurrent\Locker.cpp
# End Source File
# Begin Source File

SOURCE=include\concurrent\Locker.hpp
# End Source File
# Begin Source File

SOURCE=.\src\crypto\Sha1.cpp
# End Source File
# Begin Source File

SOURCE=.\include\crypto\Sha1.hpp
# End Source File
# Begin Source File

SOURCE=src\concurrent\ThreadPool.cpp
# End Source File
# Begin Source File

SOURCE=include\concurrent\ThreadPool.hpp
# End Source File
# Begin Source File

SOURCE=include\concurrent\TimeoutException.hpp
# End Source File
# End Group
# Begin Group "crypto"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=src\crypto\Md5.cpp
# End Source File
# Begin Source File

SOURCE=include\crypto\Md5.hpp
# End Source File
# Begin Source File

SOURCE=src\crypto\Rsa.cpp
# End Source File
# Begin Source File

SOURCE=include\crypto\Rsa.hpp
# End Source File
# End Group
# Begin Group "encoder"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=.\src\encoder\Base64.cpp
# End Source File
# Begin Source File

SOURCE=.\include\encoder\Base64.hpp
# End Source File
# Begin Source File

SOURCE=.\src\encoder\HexString.cpp
# End Source File
# Begin Source File

SOURCE=.\include\encoder\HexString.hpp
# End Source File
# End Group
# Begin Group "io"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=src\io\AbstractFile.cpp
# End Source File
# Begin Source File

SOURCE=include\io\AbstractFile.hpp
# End Source File
# Begin Source File

SOURCE=src\io\Directory.cpp
# End Source File
# Begin Source File

SOURCE=include\io\Directory.hpp
# End Source File
# Begin Source File

SOURCE=src\io\File.cpp
# End Source File
# Begin Source File

SOURCE=include\io\File.hpp
# End Source File
# Begin Source File

SOURCE=include\io\InvalidFileException.hpp
# End Source File
# End Group
# Begin Group "lang"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=src\lang\Character.cpp
# End Source File
# Begin Source File

SOURCE=include\lang\Character.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\Exception.hpp
# End Source File
# Begin Source File

SOURCE=src\lang\Integer.cpp
# End Source File
# Begin Source File

SOURCE=include\lang\Integer.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\InvalidParameterException.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\NotDigitException.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\NotImplementException.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\RepeatedCallException.hpp
# End Source File
# Begin Source File

SOURCE=include\lang\Runnable.hpp
# End Source File
# Begin Source File

SOURCE=src\lang\String.cpp
# End Source File
# Begin Source File

SOURCE=include\lang\String.hpp
# End Source File
# End Group
# Begin Group "net"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=src\net\Connection.cpp
# End Source File
# Begin Source File

SOURCE=include\net\Connection.hpp
# End Source File
# Begin Source File

SOURCE=src\net\Socket.cpp
# End Source File
# Begin Source File

SOURCE=include\net\Socket.hpp
# End Source File
# Begin Source File

SOURCE=src\net\TCPServer.cpp
# End Source File
# Begin Source File

SOURCE=include\net\TCPServer.hpp
# End Source File
# Begin Source File

SOURCE=src\net\UDPSocket.cpp
# End Source File
# Begin Source File

SOURCE=include\net\UDPSocket.hpp
# End Source File
# End Group
# Begin Group "util"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=src\util\DateTime.cpp
# End Source File
# Begin Source File

SOURCE=include\util\DateTime.hpp
# End Source File
# Begin Source File

SOURCE=include\util\OSErrorException.hpp
# End Source File
# Begin Source File

SOURCE=include\util\OutOfMemoryException.hpp
# End Source File
# Begin Source File

SOURCE=src\util\Random.cpp
# End Source File
# Begin Source File

SOURCE=include\util\Random.hpp
# End Source File
# Begin Source File

SOURCE=include\util\ServiceStopedException.hpp
# End Source File
# End Group
# Begin Group "asynchronous"

# PROP Default_Filter "cpp,hpp"
# Begin Source File

SOURCE=.\src\asynchronous\AsynchronousInputServer.cpp
# End Source File
# Begin Source File

SOURCE=.\src\asynchronous\AsynchronousOutputServer.cpp
# End Source File
# Begin Source File

SOURCE=.\src\asynchronous\AsynchronousServer.cpp
# End Source File
# Begin Source File

SOURCE=.\include\asynchronous\AsynchronousInputServer.hpp
# End Source File
# Begin Source File

SOURCE=.\include\asynchronous\AsynchronousOutputServer.hpp
# End Source File
# Begin Source File

SOURCE=.\include\asynchronous\AsynchronousServer.hpp
# End Source File
# Begin Source File

SOURCE=.\include\asynchronous\Processor.hpp
# End Source File
# End Group
# Begin Source File

SOURCE=include\Error.hpp
# End Source File
# Begin Source File

SOURCE=include\hfc_def.hpp
# End Source File
# End Target
# End Project

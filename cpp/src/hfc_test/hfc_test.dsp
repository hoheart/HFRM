# Microsoft Developer Studio Project File - Name="hfc_test" - Package Owner=<4>
# Microsoft Developer Studio Generated Build File, Format Version 6.00
# ** DO NOT EDIT **

# TARGTYPE "Win32 (x86) Console Application" 0x0103

CFG=hfc_test - Win32 Debug
!MESSAGE This is not a valid makefile. To build this project using NMAKE,
!MESSAGE use the Export Makefile command and run
!MESSAGE 
!MESSAGE NMAKE /f "hfc_test.mak".
!MESSAGE 
!MESSAGE You can specify a configuration when running NMAKE
!MESSAGE by defining the macro CFG on the command line. For example:
!MESSAGE 
!MESSAGE NMAKE /f "hfc_test.mak" CFG="hfc_test - Win32 Debug"
!MESSAGE 
!MESSAGE Possible choices for configuration are:
!MESSAGE 
!MESSAGE "hfc_test - Win32 Release" (based on "Win32 (x86) Console Application")
!MESSAGE "hfc_test - Win32 Debug" (based on "Win32 (x86) Console Application")
!MESSAGE 

# Begin Project
# PROP AllowPerConfigDependencies 0
# PROP Scc_ProjName ""
# PROP Scc_LocalPath ""
CPP=cl.exe
RSC=rc.exe

!IF  "$(CFG)" == "hfc_test - Win32 Release"

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
# ADD BASE CPP /nologo /W3 /GX /O2 /D "WIN32" /D "NDEBUG" /D "_CONSOLE" /D "_MBCS" /YX /FD /c
# ADD CPP /nologo /W3 /GX /O2 /D "WIN32" /D "NDEBUG" /D "_CONSOLE" /D "_MBCS" /YX /FD /c
# ADD BASE RSC /l 0x804 /d "NDEBUG"
# ADD RSC /l 0x804 /d "NDEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /subsystem:console /machine:I386
# ADD LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /subsystem:console /machine:I386

!ELSEIF  "$(CFG)" == "hfc_test - Win32 Debug"

# PROP BASE Use_MFC 0
# PROP BASE Use_Debug_Libraries 1
# PROP BASE Output_Dir "Debug"
# PROP BASE Intermediate_Dir "Debug"
# PROP BASE Target_Dir ""
# PROP Use_MFC 0
# PROP Use_Debug_Libraries 1
# PROP Output_Dir "Debug"
# PROP Intermediate_Dir "Debug"
# PROP Target_Dir ""
# ADD BASE CPP /nologo /W3 /Gm /GX /ZI /Od /D "WIN32" /D "_DEBUG" /D "_CONSOLE" /D "_MBCS" /YX /FD /GZ /c
# ADD CPP /nologo /W3 /Gm /GX /ZI /Od /I "..\third_part\include" /D "WIN32" /D "_DEBUG" /D "_CONSOLE" /D "_MBCS" /FR /YX /FD /GZ /c
# ADD BASE RSC /l 0x804 /d "_DEBUG"
# ADD RSC /l 0x804 /d "_DEBUG"
BSC32=bscmake.exe
# ADD BASE BSC32 /nologo
# ADD BSC32 /nologo
LINK32=link.exe
# ADD BASE LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib /nologo /subsystem:console /debug /machine:I386 /pdbtype:sept
# ADD LINK32 kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib kernel32.lib user32.lib gdi32.lib winspool.lib comdlg32.lib advapi32.lib shell32.lib ole32.lib oleaut32.lib uuid.lib odbc32.lib odbccp32.lib hfc.lib /nologo /subsystem:console /debug /machine:I386 /out:"../bin_win32/hfc_test.exe" /pdbtype:sept /libpath:"../lib_win32"
# SUBTRACT LINK32 /pdb:none

!ENDIF 

# Begin Target

# Name "hfc_test - Win32 Release"
# Name "hfc_test - Win32 Debug"
# Begin Group "lang"

# PROP Default_Filter "cpp;hpp"
# Begin Source File

SOURCE=src\lang\CharacterTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\lang\IntegerTest.hpp
# End Source File
# Begin Source File

SOURCE=src\lang\LangTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\lang\StringTest.hpp
# End Source File
# End Group
# Begin Group "net"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=.\src\net\NetTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\net\TCPTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\net\UDPTest.hpp
# End Source File
# End Group
# Begin Group "crypto"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=.\src\crypto\CryptoTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\crypto\Md5EncryptionTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\crypto\RsaEncryptionTest.hpp
# End Source File
# End Group
# Begin Group "util"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=.\src\util\DateTimeTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\util\RandomTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\util\UtilTest.hpp
# End Source File
# End Group
# Begin Group "io"

# PROP Default_Filter "hpp;cpp"
# Begin Source File

SOURCE=.\src\io\AbstractFileTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\io\FileTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\io\IOTest.hpp
# End Source File
# End Group
# Begin Group "concurrent"

# PROP Default_Filter ""
# Begin Source File

SOURCE=.\src\concurrent\ConcurrentTest.hpp
# End Source File
# Begin Source File

SOURCE=.\src\concurrent\ThreadPoolTest.hpp
# End Source File
# End Group
# Begin Source File

SOURCE=src\ITest.hpp
# End Source File
# Begin Source File

SOURCE=src\main.cpp
# End Source File
# End Target
# End Project

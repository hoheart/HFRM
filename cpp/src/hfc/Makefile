BINPATH = $(YY_ROOT)/lib/
SONAME = libhfc.so.1
TARGET =  $(BINPATH)$(SONAME)

INCDIR = third_part/include

CC = g++
#-fPIC生成位置无关代码
CFLAGS = -c -fPIC -ggdb3 -D_LINUX -I$(INCDIR)

%.o : src/concurrent/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<
	
%.o : src/crypto/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<
	
%.o : src/encoder/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<
	
%.o : src/io/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<

%.o : src/lang/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<

%.o : src/net/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<
	
%.o : src/util/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<

LDPATH			= -L/home/hoheart/yyup/lib

LFLAGS1 = -shared -Wl,-soname,$(SONAME)
LFLAGS2 = $(LDPATH) -lcrypto -lpthread

concurrent = Condition Locker ThreadPool
crypto = Md5 Rsa Sha1
encoder = Base64 HexString
io = AbstractFile Directory File
lang = Character Integer String
net = Connection Socket TCPServer UDPSocket
util = DateTime Random
all = $(concurrent) $(crypto) $(encoder) $(io) $(lang) $(net) $(util)
OBJS = $(foreach n,$(all),$n.o)

outObjs = $(foreach n,$(OBJS),Debug/$n)

$(TARGET) : $(OBJS)
	$(CC) $(LFLAGS1) -o $(TARGET) $(outObjs) $(LFLAGS2)

#$(SRCPREV)/%.cpp : $(SRCPREV)/%.hpp
	
clean:
	rm -f Debug/*.o





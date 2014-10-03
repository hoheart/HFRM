BINPATH = /home/hoheart/yyup/lib/
SONAME = libnet_service_frame.so.1
TARGET =  $(BINPATH)$(SONAME)

INCDIR = ../include/

CC = g++
#-fPIC生成位置无关代码
CFLAGS = -c -fPIC -ggdb3 -D_LINUX -I$(INCDIR)

%.o : src/%.cpp
	$(CC) $(CFLAGS) -o Debug/$@ $<

LDPATH			= -L$(LD_LIBRARY_PATH)

LFLAGS1 = -shared -Wl,-soname,$(SONAME)
LFLAGS2 = $(LDPATH) -lhfc

frame = NetService EPoll FrameProcessor  
all = $(frame)
OBJS = $(foreach n,$(all),$n.o)

outObjs = $(foreach n,$(OBJS),Debug/$n)

$(TARGET) : $(OBJS)
	$(CC) $(LFLAGS1) -o $(TARGET) $(outObjs) $(LFLAGS2)

#$(SRCPREV)/%.cpp : $(SRCPREV)/%.hpp
	
clean:
	rm -f Debug/*.o





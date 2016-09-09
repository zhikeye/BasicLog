# socket

## 说明

### 服务器端编程的步骤

1.加载套接字库，创建套接字(WSAStartup()/socket())；
2.绑定套接字到一个IP地址和一个端口上(bind())；
3.将套接字设置为监听模式等待连接请求(listen())；
4.请求到来后，接受连接请求，返回一个新的对应于此次连接的套接字(accept())；
5.用返回的套接字和客户端进行通信(send()/recv())；
6.返回，等待另一连接请求；
7.关闭套接字，关闭加载的套接字库(closesocket()/WSACleanup())。

### 客户端编程的步骤

1.加载套接字库，创建套接字(WSAStartup()/socket())；
2.向服务器发出连接请求(connect())；
3.和服务器端进行通信(send()/recv())；
4.关闭套接字，关闭加载的套接字库(closesocket()/WSACleanup())。


## 服务端

```
#include <iostream>
#include <WINSOCK2.H>
#include <string>
#include <stdio.h>
using namespace std;

void log(const string &msg)
{
	cout << msg << endl;
}

/**
 第一式: 加载/释放Winsock库
 第二式: 构造SOCKET
 第三式:配置监听地址和端口:
 第四式:   绑定SOCKET:
 第五式: 服务端/客户端连接:
 第六式: 收/发数据:
 第七式: 关闭SOCKET:
 */

int main()
{
	WSADATA wsa;

	/*初始化socket资源*/
	if (WSAStartup(MAKEWORD(1,1),&wsa) != 0)
	{
		log("init socket failure");
		return 1;
	}

	/*构造SOCKET*/
	SOCKET Listen_Sock = socket(AF_INET,SOCK_STREAM,0);

	/*配置监听地址和端口*/
	SOCKADDR_IN serverAddr;
	serverAddr.sin_family = AF_INET;
	serverAddr.sin_port = htons(6666);
	serverAddr.sin_addr.S_un.S_addr = htonl(INADDR_ANY);

	/*绑定SOCKET*/
	int retVal = bind(Listen_Sock,(sockaddr*) &serverAddr,sizeof(SOCKADDR_IN) );
	if (retVal == SOCKET_ERROR)
	{
		log("bind socket failure");
		return 1;
	}

	listen(Listen_Sock,10);

	SOCKADDR_IN clientAddr;
	int len = sizeof(SOCKADDR_IN);

	while(true)
	{
		/*服务端/客户端连接*/
		SOCKET sockConn = accept(Listen_Sock,(sockaddr*) &clientAddr,&len);
		/*收/发数据*/
		char sendBuf[1024];
		sprintf(sendBuf,"Weclome %s to here!",inet_ntoa(clientAddr.sin_addr));
		send(sockConn,sendBuf,strlen(sendBuf) + 1,0);
		char retBuf[1024];
		recv(sockConn,retBuf,1024,0);
		cout << retBuf << endl;
		/*关闭SOCKET*/
		closesocket(sockConn);
	}




		

	/*释放资源*/
	closesocket(Listen_Sock);
	WSACleanup();

}
```


## 客户端

```
#include <WINSOCK2.H>
#include <stdio.h>
#include <iostream>
#include <string>
#include <windows.h>
using namespace std;

void log(const string &msg)
{
	cout << msg << endl;
}
int main()
{
	WSADATA was;
	if (WSAStartup(MAKEWORD(1,1),&was) != 0)
	{
		log("init socket failure");
		return 1;
	}

	SOCKET Client_Sock = socket(AF_INET, SOCK_STREAM, 0);

	SOCKADDR_IN clientsock_in;
	clientsock_in.sin_addr.S_un.S_addr=inet_addr("127.0.0.1");
	clientsock_in.sin_family=AF_INET;
	clientsock_in.sin_port=htons(6666);

	connect(Client_Sock,(SOCKADDR*)&clientsock_in,sizeof(SOCKADDR_IN));
	int cur = 10;
	while(cur-- > 0)
	{
		char receiveBuf[1024];
		recv(Client_Sock,receiveBuf,1025,0);
		printf("SERVER:%s\n",receiveBuf);
		send(Client_Sock,"hello,this is client",strlen("hello,this is client")+1,0);
		Sleep(3000);
	}
	closesocket(Client_Sock);
	WSACleanup();

	return 0;
}
```


## 函数说明

### socket()函数(创建Socket)

> int socket(int domain, int type, int protocol);

 socket函数对应于普通文件的打开操作。普通文件的打开操作返回一个文件描述字，而socket()用于创建一个socket描述符（socket descriptor），它唯一标识一个socket。这个socket描述字跟文件描述字一样，后续的操作都有用到它，把它作为参数，通过它来进行一些读写操作。正如可以给fopen的传入不同参数值，以打开不同的文件。创建socket的时候，也可以指定不同的参数创建不同的socket描述符，socket函数的三个参数分别为：

 - domain：即协议域，又称为协议族（family）。常用的协议族有，AF_INET、AF_INET6、AF_LOCAL（或称AF_UNIX，Unix域socket）、AF_ROUTE等等。协议族决定了socket的地址类型，在通信中必须采用对应的地址，如AF_INET决定了要用ipv4地址（32位的）与端口号（16位的）的组合、AF_UNIX决定了要用一个绝对路径名作为地址。
 - type：指定socket类型。常用的socket类型有，SOCK_STREAM、SOCK_DGRAM、SOCK_RAW、SOCK_PACKET、SOCK_SEQPACKET等等（socket的类型有哪些？）。
 - protocol：故名思意，就是指定协议。常用的协议有，IPPROTO_TCP、IPPTOTO_UDP、IPPROTO_SCTP、IPPROTO_TIPC等，它们分别对应TCP传输协议、UDP传输协议、STCP传输协议、TIPC传输协议（这个协议我将会单独开篇讨论！）。

> 注意：并不是上面的type和protocol可以随意组合的，如SOCK_STREAM不可以跟IPPROTO_UDP组合。当protocol为0时，会自动选择type类型对应的默认协议。

> 当我们调用socket创建一个socket时，返回的socket描述字它存在于协议族（address family，AF_XXX）空间中，但没有一个具体的地址。如果想要给它赋值一个地址，就必须调用bind()函数，否则就当调用connect()、listen()时系统会自动随机分配一个端口。

### bind()函数

> int bind(int sockfd, const struct sockaddr *addr, socklen_t addrlen);

函数的三个参数分别为：
- sockfd：即socket描述字，它是通过socket()函数创建了，唯一标识一个socket。bind()函数就是将给这个描述字绑定一个名字。
- addr：一个const struct sockaddr *指针，指向要绑定给sockfd的协议地址。这个地址结构根据地址创建socket时的地址协议族的不同而不同，如ipv4对应的是：
```
struct sockaddr_in {
    sa_family_t    sin_family; /* address family: AF_INET */
    in_port_t      sin_port;   /* port in network byte order */
    struct in_addr sin_addr;   /* internet address */
};
/* Internet address. */
struct in_addr {
    uint32_t       s_addr;     /* address in network byte order */
};
```
ipv6对应的是： 
```
struct sockaddr_in6 { 
    sa_family_t     sin6_family;   /* AF_INET6 */ 
    in_port_t       sin6_port;     /* port number */ 
    uint32_t        sin6_flowinfo; /* IPv6 flow information */ 
    struct in6_addr sin6_addr;     /* IPv6 address */ 
    uint32_t        sin6_scope_id; /* Scope ID (new in 2.4) */ 
};
struct in6_addr { 
    unsigned char   s6_addr[16];   /* IPv6 address */ 
};
```

- addrlen：对应的是地址的长度。

*通常服务器在启动的时候都会绑定一个众所周知的地址（如ip地址+端口号），用于提供服务，客户就可以通过它来接连服务器；而客户端就不用指定，有系统自动分配一个端口号和自身的ip地址组合。这就是为什么通常服务器端在listen之前会调用bind()，而客户端就不会调用，而是在connect()时由系统随机生成一个。*

### listen()、connect()函数

如果作为一个服务器，在调用socket()、bind()之后就会调用listen()来监听这个socket，如果客户端这时调用connect()发出连接请求，服务器端就会接收到这个请求。

> int listen(int sockfd, int backlog);

> int connect(int sockfd, const struct sockaddr *addr, socklen_t addrlen);

listen函数的第一个参数即为要监听的socket描述字，第二个参数为相应socket可以排队的最大连接个数。socket()函数创建的socket默认是一个主动类型的，listen函数将socket变为被动类型的，等待客户的连接请求。

connect函数的第一个参数即为客户端的socket描述字，第二参数为服务器的socket地址，第三个参数为socket地址的长度。客户端通过调用connect函数来建立与TCP服务器的连接。

### accept()函数

TCP服务器端依次调用socket()、bind()、listen()之后，就会监听指定的socket地址了。TCP客户端依次调用socket()、connect()之后就想TCP服务器发送了一个连接请求。TCP服务器监听到这个请求之后，就会调用accept()函数取接收请求，这样连接就建立好了。之后就可以开始网络I/O操作了，即类同于普通文件的读写I/O操作。

>  int accept(int sockfd, struct sockaddr *addr, socklen_t *addrlen);

accept函数的第一个参数为服务器的socket描述字，第二个参数为指向struct sockaddr *的指针，用于返回客户端的协议地址，第三个参数为协议地址的长度。如果accpet成功，那么其返回值是由内核自动生成的一个全新的描述字，代表与返回客户的TCP连接。

**注意：accept的第一个参数为服务器的socket描述字，是服务器开始调用socket()函数生成的，称为监听socket描述字；而accept函数返回的是已连接的socket描述字。一个服务器通常通常仅仅只创建一个监听socket描述字，它在该服务器的生命周期内一直存在。内核为每个由服务器进程接受的客户连接创建了一个已连接socket描述字，当服务器完成了对某个客户的服务，相应的已连接socket描述字就被关闭。**

### read()、write()等函数

- read()/write()
- recv()/send()
- readv()/writev()
- recvmsg()/sendmsg()
- recvfrom()/sendto()

# Build Docker image for Zenoss 5 Network Weathermap

FROM centos:centos6
MAINTAINER "Jan Garaj" <jan.garaj@gmail.com> (www.jangaraj.com)

RUN yum -y install httpd wget && \
TODO
wget http://network-weathermap.com/files/php-weathermap-0.97c.zip  && \
unzip  && \ 

TODO
COPY php-weathermap-zenoss5-plugin/lib/ /etc/httpd/conf.d/
COPY php-weathermap-zenoss5-plugin/example/ /etc/httpd/conf.d/ 
COPY network-weathermap.conf /etc/httpd/conf.d/

CMD ["/usr/sbin/apachectl", "-D", "FOREGROUND"]

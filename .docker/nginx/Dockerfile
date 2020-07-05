FROM nginx:alpine

ARG HOST_USERID

RUN rm /etc/nginx/conf.d/default.conf
COPY conf/nginx.conf /etc/nginx/nginx.conf
COPY conf.d/app.conf /etc/nginx/conf.d/app.conf
COPY conf.d/inc/cors.conf /etc/nginx/conf.d/inc/cors.conf

RUN if [ ! -z "$HOST_USERID" ] ; then set -x ; \
    delgroup www-data; \
    deluser www-data; \
    addgroup -g $HOST_USERID -S www-data ; \
    adduser -u $HOST_USERID -D -S -G www-data www-data; \
    fi

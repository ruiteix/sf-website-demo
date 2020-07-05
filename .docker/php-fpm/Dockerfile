FROM registry.gitlab.com/le-dev-francais/tools/docker/php:7-fpm

LABEL multi.maintainer1="Rui TEIXEIRA<ruiteix@yahoo.fr>"

# Install Symfony
RUN mkdir -p /usr/local/bin \
    && curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony \
    && chmod a+x /usr/local/bin/symfony

# Install oh-my-zsh
RUN git clone git://github.com/robbyrussell/oh-my-zsh.git /usr/local/etc/oh-my-zsh

COPY conf/zshrc /root/.zshrc
COPY conf/zshrc /home/www/.zshrc

RUN mkdir -p /usr/local/etc/oh-my-zsh/themes \
    && curl https://raw.githubusercontent.com/dpdornseifer/docker-zsh-theme/master/docker.zsh-theme > /usr/local/etc/oh-my-zsh/themes/docker.zsh-theme

RUN sed -i "s|/root:/bin/ash|/root:/bin/zsh|g" /etc/passwd
RUN sed -i "s|/home/www-data:/bin/false|/home/www-data:/bin/zsh|g" /etc/passwd

# Configs
COPY conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ARG HOST_USERID
ARG HOST_GROUPID

RUN if [ ! -z "$HOST_USERID" ] && [ ! -z "$HOST_GROUPID" ] ; then set -x ; \
    ln -sfn /dev/null /var/log/lastlog; \
    usermod -u ${HOST_USERID} -d /home/www www-data; \
    groupmod -g ${HOST_GROUPID} www-data; \
    chown -R www-data: /home/www; \
    fi

# DEBIAN
FROM ubuntu:18.04

RUN apt update \
    && apt full-upgrade --quiet --yes \
    && apt install --quiet --yes wget libx11-6 libjogl2-java openssh-server \
    && cd /tmp \
    # UPDATE SCILAB VERSION
    && wget https://www.scilab.org/download/6.0.2/scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && tar -xzf scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mv scilab-6.0.2/ /opt/scilab \
    && chown -R root:root /opt/scilab \
    && rm scilab-6.0.2.bin.linux-x86_64.tar.gz \
    && mkdir -p /var/run/sshd \
    && mkdir -p /opt/bp-app \
    && mkdir -p /root/.ssh \
    && sed -ri 's/^#?PermitRootLogin\s+.*/PermitRootLogin yes/' /etc/ssh/sshd_config \
    && sed -ri 's/^#?PasswordAuthentication\s+.*/PasswordAuthentication no/' /etc/ssh/sshd_config \
    && sed -ri 's/^#?AcceptEnv\s+.*/AcceptEnv SCRIPT/' /etc/ssh/sshd_config \
    && sed -ri 's/^#?X11Forwarding\s+.*/X11Forwarding no/' /etc/ssh/sshd_config \
    && sed -ri 's/^#?Subsystem\s+.*/#Subsystem/' /etc/ssh/sshd_config

COPY docker/run-script.sh /opt/bp-app/
COPY docker/test-script.sh /opt/bp-app/
COPY docker/1622619815_1619954846_tcn.zcos /opt/bp-app/
COPY docker/authorized_keys /root/.ssh/

RUN chown -R root:root /root/ \
    && chmod +x /opt/bp-app/run-script.sh \
    && chmod +x /opt/bp-app/test-script.sh \
    && chmod -R 600 /root/.ssh \
    && chmod 700 /root/.ssh


CMD /usr/sbin/sshd -D

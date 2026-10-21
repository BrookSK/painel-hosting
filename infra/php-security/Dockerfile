# syntax=docker/dockerfile:1
ARG BASE_IMAGE
ARG RUNTIME_IMAGE
FROM ${BASE_IMAGE} AS builder
USER root
RUN apt-get update && apt-get install -y --no-install-recommends $PHPIZE_DEPS python3 apache2-dev libargon2-dev libcurl4-openssl-dev libonig-dev libreadline-dev libsodium-dev libsqlite3-dev libssl-dev libxml2-dev zlib1g-dev
COPY php-8.3.36.tar.xz /tmp/php.tar.xz
RUN echo 'dd54693aa334c5c4d72e9aee96b082c2473750a18a6c1d105789c93a3b1692fd  /tmp/php.tar.xz' | sha256sum -c -
RUN --mount=type=tmpfs,target=/usr/src/php-security \
    tar -xf /tmp/php.tar.xz --strip-components=1 -C /usr/src/php-security && \
    cd /usr/src/php-security && php-config --configure-options > /tmp/configure-options && \
    python3 -c 'import shlex,subprocess; subprocess.run(["./configure"]+shlex.split(open("/tmp/configure-options").read()),check=True)' && \
    make -j24 && sapi/cli/php -n -v && mkdir -p /patched && \
    cp sapi/cli/php .libs/libphp.so modules/opcache.so modules/sodium.so /patched/ && \
    make install-headers INSTALL_ROOT=/patched/root && make install-build INSTALL_ROOT=/patched/root && \
    cp scripts/php-config scripts/phpize /patched/
FROM ${RUNTIME_IMAGE}
COPY --from=builder /patched/php /usr/local/bin/php
COPY --from=builder /patched/libphp.so /usr/lib/apache2/modules/libphp.so
COPY --from=builder /patched/opcache.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/opcache.so
COPY --from=builder /patched/sodium.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/sodium.so
COPY --from=builder /patched/root/usr/local/include/php /usr/local/include/php
COPY --from=builder /patched/root/usr/local/lib/php/build /usr/local/lib/php/build
COPY --from=builder /patched/php-config /usr/local/bin/php-config
COPY --from=builder /patched/phpize /usr/local/bin/phpize
COPY php-8.3.36.tar.xz /usr/src/php.tar.xz
ENV PHP_VERSION=8.3.36 PHP_SHA256=dd54693aa334c5c4d72e9aee96b082c2473750a18a6c1d105789c93a3b1692fd PHP_URL=https://www.php.net/distributions/php-8.3.36.tar.xz PHP_ASC_URL=https://www.php.net/distributions/php-8.3.36.tar.xz.asc
RUN php -v && php -m && apache2ctl -t
LABEL local.php.security.patch="CVE-2026-109618,CVE-2026-109619" local.php.version="8.3.36"

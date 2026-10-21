# PHP — correção de segurança de 21/10/2026

Referência: [anúncio oficial do PHP](https://www.php.net/archive/2026.php#2026-10-21-5).
As versões corrigidas são 8.2.35, 8.3.36, 8.4.27 e 8.5.12 para
CVE-2026-109618 e CVE-2026-109619. Versões antigas exigem backports verificados.

## Provisionamento

`AppInstallService` verifica o PHP real das imagens dos templates `wordpress`,
`php-laravel` e `roundcube` antes de remover o container anterior. Falha de pull,
imagem sem PHP ou versão abaixo do mínimo abortam a instalação. Uma imagem local
com correção pode ser usada quando o pull não está disponível. Os demais templates
continuam usando o fluxo de pull existente, também antes da remoção do container.

O passo phpMyAdmin verifica a imagem candidata antes da criação. Para um container
já existente, verifica seu PHP e informa falha quando não consegue comprovar a
correção. Esse passo não substitui nem migra containers existentes.

Overrides opcionais na tabela `settings`:

| Chave | Uso |
|---|---|
| `infra.php_image.wordpress` | Imagem WordPress corrigida |
| `infra.php_image.php-laravel` | Imagem PHP/Apache corrigida |
| `infra.php_image.roundcube` | Imagem Roundcube corrigida |
| `infra.phpmyadmin_image` | Imagem phpMyAdmin corrigida |

Sem override, a imagem atual do template/phpMyAdmin é usada e passa pela mesma
verificação. Uma tag `latest` não basta para aprovar. A política aceita as branches
8.2–8.5 a partir dos mínimos acima; branches futuras e backports de fornecedor
precisam de revisão explícita da política após verificar o código/pacote.

Esses overrides são globais: use uma imagem disponível em todos os nodes atendidos
ou distribua-a para um registry antes de configurar. Não copie tags locais de um
node para a configuração global sem garantir essa disponibilidade.

## Receita das imagens PHP 8.3.36 da Eveo

`infra/php-security/Dockerfile` preserva os arquivos da aplicação e recompila PHP
CLI, módulo Apache, OPcache e sodium com as opções da imagem builder. O arquivo
fonte oficial tem SHA-256 verificado tanto pelo script quanto pelo Dockerfile.
O script exige Docker com BuildKit, curl e sha256sum.

Esta receita destina-se aos bases Debian/Apache PHP 8.3 usados na Eveo (ABI
`no-debug-non-zts-20230831`). Não é uma receita genérica para Alpine, FPM ou outras
branches. Use os mesmos bases preservados ou digests equivalentes e verifique as
extensões e o Apache na imagem final antes de substituir qualquer serviço.

Exemplos no node Eveo, onde os bases preservados já existem:

```bash
bash infra/php-security/build.sh \
  eveo-preserved/app_wordpress_5:before-20261021 \
  eveo-preserved/app_wordpress_5:before-20261021 \
  eveo-php-security/app_wordpress_5:8.3.36-20261021

bash infra/php-security/build.sh \
  eveo-preserved/app_wordpress_5:before-20261021 \
  eveo-preserved/lrv_phpmyadmin:before-20261021 \
  eveo-php-security/lrv_phpmyadmin:8.3.36-20261021
```

O build não inicia serviços nem altera volumes/configurações. A operação da Eveo
também preservou `/etc/phpmyadmin` em mount separado: o deploy deve manter esse
mount e os volumes de dados existentes. Segredos e arquivos `docker inspect` não
devem entrar no Git. A compilação já foi validada na operação da Eveo; esta mudança
versiona a receita e acrescenta o checksum dentro do build.

## Hospedagem compartilhada

Atualizar este repositório não modifica o PHP do Plesk. O provedor precisa aplicar
os pacotes/backports corrigidos. `composer.json` descreve compatibilidade da
aplicação; aumentar seu requisito não instala nem corrige o interpretador.

## Validação

`php tests/php-image-security.php` cobre versões abaixo/no limite das branches,
falhas de pull/probe e preservação da aplicação quando a imagem candidata falha.
O teste usa um Docker simulado e não cria containers nem acessa produção.

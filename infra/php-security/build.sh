#!/usr/bin/env bash
set -euo pipefail

if [[ $# != 3 ]]; then
  echo "Uso: $0 IMAGEM_BUILDER IMAGEM_APLICACAO TAG_DESTINO" >&2
  exit 2
fi
builder_image=$1
runtime_image=$2
output_image=$3
recipe_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
build_context=$(mktemp -d)
trap 'rm -rf -- "$build_context"' EXIT

curl --fail --location --retry 3 \
  https://www.php.net/distributions/php-8.3.36.tar.xz \
  --output "$build_context/php-8.3.36.tar.xz"
printf '%s  %s\n' \
  dd54693aa334c5c4d72e9aee96b082c2473750a18a6c1d105789c93a3b1692fd \
  "$build_context/php-8.3.36.tar.xz" | sha256sum --check -
cp "$recipe_dir/Dockerfile" "$build_context/Dockerfile"
DOCKER_BUILDKIT=1 docker build \
  --build-arg "BASE_IMAGE=$builder_image" \
  --build-arg "RUNTIME_IMAGE=$runtime_image" \
  --tag "$output_image" "$build_context"

docker run --rm --network none --entrypoint php "$output_image" -n -r \
  'if (PHP_VERSION !== "8.3.36") { fwrite(STDERR, "Versão PHP inesperada\n"); exit(1); } echo PHP_VERSION, "\n";'

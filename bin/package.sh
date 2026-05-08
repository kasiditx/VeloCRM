#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PACKAGE_DIR="$ROOT_DIR/output/package"
ZIP_PATH="$ROOT_DIR/output/velocrm-codecanyon.zip"

cd "$ROOT_DIR"

npm run build
php artisan optimize:clear

rm -rf "$PACKAGE_DIR" "$ZIP_PATH"
mkdir -p "$PACKAGE_DIR"

rsync -a ./ "$PACKAGE_DIR/" \
    --exclude '.git/' \
    --exclude '.env' \
    --include '.env.example' \
    --exclude '.env.*' \
    --exclude 'node_modules/' \
    --exclude 'vendor/' \
    --exclude 'storage/logs/' \
    --exclude 'storage/framework/cache/' \
    --exclude 'storage/framework/sessions/' \
    --exclude 'storage/framework/testing/' \
    --exclude 'storage/framework/views/' \
    --exclude 'public/uploads/' \
    --exclude 'public/storage' \
    --exclude 'audit_screenshots/' \
    --exclude 'output/' \
    --exclude 'progress.txt' \
    --exclude '.DS_Store'

cd "$PACKAGE_DIR"
zip -qr "$ZIP_PATH" .

echo "Package created: $ZIP_PATH"

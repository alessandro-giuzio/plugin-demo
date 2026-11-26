#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"

# ag-employee-profiles (WordPress plugin)
(cd ag-employee-profiles && find . -name '*.php' -not -path './vendor/*' -not -path './node_modules/*' -print0 | xargs -0 -n1 php -l > /dev/null)
(cd ag-employee-profiles && npm run build)

# expiration-manager (WordPress plugin)
(cd expiration-manager && find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l > /dev/null)

echo "GATE: PASS"

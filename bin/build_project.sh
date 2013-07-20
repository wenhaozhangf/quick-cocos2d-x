#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TEMPLATE_ROOT=`dirname "$DIR"`/template
php "$DIR/lib/build_project.php" -t "$TEMPLATE_ROOT/BUILD_TEMPLATE_01" $*

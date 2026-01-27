#!/bin/sh
set -eu

find . -maxdepth 1 -type d -name 'bundle_zid_ui_*' -exec rm -rf -- {} +

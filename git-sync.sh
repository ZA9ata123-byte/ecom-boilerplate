#!/usr/bin/env bash
set -e
MSG="${1:-chore: update}"
STAMP=$(date +"%Y%m%d-%H%M")
git add .
git commit -m "$MSG" || echo "no changes to commit"
git fetch origin
git rebase origin/NEW-UPDATE1 || git rebase --continue
git tag -f "backup-$STAMP" -m "auto-backup $STAMP"
git push --follow-tags

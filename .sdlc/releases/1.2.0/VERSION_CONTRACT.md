# Version Contract: 1.2.0

**Date:** 2026-10-23

## Canonical Source

The plugin header `Version:` and `LOAD_HTML_FILES_PLUGIN_VERSION` in load-html-files/load-html-files.php, and
`Stable tag:` in load-html-files/readme.txt. All are written and read back by scripts/release/bump-version.sh, and CI
fails when they disagree.

## Expected State After Deploy

- master has every version location at `1.2.0`, and `= 1.2.0 =` at the top of changelog.txt.
- Tag `v1.2.0` is on master's HEAD. release.yml builds the zip, creates the GitHub release and deploys to wp.org SVN (`Type: free`).
- wordpress.org/plugins/load-html-files shows 1.2.0 after the wp.org release confirmation.
- The zip bundles alanef/free_plugin_lib 1.3.3.

## Required Deploy Command

```
/tk-deploy live
```

## Post-Deploy Verification

See .claude/commands/deploy-live.md: SVN tag check, wp.org release confirmation, KB changelog where configured, YouTrack.

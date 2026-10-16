<!-- Managed by wordpress-plugin-boilerplate/tooling (deploy-live.free.md) - fix there first, then run bin/sync-tooling.sh -->
# Deploy live: Load HTML Files

Run by `/tk-deploy live`. Releases the version already set in the plugin header through
the single-tag pipeline: one tag here runs `release.yml`, which re-runs the checks, creates
the GitHub release with `load-html-files-$V.zip` attached and deploys to what `Type: free`
in `load-html-files/readme.txt` asks for.

Stop at the first failure and report it. Never retry a tag push, never force
anything, never delete a tag to "try again". Use absolute paths throughout:
`CDPATH` is set on Alan's machines and sends a relative `cd` somewhere else.

```bash
P=$(git rev-parse --show-toplevel)
GIT_TERMINAL_PROMPT=0 git -C $P fetch origin --tags
V=$(grep -m1 -oP '^[ \t]*\*?[ \t]*Version:[ \t]*\K\S+' $P/load-html-files/load-html-files.php)
TAG=v$V          # this repo's tag style (pin "tag_prefix" in .tooling.json)
PREV=$(git -C $P tag -l | grep -E '^v?[0-9]+(\.[0-9]+)+$' | awk '{t=$0; sub(/^v/,"",t); print t" "$0}' | sort -V | tail -1 | cut -d' ' -f2)
KEY=$(grep -oP '^YOUTRACK_PROJECT=["'\'']?\K[A-Za-z0-9_]+' $P/.env.youtrack 2>/dev/null)
KEY=${KEY:-}   # empty: YouTrack step is skipped with a note
KB=""             # KB changelog page: the changelog_url pin, else the readme's link
KB=${KB:-$(grep -m1 -oE 'https://fullworksplugins\.com/docs/[^ )"<>]*change-log[^ )"<>]*' $P/load-html-files/readme.txt)}
```

Check `git -C $P remote get-url origin` names `alanef/load-html-files` before going on.

## Preconditions

All must hold, otherwise stop:

- On `master` and level with `origin/master`
  (`git -C $P rev-parse HEAD` = `git -C $P rev-parse origin/master`).
- No tracked changes: `git -C $P status --porcelain --untracked-files=no` is empty.
- Version agreement everywhere, no prerelease suffix, changelog heading in place:
  `$P/scripts/release/bump-version.sh --check $V` exits 0.
- Tag absent locally and on origin, in either style (release.yml fires on both):
  `git -C $P tag -l "$V" "v$V"` and
  `GIT_TERMINAL_PROMPT=0 git -C $P ls-remote --tags origin "refs/tags/$V" "refs/tags/v$V"` are empty.
- `$P/.sdlc/releases/$V/RELEASE_NOTES.md` exists (from `/tk-release $V`).
- `$P/.sdlc/releases/$V/TESTS_PASSED.md` exists, and its commit
  `C=$(grep -oP '^\*\*Commit:\*\* \K[0-9a-f]+' $P/.sdlc/releases/$V/TESTS_PASSED.md)`
  is an ancestor of HEAD (`git -C $P merge-base --is-ancestor $C HEAD`) with no plugin
  code changed since
  (`git -C $P diff --quiet $C HEAD -- load-html-files/ ':!load-html-files/changelog.txt'`).
  Otherwise re-run `$P/scripts/release/run-tests-and-mark.sh $V`. If the marker shows
  PHPUnit `SKIPPED`, say so to the user when asking for confirmation.
- `$P/.sdlc/releases/$V/DEPENDENCY_AUDIT.md` exists (from `scripts/release/check-dependencies.sh`).
- **KB changelog page.** If `$KB` is empty the plugin has no KB changelog page; note
  "no KB changelog page" and go on. If `$KB` is set, `$P/.env.wordpress` must exist and set
  both `WP_POST_ID` and `CHANGELOG_FILE`:
  `grep -qE '^(export )?WP_POST_ID=.+' $P/.env.wordpress && grep -qE '^(export )?CHANGELOG_FILE=.+' $P/.env.wordpress`.
  If not, **stop** and tell the user, on its own line:
  `CHANGELOG PAGE WILL NOT BE UPDATED: no .env.wordpress (page: $KB)` (or name the missing
  key). The file is gitignored and is lost when a repo is re-cloned or moved; a release
  without it ships with a stale public changelog. Continue only on an explicit yes that
  accepts this, and remember it for the final report.
- The latest `checks.yml` run on `master` is for HEAD and succeeded:
  `gh run list -R alanef/load-html-files --workflow checks.yml --branch master --limit 1 --json headSha,status,conclusion`
  shows `headSha` = HEAD, `completed`, `success`.

Then show the user: `$PREV..HEAD` (`git -C $P log --oneline $PREV..HEAD`; no tags yet
means `$PREV` is empty, so show `git -C $P log --oneline -20` and say it is a first release), the issue ids
in range, the `= $V =` section of `load-html-files/changelog.txt`, and exactly what the next
step will publish. **Ask for explicit confirmation before tagging.** Anything other than a
clear yes means stop.

## Tag and push

```bash
git -C $P tag $TAG && GIT_TERMINAL_PROMPT=0 git -C $P push origin $TAG
```

If the push fails, stop and report: the tag then exists only locally. Do not delete it,
retry or force; tell the user.

## Watch release.yml

```bash
gh run list -R alanef/load-html-files --workflow release.yml --limit 5 --json databaseId,headBranch,status
gh run watch <id> -R alanef/load-html-files --exit-status     # the run whose headBranch is $TAG
```

Run the watch in the background and wait for completion. Every job must succeed. On
failure show `gh run view <id> -R alanef/load-html-files --log-failed` and stop.

## Confirm what landed

```bash
gh release view $TAG -R alanef/load-html-files --json assets -q '.assets[].name'   # must list load-html-files-$V.zip
```

WordPress.org SVN must have the tag:

```bash
curl -s https://plugins.svn.wordpress.org/load-html-files/tags/ | grep -c ">$V/<"   # must be 1
```

The wordpress.org API
(`https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=load-html-files`)
will NOT show `$V` yet if release confirmation is on: the release waits for a committer to
confirm it. That is expected; see the manual steps.

## Publish the changelog

`update-changelog.sh` is on PATH from dev_scripts. It reads `.env.wordpress` in the
project root (gitignored: site, credentials, `WP_POST_ID`, `CHANGELOG_FILE`) and publishes
`load-html-files/changelog.txt` to the KB page `$KB`.

- `$KB` empty: no KB changelog page. Note "no KB changelog page" and go on; nothing to warn.
- `$KB` set and `.env.wordpress` complete (as checked in the preconditions):

  ```bash
  (cd $P && update-changelog.sh); echo "exit $?"
  curl -s "$KB" | grep -c "Version $V"     # must be at least 1
  ```

- `$KB` set but the user accepted, at the preconditions, releasing without `.env.wordpress`:
  do not run it.

If the publish was skipped, exited non-zero, or the page does not show `Version $V`, the
final report MUST carry a separate, prominent line, never folded into a summary:

```
CHANGELOG PAGE NOT UPDATED: <reason: no .env.wordpress / missing WP_POST_ID / update-changelog.sh exit N / page does not show Version $V>, page $KB
```

The release has already shipped, so carry on to YouTrack; do not re-tag or re-run the release.

## YouTrack

If `$KEY` is empty, skip this step and say so: no `.env.youtrack` `YOUTRACK_PROJECT` and no
`youtrack_project` pin in `.tooling.json`.

Issues in the release: the `Issues Included` table in `.sdlc/releases/$V/RELEASE_NOTES.md`,
else ids at the start of commit subjects in range
(`git -C $P log --format=%s $PREV..$TAG | grep -oE "^$KEY-[0-9]+" | sort -u`), not body
mentions.

Decide per issue whether it shipped plugin code: list the paths its commits in range touch,
and check whether any is inside `load-html-files/`. Decide by path, never by commit subject:

```bash
ID=<issue id>   # \b keeps QPP-6 from matching QPP-61
for c in $(git -C $P log --format=%H --grep="^$ID\b" -E $PREV..$TAG); do
  git -C $P diff-tree --no-commit-id --name-only -r $c
done | grep -q '^load-html-files/' && echo shipped || echo non-shipping
```

For each issue:

- Stage: `Deployed`, **only if it shipped plugin code**. An issue whose commits in range touch
  only paths outside `load-html-files/` (tests, tooling, marketing, docs) keeps its Stage as it is.
- Resolution: `Done`, if the issue is not already resolved.
- Fix versions: `$V`, only if that version already exists in the project's Fix versions
  bundle (check the field schema with `get_issue_fields_schema`). If it does not, say so in
  the report and leave the field alone; never create the version.
- Comment, on every issue in range including non-shipping ones: `Released in $V`, with the
  GitHub release link (`https://github.com/alanef/load-html-files/releases/tag/$TAG`) and, for anything
  user-visible, one line on what changed.

## Tell the user what is still manual

Always end with these, explicitly:

- **Confirm the release on wordpress.org.** Release confirmation holds `$V` until a
  committer approves it from the confirmation email or the plugin's release confirmation
  page. Until then the directory still serves `$PREV`.
- Any server side follow-ups recorded in the release notes.

Then report: tag, release.yml run, what was confirmed, changelog result, YouTrack updates. Put any `CHANGELOG PAGE NOT UPDATED` line on its own, first.

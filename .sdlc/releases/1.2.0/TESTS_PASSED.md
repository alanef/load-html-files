# Tests Passed: 1.2.0

**Date:** 2026-10-23T12:32:41Z
**Commit:** f1f3a28dc0f2048e9b801f4fa4b746d5e05e7199
**Tracked changes at run time:** 1

| Gate | Result |
|---|---|
| composer install | pass |
| composer validate --strict | pass |
| composer run check | pass |
| version consistency | pass |
| build (composer run build) | pass |
| Plugin Check | not run locally (no plugin-check script); CI runs it on the built zip |
| PHPUnit (npm test) | pass |

PHPUnit summaries:

    OK (3 tests, 6 assertions)

Full output: tests.log (not committed).

# Tests Passed: 1.2.0

**Date:** 2026-10-23T11:10:15Z
**Commit:** f7ed3fe7d65aabe9946b2878753309a436d81516
**Tracked changes at run time:** 0

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

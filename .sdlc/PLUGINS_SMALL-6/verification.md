# Combined Verification Report: PLUGINS_SMALL-6 + PLUGINS_SMALL-7 in Load HTML Files

**Issues:** PLUGINS_SMALL-6 (opt-in notice) and PLUGINS_SMALL-7 (-Free plugin map gap), shipped together via free_plugin_lib 1.3.3
**Date:** 2026-10-23
**Commit:** 1f982b1 (library v1.3.3 = 4f34052)

## Executive Summary

| Review | Status |
|--------|--------|
| Commit Integrity | not-applicable (the issues claim no LHF SHAs); change-set 1f982b1 non-empty |
| Code Review | passed. The library was reviewed independently (see SUE .sdlc/PLUGINS_SMALL-6); LHF's own diff is reviewed below |
| Acceptance Review | passed, against what LHF 1.1.1 actually shipped |
| Browser-Test Gate | n-a: no layout change. Notice markup and redirects checked over HTTP |
| **Overall** | **APPROVED WITH NOTES** |

## Baseline: what LHF 1.1.1 ships
Checked in SVN tag 1.1.1: free_plugin_lib **1.0.1**. That version has no setup notice, posts opt-ins to the older
octopus endpoint (which subscribes), redirects once after activation from any admin page, and registers its own
uninstall hook, which replaced LHF's.
So the PLUGINS_SMALL-6 "can't dismiss" bug and the PLUGINS_SMALL-7 "subscribes nobody" bug never reached LHF users.
The changelog describes what 1.3.3 changes for them, not those fixes.

## LHF's own change (1f982b1)
- `composer.json`: free_plugin_lib `^1.3.3`.
- `control/class-uninstall.php`: Alan's decision (2026-10-23) is that deleting the plugin must keep user content.
  Under 1.0.1, LHF's routine never ran, because the library's hook replaced it. With 1.3.x it would run for the first
  time and force-delete every html_files post, all html_files_category terms and uploads/html_files. It now deletes
  only `load-html-files-settings` and calls `Main::plugin_uninstall('LHF-Free')`. The content-deleting code is
  removed (git history keeps it), and a comment says content is deliberately kept.

## Checks (wp-env, WordPress 7.1.3, PHP 8.4, WP_DEBUG_DISPLAY false for the HTTP run)
- `composer run check` clean; `npm test` OK (3 tests, 6 assertions); checks.yml green on 1f982b1 (run 39943665646).
- HTTP, as admin with `LHF-Free_form_rendered=rendering`:
  - dashboard returns 200, and the admin-ajax heartbeat returns 200 (no 1.3.1-style hijack);
  - the notice has `data-shortname="LHF-Free"` and links to `options-general.php?page=ffpl-opt-in-LHF-Free`;
  - dismiss returns `{"success":true}`, `LHF-Free_notice_dismissed`=1, and the notice is gone on reload;
  - LHF settings (`edit.php?post_type=html_files&page=load-html-files-settings`) returns 302 to the opt-in screen;
  - Skip returns 302, the status becomes `optout`, and settings then returns 200.
- Opt-in submission (`pre_http_request` capture, fake 200, no real webhook call): result true, URL
  `https://campaign.email.fw9.uk/api/webhook/product`, `plugin_id` = `lhf`.
- Uninstall, through the real path (`wp plugin deactivate` then `wp plugin uninstall --skip-delete`, using the registered
  `["\Load_HTML_Files\Control\Uninstall","uninstall"]`):
  - before: settings `{"x":"1"}`, status `optin`, post 5, term 2, file uploads/html_files/uninstall-test.html;
  - after: settings and status are both gone; the post, the term and the file remain; the plugin files are intact.
  - The test data was removed afterwards.

## Notes
- LHF's own "translation loaded too early" notice for `load-html-files` predates this work and remains. On a site that
  displays PHP notices it breaks admin logins ("headers already sent"). Follow-up.
- The settings page still calls `do_action('ffpl_ad_display')`, which outputs nothing since library 1.3.0. Harmless.

## Commit Range Covered
| SHA | Subject |
|-----|---------|
| 1f982b1 | Take free_plugin_lib 1.3.3; keep user content on uninstall (PLUGINS_SMALL-6, PLUGINS_SMALL-7) |

Library range: free_plugin_lib v1.3.0..v1.3.3. af5adaa (Plugin Check fixes) has no ticket and is covered in the release
notes and TESTS_PASSED. No out-of-range commits.

---
Verified by: AI Assistant
Date: 2026-10-23

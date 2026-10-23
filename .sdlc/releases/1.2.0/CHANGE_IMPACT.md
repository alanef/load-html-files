# Change Impact Assessment: 1.2.0

## Risk Level: Medium

### Factors Considered
- Number of issues: 3 + 1 untracked fix
- Security-sensitive changes: uninstall behaviour (narrowed), direct-access guard
- API changes: no
- Database changes: no schema change. Library options `LHF-Free_form_rendered` and user meta `LHF-Free_notice_dismissed` are new uses of the same prefix.
- Breaking changes: no

Medium, not Low, because the library jumps three minor versions (1.0.1 to 1.3.3). That adds a new admin notice, a settings-page redirect and a new opt-in endpoint for every LHF site.

## Affected Areas

### Code Areas
- Bundled free_plugin_lib (vendor/alanef/free_plugin_lib): opt-in notice, redirect, opt-in submission, no advert.
- control/class-uninstall.php: now deletes only `load-html-files-settings` and the library's opt-in status.
- control/autoloader.php: ABSPATH guard (the file isn't loaded).

### Configuration
- None.

### Dependencies
- alanef/free_plugin_lib 1.0.1 to 1.3.3 (constraint ^1.3.3).

## Behaviour users will notice
- Admins who haven't chosen see a dismissable setup notice. Opening LHF's settings sends them to the opt-in screen until they click Allow or Skip.
- Opt-ins go to the current mailing service (plugin_id `lhf`). Earlier opt-ins on the old service aren't migrated.
- The first copy of the library to load serves every Fullworks free plugin on a site.

## Rollback Plan

### Immediate Rollback
```bash
git revert <Release 1.2.0>^..<Release 1.2.0>   # then re-release as 1.2.1
```
A plain rollback to 1.1.1's code isn't enough. With `^v1.0.0` and no committed lock, a rebuild would still bundle 1.3.3.
To return to 1.0.1 behaviour, pin `"alanef/free_plugin_lib": "1.0.1"`.

### Database Rollback
N/A

### Configuration Rollback
N/A

## Monitoring
- wp.org support forum for load-html-files: notices, redirects, opt-in.
- Reports of a broken admin login on sites that display PHP notices: LHF's pre-existing early textdomain notice.

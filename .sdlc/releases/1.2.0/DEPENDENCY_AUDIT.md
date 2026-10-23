# Dependency Audit: 1.2.0

**Date:** 2026-10-23T11:09:33Z
**Commit:** 1f982b1

| Tree | Policy | Exit | Result |
|---|---|---|---|
| plugin composer (no dev) | block | 0 | clean |
| root composer (tooling) | warn | 0 | clean |
| npm (dev env / build) | warn | 1 | advisories |

## plugin composer (no dev)
```
No security vulnerability advisories found.
```
## root composer
```
No security vulnerability advisories found.
```
## npm
```
### repository root (exit 1)
fast-uri vulnerable to inconsistent host case normalization via percent-encoded octets - https://github.com/advisories/GHSA-hrr3-gc8f-f4qj
fix available via `npm audit fix`
node_modules/fast-uri

http-cache-semantics  <=4.2.0
Severity: high
http-cache-semantics max-stale handling can disclose cross-user cached responses - https://github.com/advisories/GHSA-ch52-4w7c-c8xp
fix available via `npm audit fix`
node_modules/http-cache-semantics

proxy-addr  1.1.0 - 2.0.7
Severity: critical
proxy-addr vulnerable to IP spoofing via IPv4-mapped IPv6 trust subnet - https://github.com/advisories/GHSA-jqcg-44mw-7w3h
fix available via `npm audit fix`
node_modules/proxy-addr

qs  2.2.5 - 6.15.3
Severity: moderate
qs array-limit bypass via bracket-key comma parsing - https://github.com/advisories/GHSA-x5fp-wj9c-mxmx
qs: Denial of Service via Attacker Controlled isBuffer - https://github.com/advisories/GHSA-4mjr-xmp4-gh2g
fix available via `npm audit fix`
node_modules/qs
  body-parser  1.20.5 - 1.20.6
  Depends on vulnerable versions of qs
  node_modules/body-parser
  express  4.22.2
  Depends on vulnerable versions of qs
  node_modules/express
    @wp-playground/cli  >=3.1.38
    Depends on vulnerable versions of express
    node_modules/@wp-playground/cli


12 vulnerabilities (5 moderate, 4 high, 3 critical)

To address issues that do not require attention, run:
  npm audit fix

To address all issues (including breaking changes), run:
  npm audit fix --force

```

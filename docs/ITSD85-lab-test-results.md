# Test Results — ITSD 85 Lab (Automated CI/CD)

All figures below are taken from the GitHub Actions API (`/actions/runs`, `/actions/runs/{id}/jobs`) for the
repository `RodAndro/itsd85-cicd-lab`, plus direct HTTP probes of the deployed container.
**No number in this document is estimated.** Where something could not be measured, it is listed under
*Limitations* instead of being guessed.

- Date of measurements: **2026-11-17**
- GitHub timestamps are UTC; local time was UTC+8 (Asia/Manila)
- Application version = the 40-character commit SHA, reported by `GET /health`

## 1. Environment under test

| Component | Version / value |
|---|---|
| Development host | Windows, Node.js v24.19.0, npm 11.17.0, git 2.55.0.windows.3 |
| CI runner | GitHub-hosted `ubuntu-latest`, Node.js 24 |
| Deployment server | VirtualBox 7.2.22 VM `itsd85-server`, Ubuntu Server 26.04.1 LTS |
| VM resources | 2 vCPU, 3072 MB RAM, 25 GB VDI |
| Docker | 29.1.3 (build 29.1.3-0ubuntu4.1) |
| Docker Compose | 2.40.3+ds1-0ubuntu1 |
| Self-hosted runner | `itsd85-server`, labels `self-hosted, Linux, X64, deploy`, systemd service, status **online** |
| Port forwarding | `2222 -> 22` (ssh), `8080 -> 8080` (web), VirtualBox NAT |
| Branch protection | ruleset `protect-main`: pull request required, required status check `test`, force-push and deletion blocked |

## 2. Local verification (Part A)

Run from `itsd85-lab/` on the development host and repeated inside the Ubuntu VM.

| Command | Host result | VM result |
|---|---|---|
| `npm ci` | exit 0, 0 vulnerabilities | exit 0 |
| `npm run lint` | exit 0, no output | exit 0, no output |
| `npm test` | **7 tests, 7 pass, 0 fail** (exit 0) | **7 tests, 7 pass, 0 fail** (exit 0) |

Test names: `add(2, 3) returns 5`, `GET /api/add returns the sum as JSON`,
`GET /api/add rejects a nonnumeric operand with HTTP 400`,
`GET /api/add rejects missing and nonfinite operands`,
`GET / displays the lab heading and current version`,
`GET /health returns HTTP 200 and the current version`,
`GET / and /health return APP_VERSION when supplied by the deployment`.

## 3. CI job results (Part 4)

Every push to `main` and every pull request into `main` triggered the `test` job. All steps passed except where
Test B introduced a deliberate defect.

| Step | Result |
|---|---|
| Get the code | success |
| Install Node.js 24 | success |
| Install dependencies (`npm ci`) | success |
| Lint | success |
| Run tests | success on good changes, **failure** on Test B |
| Post steps / Complete job | success |

## 4. End-to-end pipeline runs

`test` duration and `deploy` duration are the job durations reported by GitHub. *Run wall time* is
`created_at -> updated_at` for the workflow run, i.e. the real merge-to-live lead time including queueing.

| Run ID | Commit SHA | Branch | test | deploy | Run wall time |
|---|---|---|---|---|---|
| 44102645203 | `1b4febbbe17f026fab59ad33bdea9673c9aec0af` | main | success, 9 s | success, 34 s | 614 s (runner not yet installed; job queued) |
| 44104882479 | `6f63295f87e4220a691b7eec2c426db0b68829b0` | main | success, 9 s | success, 21 s | **39 s** |
| 44106060307 | `ece28f635a8eaee722658881b7376a0f92f663bf` | main | success, 23 s | success, 26 s | **58 s** |
| 44107222362 | `92ea9be423c973d323842d1c94355ab5d436bac3` | main | success, 6 s | success, 25 s | **38 s** |
| 44107666276 | `53d76b4b372ffe6aaf4a890bbd74830a43904615` | main | success, 8 s | success, 22 s | **36 s** |

Excluding the first run (which waited for the runner to exist):

- Mean lead time: **42.75 s**, median **38.5 s**, range **36–58 s**, n = 4.
- Mean `test` job: 11.5 s. Mean `deploy` job: 23.5 s.

## 5. Test A — a good change deploys automatically

Pull request **#1** `Personalize homepage title` (`feature/greeting`).

- On the pull request: `test` = success, `deploy` = **skipped** — pull requests never deploy.
- Merged at `2026-11-17T14:35:58Z`, producing SHA `6f63295`.
- Run `44104882479`: `test` success -> `deploy` success. Deploy steps, all success:
  Get the code, Build image and restart container, Health check, Remove unused image layers.
- Probe from the Windows host through the port forward:
  `GET /health` -> `{"status":"ok","version":"6f63295f87e4220a691b7eec2c426db0b68829b0"}` — the deployed
  version equals the merge SHA.
- `GET /` contains `ITSD 85 CI/CD Lab - Rodan`.

**Result: PASS.** Lead time 39 s (merge to live), deploy job 21 s.

## 6. Test B — a broken change is blocked

Two independent attempts changed `const result = a + b;` to `const result = a - b;`.

| PR | Branch | Run ID | test | deploy | Merge outcome |
|---|---|---|---|---|---|
| #2 | `bug/broken-add` | 44105110496 | **failure**, 13 s | skipped | PR closed without merging |
| #3 | `bug/broken-add2` | 44105330939 | **failure**, 8 s | skipped | merge rejected, HTTP 405 |

Failing step in both runs: **`6. Run tests`**. Locally this change produces 3 failing assertions and exit code 1:

```
tests 7 | pass 4 | fail 3
✖ add(2, 3) returns 5                  -1 !== 5
✖ GET /api/add returns the sum as JSON  result: -1 !== 5
✖ GET /api/add rejects missing and nonfinite operands   200 !== 400
```

GitHub merge attempt on PR #3 was refused with:

```
405 Repository rule violations found
Required status check "test" is failing.
```

While both PRs were open, `GET /health` still returned the previous SHA `6f63295...`, confirming the server was
untouched.

**Result: PASS.** Failing test blocked the merge; nothing was deployed.

## 7. Test C — bad release and rollback

A change that the test-suite cannot detect: the heading became `ITSD 85 BROKEN RELEASE`.

### 7.1 The bad release deploys

- PR **#4** (`feature/broken-release`): `test` = success (7/7 pass), so the gate allowed the merge.
- Merged at `2026-11-17T14:42:28Z` -> SHA `ece28f63`.
- Run `44106060307`: `test` success 23 s, `deploy` success 26 s, wall time 58 s.
- Probe showed `{"status":"ok","version":"ece28f635a8eaee722658881b7376a0f92f663bf"}` and the page contained
  `BROKEN RELEASE`.

This is the expected limitation of a test-only gate: it verifies behaviour, not intent.

### 7.2 Method 1 — rollback through the pipeline

```bash
git switch main
git switch -c revert/broken-release
git revert -m 1 --no-edit HEAD      # HEAD was a merge commit, so -m 1 was required
```

- PR **#5** (`revert/broken-release`): `test` success 7 s, `deploy` skipped; merged to SHA `92ea9be`.
- Run `44107222362`: `test` success 6 s, `deploy` success 25 s, wall time **38 s**.
- Probe: `{"status":"ok","version":"92ea9be423c973d323842d1c94355ab5d436bac3"}`, page heading back to
  `ITSD 85 CI/CD Lab - Rodan`, string `BROKEN RELEASE` absent.

**Result: PASS.** Time to restore 38 s, no manual server access.

### 7.3 Method 2 — emergency rollback on the server

Images retained on the server, one tag per commit (verified with `docker image ls itsd85-app`):

```
REPOSITORY   TAG                                          SIZE
itsd85-app   ece28f635a8eaee722658881b7376a0f92f663bf     255MB   (bad release)
itsd85-app   6f63295f87e4220a691b7eec2c426db0b68829b0     255MB   (known good)
itsd85-app   1b4febbbe17f026fab59ad33bdea9673c9aec0af     255MB   (initial)
itsd85-app   manual                                       255MB   (dry run)
```

```bash
cd ~/actions-runner/_work/itsd85-cicd-lab/itsd85-cicd-lab
APP_VERSION=6f63295f87e4220a691b7eec2c426db0b68829b0 docker compose -p itsd85 up -d --no-build
```

The container was recreated with no build step, and `GET /health` immediately returned
`6f63295...`, a sub-5-second outage window. The broken commit was later reverted in Git (Method 1) so the next
deploy did not reintroduce it.

**Result: PASS.**

## 8. Comparison of rollback methods

| | Method 1 (`git revert`) | Method 2 (`docker compose up --no-build`) |
|---|---|---|
| Measured time | 38 s | < 5 s |
| Rebuild required | yes | no |
| Git history | corrected permanently | unchanged until a revert is also made |
| Requires server access | no | yes |
| When to use | default, keeps history truthful | live incident, stop the bleeding first |

## 9. Branch protection evidence

`protect-main` was applied after Test B; the negative test was repeated as PR **#3**.

- PR #3 reported `mergeable_state = blocked` for the whole observation window.
- Direct `git push origin main` was rejected by the ruleset, forcing every later change through a pull request
  (PRs #5 and #6 were merged this way).

## 10. Corrections to earlier reported numbers

Earlier in the session two stopwatch readings were quoted as 16 s and 22 s. Those timers started *after* the
merge response had already returned and therefore under-count the lead time. The authoritative values from the
GitHub run records are **39 s** (Test A) and **38 s** (Test C, Method 1). The table in `README.md` has been
updated to match this document.

## 11. Limitations — what was not measured

- **Sample size is small.** n = 4 automated deploys on one application, one server, one network. Results are
  indicative, not statistically meaningful.
- **CI duration is not comparable to a bare-metal server.** The host has Hyper-V active, so VirtualBox runs
  through the Windows Hypervisor Platform; Docker builds were slower than the lab sheet's two-minute estimate.
- **No load or concurrency testing.** The pipeline was never exercised with two deploys at once; the
  `concurrency: production` group is configured but not stress-tested.
- **Rollback Method 2 timing is a bound, not an instrumented average.** "Under 5 s" reflects container
  recreation time observed through `docker ps`; it was not sampled repeatedly.
- **No public exposure.** The deployment is reachable only through the laptop's port forward; no HTTPS,
  DNS name or public IP was configured.
- **The `/api/docs` and database parts of the RGV project are out of scope** for this lab and were not touched.

## 12. Reproduction

```powershell
# 1. Local
cd itsd85-lab; npm ci; npm run lint; npm test

# 2. Server (VM)
ssh -p 2222 deploy@localhost
sudo ./actions-runner/svc.sh status        # active (running)

# 3. Observe a deploy
#    merge a pull request into main, then:
curl http://localhost:8080/health          # version must equal the merge SHA
```

## Appendix A — all workflow runs observed

| Run ID | Branch | Event | Conclusion |
|---|---|---|---|
| 44102645203 | main | push | success (first deploy, 614 s incl. queueing) |
| 44104693651 | feature/greeting | pull_request | success (deploy skipped) |
| 44104882479 | main | push | success (Test A) |
| 44105110496 | bug/broken-add | pull_request | **failure** (Test B #1) |
| 44105330939 | bug/broken-add2 | pull_request | **failure** (Test B #2) |
| 44105977904 | feature/broken-release | pull_request | success (deploy skipped) |
| 44106060307 | main | push | success (bad release deployed) |
| 44107139607 | revert/broken-release | pull_request | success (deploy skipped) |
| 44107222362 | main | push | success (rollback live) |
| 44107585956 | docs/results-table | pull_request | success (deploy skipped) |
| 44107666276 | main | push | success (results published) |

## Appendix B — links

- Repository: https://github.com/RodAndro/itsd85-cicd-lab
- Release v1.0: https://github.com/RodAndro/itsd85-cicd-lab/releases/tag/v1.0
- Actions history: https://github.com/RodAndro/itsd85-cicd-lab/actions
- Pull requests: https://github.com/RodAndro/itsd85-cicd-lab/pulls?q=is%3Apr

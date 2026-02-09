---
name: pre-pr-review
description: Perform a pre-pull-request code review focused on bugs, regressions, security risks, and missing tests. Use when a user asks to open or prepare a PR, asks for a review, asks if code is ready to merge, or asks for final validation before submitting changes.
---

# Pre Pr Review

## Overview

Run a strict quality gate before PR creation. Identify issues first, verify with local checks where possible, and only mark ready when no blocking findings remain.

## Review Workflow

1. Collect review context.
- Check `git status --short` and `git diff --stat`.
- Inspect the full diff for changed files.
- Prioritize migrations, auth, permissions, money logic, queue/job flows, and external API code.

2. Run validation checks proportional to the change.
- Run targeted tests first, then broader suites if needed.
- Run lint/static analysis where configured.
- Report any checks you could not run.

3. Identify findings with severity.
- `P0`: release-blocking/data-loss/security-critical.
- `P1`: high-risk bug or strong regression risk.
- `P2`: correctness/design issue that should be fixed before merge.
- `P3`: minor improvement or clarity issue.

4. Ensure findings are actionable.
- Include file path and line references.
- Explain impact and concrete fix direction.
- Flag missing or weak test coverage for changed behavior.

5. Decide PR readiness.
- `Ready`: no unresolved `P0-P2` findings.
- `Not ready`: at least one unresolved `P0-P2` finding.
- Always summarize residual risk and unexecuted checks.

## Output Format

Use this structure in responses:
- Findings first, ordered by severity.
- Open questions/assumptions next.
- Brief readiness verdict last (`Ready` or `Not ready`).

If there are no findings, say so explicitly and still list residual risks (for example, tests not run or uncovered paths).

## Guardrails

- Do not approve PR readiness based only on passing tests.
- Do not ignore risky code paths that lack tests.
- Do not block on style-only issues when automated formatter/linter can fix them safely.

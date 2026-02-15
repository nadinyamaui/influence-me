---
name: feature-test-video-proof
description: Record browser-based feature validations as MP4 proof artifacts with a machine-readable pass/fail report. Use it after completing UI related features
---

# Feature Test Video Proof

## Workflow

1. Prepare a deterministic browser flow as JSON actions.
2. Run `scripts/record_feature_video.mjs` to execute the flow and capture frames.
3. Convert frames to an MP4 video with `ffmpeg`.
4. Return both artifacts:
- Video file (`.mp4`)
- Report file (`.json`) with `passed`, `actions_executed`, and `error`

## Action File Format

Store actions in a JSON array or in `{ "actions": [...] }`.

```json
{
  "actions": [
    { "type": "goto", "url": "/login" },
    { "type": "waitForSelector", "selector": "input[name='email']" },
    { "type": "type", "selector": "input[name='email']", "text": "${AI_LOGIN_EMAIL}" },
    { "type": "type", "selector": "input[name='password']", "text": "${AI_LOGIN_PASSWORD}" },
    { "type": "click", "selector": "button[type='submit']" },
    { "type": "waitForNavigation" },
    { "type": "assertText", "selector": "body", "text": "Dashboard" }
  ]
}
```

Support `${ENV_VAR}` placeholders for credentials/secrets.
Use `.codex/skills/feature-test-video-proof/scripts/example_actions.json` as a starting template.

## Run Recorder

Use:

```bash
node .codex/skills/feature-test-video-proof/scripts/record_feature_video.mjs \
  --base-url http://127.0.0.1:8000 \
  --actions /tmp/feature-actions.json \
  --output artifacts/videos/feature-proof.mp4 \
  --report artifacts/videos/feature-proof.json
```

Optional flags:
- `--fps 6`
- `--width 1440`
- `--height 900`
- `--hold-ms 1200`
- `--keep-frames`

## Action Types

Use only supported actions:
- `goto`
- `waitForSelector`
- `click`
- `type`
- `press`
- `select`
- `waitForNavigation`
- `waitForTimeout`
- `assertText`

## Operating Rules

- Start the application first (`php artisan serve`) before recording.
- Prefer stable selectors (`data-*` attributes) over brittle positional selectors.
- Keep flows short and task-focused.
- Fail fast when assertions do not pass.
- Include absolute output paths in the final response so the user can open the artifacts directly.
- Do **not** commit `artifacts/` files to git unless the user explicitly asks for that.
- When creating/updating a PR, include the video/report artifact paths in the PR description instead of committing the artifacts.

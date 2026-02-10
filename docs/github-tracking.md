# GitHub RFC Tracking Rules

Use these rules for all new RFC issues and PRs.

## Issues

- Title format: `RFC NNN: Title`
- Milestone: match the RFC folder milestone (for example, `04-clients`)
- Body must contain the full RFC markdown content
- Do not create RFC issues that only link to `rfc/*.md` file paths

### Create an RFC issue from a markdown file

```bash
scripts/github/create-rfc-issue.sh rfc/pending/04-clients/031-client-list-page.md 04-clients "feature,clients"
```

## Pull Requests

- PR body must include exactly one native GitHub issue link line:

```text
Closes #123
```

- Do not use issue comments to link PRs and issues
- Use `.github/pull_request_template.md` as the default structure

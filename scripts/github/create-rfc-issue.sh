#!/usr/bin/env bash
set -euo pipefail

if ! command -v gh >/dev/null 2>&1; then
  echo "gh CLI is required." >&2
  exit 1
fi

if [ "$#" -lt 2 ] || [ "$#" -gt 3 ]; then
  echo "Usage: $0 <rfc-markdown-file> <milestone-title> [label1,label2,...]" >&2
  exit 1
fi

rfc_file="$1"
milestone="$2"
labels_csv="${3:-}"

if [ ! -f "$rfc_file" ]; then
  echo "RFC file not found: $rfc_file" >&2
  exit 1
fi

header="$(sed -n '1p' "$rfc_file" | sed -E 's/^#\s*//')"
rfc_id="$(printf '%s' "$header" | sed -E 's/^([0-9]{3}).*/\1/')"
rfc_title="$(printf '%s' "$header" | sed -E 's/^[0-9]{3}\s*-\s*//')"

if ! printf '%s' "$rfc_id" | grep -Eq '^[0-9]{3}$'; then
  echo "Could not parse RFC id from first line in $rfc_file" >&2
  exit 1
fi

title="RFC ${rfc_id}: ${rfc_title}"

tmp_body="$(mktemp)"
cat "$rfc_file" > "$tmp_body"

cmd=(gh issue create --title "$title" --milestone "$milestone" --body-file "$tmp_body")

if [ -n "$labels_csv" ]; then
  IFS=',' read -r -a labels <<< "$labels_csv"
  for label in "${labels[@]}"; do
    label_trimmed="$(printf '%s' "$label" | xargs)"
    if [ -n "$label_trimmed" ]; then
      cmd+=(--label "$label_trimmed")
    fi
  done
fi

"${cmd[@]}"

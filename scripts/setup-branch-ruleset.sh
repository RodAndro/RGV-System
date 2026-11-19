#!/usr/bin/env bash
#
# Apply the `main` branch ruleset (requirement DP5).
#
# Rulesets are a GitHub repository setting, so they cannot be enforced from the
# working tree. This script applies the committed definition in
# .github/rulesets/protect-main.json so the rule is reproducible and auditable
# rather than a manual, undocumented click.
#
# Requires: GitHub CLI authenticated with admin rights on the repository.
#
# Usage:
#   ./scripts/setup-branch-ruleset.sh <owner>/<repo>

set -euo pipefail

REPO="${1:-}"

if [[ -z "$REPO" ]]; then
    echo "Usage: $0 <owner>/<repo>" >&2
    echo "Example: $0 myorg/itsd85-final-bsit4a-teamnexus" >&2
    exit 1
fi

if ! command -v gh >/dev/null 2>&1; then
    echo "ERROR: GitHub CLI (gh) is required. https://cli.github.com/" >&2
    exit 1
fi

RULESET="$(dirname "$0")/../.github/rulesets/protect-main.json"

if [[ ! -f "$RULESET" ]]; then
    echo "ERROR: ruleset definition not found at $RULESET" >&2
    exit 1
fi

echo "Applying ruleset to ${REPO} from ${RULESET}"

# Update if it already exists, otherwise create it.
if gh api "repos/${REPO}/rulesets" --jq '.[].name' 2>/dev/null | grep -qx 'protect-main'; then
    ID=$(gh api "repos/${REPO}/rulesets" --jq '.[] | select(.name=="protect-main") | .id')
    echo "Updating existing ruleset (id=${ID})"
    gh api --method PUT "repos/${REPO}/rulesets/${ID}" --input "$RULESET"
else
    echo "Creating ruleset"
    gh api --method POST "repos/${REPO}/rulesets" --input "$RULESET"
fi

echo
echo "Active rules on the default branch:"
gh api "repos/${REPO}/rules/branches/main" --jq '.[] | "  - \(.type)"'

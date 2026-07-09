#!/usr/bin/env bash
set -euo pipefail

# Registers a self-hosted GitHub Actions runner on this VPS.
#
# 1. GitHub → AHA-Technocrats/omics-logic-crm → Settings → Actions → Runners
# 2. Click "New self-hosted runner", choose Linux / x64
# 3. Copy the registration token from the config command
# 4. Run: ./scripts/setup-github-actions-runner.sh YOUR_TOKEN

if [ "${1:-}" = "" ]; then
  echo "Usage: $0 <registration-token>"
  echo "Get the token from: Repo Settings → Actions → Runners → New self-hosted runner"
  exit 1
fi

TOKEN="$1"
RUNNER_DIR="/home/ahatech1/actions-runner"
RUNNER_VERSION="2.335.1"
RUNNER_URL="https://github.com/actions/runner/releases/download/v2.335.1/actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz"
REPO_URL="https://github.com/AHA-Technocrats/omics-logic-crm"
RUNNER_NAME="ahatech-vps-omics"

mkdir -p "$RUNNER_DIR"
cd "$RUNNER_DIR"

if [ ! -f ./config.sh ]; then
  curl -fsSL -o actions-runner.tar.gz "$RUNNER_URL"
  tar xzf actions-runner.tar.gz
  rm actions-runner.tar.gz
fi

./config.sh \
  --url "$REPO_URL" \
  --token "$TOKEN" \
  --name "$RUNNER_NAME" \
  --labels "ahatech-vps" \
  --unattended \
  --replace

sudo ./svc.sh install
sudo ./svc.sh start
sudo ./svc.sh status

echo ""
echo "Runner installed. It will pick up jobs with: runs-on: [self-hosted, linux, ahatech-vps]"

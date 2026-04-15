#!/usr/bin/env bash
# Script tu dong setup CCPM cho du an cms-nvd
# Chay: bash .claude/ccpm-setup.sh

set -e

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR"
echo "==> Project: $PROJECT_DIR"

# 1. Kiem tra git
if ! command -v git >/dev/null 2>&1; then
  echo "[X] Chua cai git. Cai tu https://git-scm.com roi chay lai."
  exit 1
fi

# 2. Kiem tra gh CLI
if ! command -v gh >/dev/null 2>&1; then
  echo "[!] Chua co GitHub CLI."
  echo "    Cai bang: winget install --id GitHub.cli"
  echo "    Sau do mo lai terminal va chay lai script nay."
  exit 1
fi

# 3. Dang nhap gh neu chua
if ! gh auth status >/dev/null 2>&1; then
  echo "==> Dang nhap GitHub CLI..."
  gh auth login
else
  echo "==> gh da dang nhap."
fi

# 4. Khoi tao git repo neu chua co
if [ ! -d .git ]; then
  echo "==> Khoi tao git repo..."
  git init
  git add .
  git commit -m "init cms-nvd" || true
fi

# 5. Tao GitHub repo neu chua co remote
if ! git remote get-url origin >/dev/null 2>&1; then
  echo "==> Tao GitHub repo (private)..."
  REPO_NAME="$(basename "$PROJECT_DIR")"
  gh repo create "$REPO_NAME" --private --source=. --push
else
  echo "==> Remote origin da ton tai: $(git remote get-url origin)"
fi

# 6. Cai/cap nhat CCPM skill
SKILL_DIR=".claude/skills/ccpm"
if [ -d "$SKILL_DIR" ]; then
  echo "==> CCPM skill da ton tai. Cap nhat..."
  rm -rf "$SKILL_DIR"
fi
mkdir -p .claude/skills
TMP_DIR=".claude/.ccpm-tmp"
rm -rf "$TMP_DIR"
echo "==> Clone ccpm..."
git clone --depth 1 https://github.com/automazeio/ccpm.git "$TMP_DIR"
mv "$TMP_DIR/skill/ccpm" "$SKILL_DIR"
rm -rf "$TMP_DIR"

echo ""
echo "[OK] CCPM da san sang!"
echo "     Skill:    $SKILL_DIR"
echo "     Huong dan: .claude/CCPM-HUONG-DAN.md"
echo ""
echo "Mo Claude Code va noi: 'Toi muon dung ccpm de len ke hoach cho tinh nang X'"

# Hướng dẫn sử dụng CCPM (Claude Code PM) trong dự án cms-nvd

CCPM là một **Agent Skill** giúp Claude Code quản lý dự án theo quy trình: PRD → Epic → Task → GitHub Issue → thực thi song song. Skill đã được cài tại [.claude/skills/ccpm/](skills/ccpm/).

## 1. Yêu cầu trước khi dùng

- **Git** đã cài đặt.
- **GitHub CLI** (`gh`) đã đăng nhập: chạy `gh auth login`.
- Dự án `cms-nvd` phải là một repo GitHub (hiện chưa phải repo git — nếu muốn dùng đầy đủ CCPM cần `git init` và push lên GitHub).
- Claude Code đang chạy trong thư mục gốc dự án để nhận skill trong `.claude/skills/`.

## 2. Cấu trúc thư mục CCPM sẽ tạo ra

```
.claude/
├── skills/ccpm/           # Skill đã cài (không sửa)
├── prds/                  # Product Requirement Documents
│   └── <ten-tinh-nang>.md
└── epics/
    └── <ten-tinh-nang>/
        ├── epic.md        # Epic kỹ thuật
        ├── tasks/         # Danh sách task nhỏ
        └── updates/       # Tiến độ từ agent
```

Các thư mục `prds/`, `epics/` sẽ tự sinh khi bạn bắt đầu tính năng đầu tiên.

## 3. Quy trình làm việc (5 giai đoạn)

CCPM **không dùng slash command**, mà kích hoạt qua ngôn ngữ tự nhiên. Bạn chỉ cần nói với Claude theo các cụm từ dưới đây:

| Giai đoạn | Câu lệnh gợi ý | Kết quả |
|---|---|---|
| 1. Lập kế hoạch | `Tôi muốn xây dựng tính năng quản lý bài viết` | Claude brainstorm + tạo PRD tại `.claude/prds/quan-ly-bai-viet.md` |
| 2. Tạo Epic | `parse the quan-ly-bai-viet PRD` | Sinh epic kỹ thuật ở `.claude/epics/quan-ly-bai-viet/epic.md` |
| 3. Chia task | `break down the quan-ly-bai-viet epic` | Tạo file task kèm dependency |
| 4. Đồng bộ GitHub | `sync the quan-ly-bai-viet epic to GitHub` | Tạo issues + worktree riêng |
| 5. Thực thi | `start working on issue 12` | Khởi động agent làm việc song song |

**Các lệnh trạng thái hữu ích:**

- `standup` — báo cáo nhanh tiến độ hôm nay
- `what's next` — việc ưu tiên kế tiếp
- `what's blocked` — task đang bị chặn
- `close issue 12` — đóng issue khi xong
- `merge the quan-ly-bai-viet epic` — gộp kết quả epic

## 4. Các bước bắt đầu lần đầu

1. Khởi tạo git nếu chưa có:
   ```bash
   git init
   git add .
   git commit -m "init cms-nvd"
   gh repo create cms-nvd --private --source=. --push
   ```
2. Xác thực GitHub CLI: `gh auth login`.
3. Mở Claude Code tại thư mục `d:/PROJECTS/FREELANCER/cms-nvd`.
4. Nói với Claude: *"Tôi muốn dùng ccpm để lên kế hoạch cho tính năng X"* — skill sẽ tự kích hoạt.

## 5. Mẹo sử dụng

- Giữ mỗi PRD tập trung vào **một** tính năng để epic không quá lớn.
- Khi task có thể chạy song song, hãy nói rõ *"run these in parallel"* để CCPM spawn nhiều agent cùng lúc.
- Định kỳ chạy `standup` để cập nhật file `updates/` — dùng làm nhật ký dự án.
- Không chỉnh sửa thủ công file trong `.claude/skills/ccpm/`; nếu cần nâng cấp, xoá thư mục đó và clone lại từ https://github.com/automazeio/ccpm.

## 6. Cập nhật CCPM

```bash
cd d:/PROJECTS/FREELANCER/cms-nvd
rm -rf .claude/skills/ccpm
git clone --depth 1 https://github.com/automazeio/ccpm.git .claude/.tmp
mv .claude/.tmp/skill/ccpm .claude/skills/ccpm
rm -rf .claude/.tmp
```

## 7. Tham khảo

- Repo gốc: https://github.com/automazeio/ccpm
- Skill definition: [.claude/skills/ccpm/SKILL.md](skills/ccpm/SKILL.md)
- References: [.claude/skills/ccpm/references/](skills/ccpm/references/)

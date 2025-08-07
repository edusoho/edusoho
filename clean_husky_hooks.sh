#!/bin/bash

HOOKS_DIR=".git/hooks"

# 检查目录是否存在
if [ ! -d "$HOOKS_DIR" ]; then
  echo "目录 $HOOKS_DIR 不存在。请确保你在 Git 仓库根目录中运行此脚本。"
  exit 1
fi

# 查找包含 "husky" 字样的文件
for file in "$HOOKS_DIR"/*; do
  if grep -q "husky" "$file" 2>/dev/null; then
    echo "删除包含 'husky' 的文件: $file"
    rm "$file"
  fi
done

echo "操作完成。"


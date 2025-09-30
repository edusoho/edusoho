#!/bin/bash
set -e

# 检查 app 和 app-h5 目录是否存在
if [ ! -d "edusoho" ]; then
  echo "目录 edusoho 不存在，退出脚本。"
  exit 1
fi

if [ ! -d "edusoho-h5" ]; then
  echo "目录 edusoho-h5 不存在，退出脚本。"
  exit 1
fi

# 检查 app-source 是否已存在
if [ -d "edusoho-source" ]; then
  echo "源码目录 edusoho-source 已存在，请删除后再执行。"
  exit 1
fi

# 创建 app-source 目录
mkdir  edusoho-source

# 复制 app 目录内容（排除 .git）
rsync -av --exclude='.git' edusoho/ edusoho-source/

# 复制 app-h5 目录内容（排除 .git）
rsync -av --exclude='.git' edusoho-h5/ edusoho-source/h5/

# 打包 app-source 目录
zip -r edusoho-source.zip edusoho-source

# 删除临时源码目录
rm -rf edusoho-source

echo "打包完成： edusoho-source.zip"
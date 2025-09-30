#!/bin/bash

# 默认分支名
DEFAULT_BRANCH="develop"
DELETE_BRANCHES=false
CREATE_BRANCH=false
SHOW_STATUS=false
SHOW_DIFF=false
SHOW_DIFF_DETAIL=false
USE_ORIGIN=false
BRANCH_NAMES=()
INCLUDE_PLUGINS=""
EXCLUDE_PLUGINS=""

# 显示帮助信息
show_help() {
    echo "用法: $0 [选项] [分支名]"
    echo ""
    echo "描述: 初始化 plugins 目录下所有 Git 仓库，切换到指定分支"
    echo ""
    echo "参数:"
    echo "  分支名                指定要切换的分支名 (默认: develop)"
    echo ""
    echo "选项:"
    echo "  -c, --create         当分支不存在时创建新分支"
    echo "  -dd, --delete-branch  删除除当前分支外的所有本地分支"
    echo "  -s, --status         显示所有插件的当前分支状态表格"
    echo "  -df, --show-diff     显示分支差异对比表格"
    echo "  --origin             与 -df 配合使用，比较远端分支差异"
    echo "  --detail             与 -df 配合使用，显示差异文件明细"
    echo "  --include PLUGINS    指定要处理的插件列表，用逗号分割 (如: Plugin1,Plugin2)"
    echo "  --exclude PLUGINS    指定要排除的插件列表，用逗号分割 (如: Plugin1,Plugin2)"
    echo "  -h, --help           显示此帮助信息"
    echo ""
    echo "示例:"
    echo "  $0                   # 切换到 develop 分支"
    echo "  $0 main              # 切换到 main 分支"
    echo "  $0 -c feature        # 切换到 feature 分支，不存在则创建"
    echo "  $0 -dd develop       # 切换到 develop 分支并删除其他本地分支"
    echo "  $0 -c -dd main       # 切换到 main 分支，不存在则创建，并删除其他本地分支"
    echo "  $0 -s                # 显示所有插件的当前分支状态"
    echo "  $0 -df               # 显示当前分支与远端当前分支的差异"
    echo "  $0 -df main          # 显示当前分支与 main 分支的差异"
    echo "  $0 -df --origin main # 显示当前分支的远端与 main 分支远端的差异"
    echo "  $0 -df develop main  # 显示 develop 分支与 main 分支的差异"
    echo "  $0 -df --origin develop main  # 显示远端 develop 分支与远端 main 分支的差异"
    echo "  $0 -df --detail      # 显示当前分支与远端当前分支的差异及文件明细"
    echo "  $0 -df --detail --origin develop main  # 显示远端分支差异及文件明细"
    echo "  $0 --include ExamPlugin,QAPlugin develop  # 只处理指定插件"
    echo "  $0 --exclude TestPlugin,OldPlugin -s      # 排除指定插件后显示状态"
    echo "  $0 -df --include ExamPlugin,QAPlugin      # 只对指定插件显示差异"
}

# 检查插件是否应该被处理
should_process_plugin() {
    local plugin_name="$1"

    # 如果设置了包含列表，只处理列表内的插件
    if [[ -n "$INCLUDE_PLUGINS" ]]; then
        IFS=',' read -ra INCLUDE_ARRAY <<< "$INCLUDE_PLUGINS"
        for include_plugin in "${INCLUDE_ARRAY[@]}"; do
            if [[ "$plugin_name" == "$include_plugin" ]]; then
                return 0  # 应该处理
            fi
        done
        return 1  # 不在包含列表中，不处理
    fi

    # 如果设置了排除列表，不处理列表内的插件
    if [[ -n "$EXCLUDE_PLUGINS" ]]; then
        IFS=',' read -ra EXCLUDE_ARRAY <<< "$EXCLUDE_PLUGINS"
        for exclude_plugin in "${EXCLUDE_ARRAY[@]}"; do
            if [[ "$plugin_name" == "$exclude_plugin" ]]; then
                return 1  # 在排除列表中，不处理
            fi
        done
        return 0  # 不在排除列表中，应该处理
    fi

    # 如果没有设置任何过滤，处理所有插件
    return 0
}

# 显示插件分支状态表格
show_plugin_status() {
    echo "=============================================="
    echo "            插件分支状态表格"
    echo "=============================================="
    if [[ -n "$INCLUDE_PLUGINS" ]]; then
        echo "只显示指定插件: $INCLUDE_PLUGINS"
    fi
    if [[ -n "$EXCLUDE_PLUGINS" ]]; then
        echo "排除以下插件: $EXCLUDE_PLUGINS"
    fi
    printf "%-30s %-20s %-10s\n" "插件名称" "当前分支" "状态"
    echo "----------------------------------------------"

    # 切换到 plugins 目录
    if [[ ! -d "plugins" ]]; then
        echo "错误: plugins 目录不存在"
        exit 1
    fi

    cd plugins

    # 获取所有目录的列表
    directories=$(find . -maxdepth 1 -type d | sort)

    # 循环遍历每个目录
    for dir in $directories; do
        # 跳过当前目录和上级目录
        if [[ "$dir" == "." || "$dir" == "./" ]]; then
            continue
        fi

        # 去除目录前缀的 ./
        plugin_name=${dir#./}

        # 检查是否应该处理此插件
        if ! should_process_plugin "$plugin_name"; then
            continue
        fi

        # 进入到每个目录
        cd "$dir"

        # 判断是否是 Git 仓库
        if [ -d ".git" ]; then
            # 获取当前分支
            current_branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
            if [[ $? -eq 0 ]]; then
                # 检查工作区状态
                if git diff-index --quiet HEAD -- 2>/dev/null; then
                    status="clean"
                else
                    status="dirty"
                fi
                printf "%-30s %-20s %-10s\n" "$plugin_name" "$current_branch" "$status"
            else
                printf "%-30s %-20s %-10s\n" "$plugin_name" "unknown" "error"
            fi
        else
            printf "%-30s %-20s %-10s\n" "$plugin_name" "non-git" "N/A"
        fi

        # 返回上级目录
        cd ..
    done

    echo "=============================================="
    echo "状态说明："
    echo "  clean - 工作区干净，无未提交修改"
    echo "  dirty - 工作区有未提交的修改"
    echo "  error - 获取分支信息失败"
    echo "  N/A   - 非Git仓库"
}

# 显示插件分支差异表格
show_plugin_diff() {
    local branch1=""
    local branch2=""
    local comparison_desc=""

    # 根据参数确定比较的分支
    case ${#BRANCH_NAMES[@]} in
        0)
            # 无分支：比较当前分支与远端当前分支
            branch1="HEAD"
            if [[ "$USE_ORIGIN" == true ]]; then
                branch2="origin/HEAD"
                comparison_desc="当前分支远端 vs 默认远端分支"
            else
                branch2="@{upstream}"
                comparison_desc="当前分支 vs 远端当前分支"
            fi
            ;;
        1)
            # 一个分支：比较当前分支与指定分支
            if [[ "$USE_ORIGIN" == true ]]; then
                branch1="@{upstream}"
                branch2="origin/${BRANCH_NAMES[0]}"
                comparison_desc="当前分支远端 vs ${BRANCH_NAMES[0]} 远端"
            else
                branch1="HEAD"
                branch2="${BRANCH_NAMES[0]}"
                comparison_desc="当前分支 vs ${BRANCH_NAMES[0]} 分支"
            fi
            ;;
        2)
            # 两个分支：比较指定的两个分支
            if [[ "$USE_ORIGIN" == true ]]; then
                branch1="origin/${BRANCH_NAMES[0]}"
                branch2="origin/${BRANCH_NAMES[1]}"
                comparison_desc="${BRANCH_NAMES[0]} 远端 vs ${BRANCH_NAMES[1]} 远端"
            else
                branch1="${BRANCH_NAMES[0]}"
                branch2="${BRANCH_NAMES[1]}"
                comparison_desc="${BRANCH_NAMES[0]} 分支 vs ${BRANCH_NAMES[1]} 分支"
            fi
            ;;
        *)
            echo "错误: 最多只能指定两个分支进行比较"
            exit 1
            ;;
    esac

    echo "=============================================="
    echo "            插件分支差异表格"
    echo "=============================================="
    echo "比较说明: $comparison_desc"
    if [[ -n "$INCLUDE_PLUGINS" ]]; then
        echo "只显示指定插件: $INCLUDE_PLUGINS"
    fi
    if [[ -n "$EXCLUDE_PLUGINS" ]]; then
        echo "排除以下插件: $EXCLUDE_PLUGINS"
    fi
    echo "----------------------------------------------"
    printf "%-30s %-15s %-15s %-10s\n" "插件名称" "分支1" "分支2" "差异文件数"
    echo "----------------------------------------------"

    # 切换到 plugins 目录
    if [[ ! -d "plugins" ]]; then
        echo "错误: plugins 目录不存在"
        exit 1
    fi

    cd plugins

    # 获取所有目录的列表
    directories=$(find . -maxdepth 1 -type d | sort)

    # 循环遍历每个目录
    for dir in $directories; do
        # 跳过当前目录和上级目录
        if [[ "$dir" == "." || "$dir" == "./" ]]; then
            continue
        fi

        # 去除目录前缀的 ./
        plugin_name=${dir#./}

        # 检查是否应该处理此插件
        if ! should_process_plugin "$plugin_name"; then
            continue
        fi

        # 进入到每个目录
        cd "$dir"

        # 判断是否是 Git 仓库
        if [ -d ".git" ]; then
            # 获取远程更新
            git fetch origin &>/dev/null

            # 获取实际的分支名称
            local actual_branch1=""
            local actual_branch2=""
            local diff_count="error"

            # 处理分支1
            case $branch1 in
                "HEAD")
                    actual_branch1=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
                    ;;
                "origin/HEAD")
                    actual_branch1="origin/$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')"
                    ;;
                "@{upstream}")
                    actual_branch1=$(git rev-parse --abbrev-ref --symbolic-full-name @{upstream} 2>/dev/null)
                    ;;
                *)
                    actual_branch1="$branch1"
                    ;;
            esac

            # 处理分支2
            case $branch2 in
                "HEAD")
                    actual_branch2=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
                    ;;
                "origin/HEAD")
                    actual_branch2="origin/$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')"
                    ;;
                "@{upstream}")
                    actual_branch2=$(git rev-parse --abbrev-ref --symbolic-full-name @{upstream} 2>/dev/null)
                    ;;
                *)
                    actual_branch2="$branch2"
                    ;;
            esac

            # 检查分支是否存在并计算差异
            if [[ -n "$actual_branch1" ]] && [[ -n "$actual_branch2" ]]; then
                if git rev-parse --verify "$actual_branch1" &>/dev/null && git rev-parse --verify "$actual_branch2" &>/dev/null; then
                    diff_files=$(git diff "$actual_branch1" "$actual_branch2" --name-only 2>/dev/null | wc -l)
                    diff_count="$diff_files"
                else
                    diff_count="branch-missing"
                fi
            fi

            # 简化分支名显示
            display_branch1=${actual_branch1##*/}
            display_branch2=${actual_branch2##*/}

            printf "%-30s %-15s %-15s %-10s\n" "$plugin_name" "$display_branch1" "$display_branch2" "$diff_count"
        else
            printf "%-30s %-15s %-15s %-10s\n" "$plugin_name" "non-git" "non-git" "N/A"
        fi

        # 返回上级目录
        cd ..
    done

    echo "=============================================="
    echo "差异说明："
    echo "  数字     - 两分支间不同的文件数量"
    echo "  error    - 获取差异信息失败"
    echo "  branch-missing - 指定的分支不存在"
    echo "  N/A      - 非Git仓库"

    # 如果启用了详细模式，显示具体的文件差异
    if [[ "$SHOW_DIFF_DETAIL" == true ]]; then
        echo ""
        echo "=============================================="
        echo "            详细文件差异列表"
        echo "=============================================="

        # 此时我们已经在 plugins 目录中了
        for dir in $directories; do
            if [[ "$dir" == "." || "$dir" == "./" ]]; then
                continue
            fi

            plugin_name=${dir#./}
            cd "$dir"

            if [ -d ".git" ]; then
                git fetch origin &>/dev/null

                # 重新获取分支信息 (复制之前的逻辑)
                local actual_branch1=""
                local actual_branch2=""

                case $branch1 in
                    "HEAD")
                        actual_branch1=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
                        ;;
                    "origin/HEAD")
                        actual_branch1="origin/$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')"
                        ;;
                    "@{upstream}")
                        actual_branch1=$(git rev-parse --abbrev-ref --symbolic-full-name @{upstream} 2>/dev/null)
                        ;;
                    *)
                        actual_branch1="$branch1"
                        ;;
                esac

                case $branch2 in
                    "HEAD")
                        actual_branch2=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
                        ;;
                    "origin/HEAD")
                        actual_branch2="origin/$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')"
                        ;;
                    "@{upstream}")
                        actual_branch2=$(git rev-parse --abbrev-ref --symbolic-full-name @{upstream} 2>/dev/null)
                        ;;
                    *)
                        actual_branch2="$branch2"
                        ;;
                esac

                # 获取差异文件列表
                if [[ -n "$actual_branch1" ]] && [[ -n "$actual_branch2" ]]; then
                    if git rev-parse --verify "$actual_branch1" &>/dev/null && git rev-parse --verify "$actual_branch2" &>/dev/null; then
                        diff_files=$(git diff "$actual_branch1" "$actual_branch2" --name-only 2>/dev/null)

                        if [[ -n "$diff_files" ]]; then
                            echo ""
                            echo "=== $plugin_name 差异文件 ==="
                            echo "$diff_files" | sed 's/^/  /'
                        fi
                    fi
                fi
            fi

            cd ..
        done

        echo ""
        echo "=============================================="
    fi

    # 返回到原始目录
    cd ..
}

# 解析命令行参数
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -c|--create)
            CREATE_BRANCH=true
            shift
            ;;
        -dd|--delete-branch)
            DELETE_BRANCHES=true
            shift
            ;;
        -s|--status)
            SHOW_STATUS=true
            shift
            ;;
        -df|--show-diff)
            SHOW_DIFF=true
            shift
            ;;
        --origin)
            USE_ORIGIN=true
            shift
            ;;
        --detail)
            SHOW_DIFF_DETAIL=true
            shift
            ;;
        --include)
            INCLUDE_PLUGINS="$2"
            shift 2
            ;;
        --exclude)
            EXCLUDE_PLUGINS="$2"
            shift 2
            ;;
        -*)
            echo "错误: 未知选项 $1"
            echo "使用 '$0 --help' 查看帮助信息"
            exit 1
            ;;
        *)
            # 如果不是选项，则作为分支名
            if [[ "$SHOW_DIFF" == true ]]; then
                # 对于 -df 选项，可以接受多个分支名
                BRANCH_NAMES+=("$1")
            else
                # 对于其他选项，只能有一个分支名
                if [[ -z "$BRANCH_NAME" ]]; then
                    BRANCH_NAME="$1"
                else
                    echo "错误: 只能指定一个分支名"
                    exit 1
                fi
            fi
            shift
            ;;
    esac
done

# 检查 --include 和 --exclude 选项冲突
if [[ -n "$INCLUDE_PLUGINS" && -n "$EXCLUDE_PLUGINS" ]]; then
    echo "错误: --include 和 --exclude 选项不能同时使用"
    exit 1
fi

# 如果指定了显示状态选项，则只显示状态表格并退出
if [[ "$SHOW_STATUS" == true ]]; then
    show_plugin_status
    exit 0
fi

# 如果指定了显示差异选项，则只显示差异表格并退出
if [[ "$SHOW_DIFF" == true ]]; then
    show_plugin_diff
    exit 0
fi

# 如果没有指定分支名，使用默认分支
if [[ -z "$BRANCH_NAME" ]]; then
    BRANCH_NAME="$DEFAULT_BRANCH"
fi

echo "目标分支: $BRANCH_NAME"
if [[ "$CREATE_BRANCH" == true ]]; then
    echo "分支不存在时将创建新分支"
fi
if [[ "$DELETE_BRANCHES" == true ]]; then
    echo "将删除除目标分支外的所有本地分支"
fi
if [[ -n "$INCLUDE_PLUGINS" ]]; then
    echo "只处理指定插件: $INCLUDE_PLUGINS"
fi
if [[ -n "$EXCLUDE_PLUGINS" ]]; then
    echo "排除以下插件: $EXCLUDE_PLUGINS"
fi
echo ""

# 切换到 plugins 目录
if [[ ! -d "plugins" ]]; then
    echo "错误: plugins 目录不存在"
    exit 1
fi

cd plugins

# 获取所有目录的列表
directories=$(find . -maxdepth 1 -type d)

# 循环遍历每个目录
for dir in $directories; do
    # 跳过当前目录和上级目录
    if [[ "$dir" == "." || "$dir" == "./" ]]; then
        continue
    fi

    # 去除目录前缀的 ./
    plugin_name=${dir#./}

    # 检查是否应该处理此插件
    if ! should_process_plugin "$plugin_name"; then
        continue
    fi

    echo "正在处理 $dir 目录..."

    # 进入到每个目录
    cd "$dir"

    # 判断是否是 Git 仓库
    if [ -d ".git" ]; then
        # 检查当前目录是否已在safe.directory列表中
        repo_path="$(pwd)"
        if ! git config --global --get-all safe.directory | grep -q "^${repo_path}$"; then
            echo "添加 $dir 到 Git safe.directory 列表..."
            git config --global --add safe.directory "$repo_path"
        fi
        # 判断远程分支是否存在指定分支
        git fetch origin
        if git rev-parse --verify origin/"$BRANCH_NAME" > /dev/null 2>&1; then
            # 切换到指定分支
            echo "当前目录 $dir 已存在 Git 仓库，正在切换到 $BRANCH_NAME 分支..."
            git checkout -B "$BRANCH_NAME" origin/"$BRANCH_NAME"
            git pull
        else
            # 检查是否允许创建分支
            if [[ "$CREATE_BRANCH" == true ]]; then
                echo "当前目录 $dir 已存在 Git 仓库，但没有 $BRANCH_NAME 分支，正在创建并切换到 $BRANCH_NAME 分支..."
                if git checkout -B "$BRANCH_NAME" 2>/dev/null; then
                    echo "成功创建并切换到 $BRANCH_NAME 分支"
                else
                    echo "错误: 无法创建 $BRANCH_NAME 分支"
                fi
            else
                echo "警告: 当前目录 $dir 没有 $BRANCH_NAME 分支，跳过 (使用 -c 选项可创建新分支)"
            fi
        fi

        # 只有在指定了删除分支选项时才删除本地分支
        if [[ "$DELETE_BRANCHES" == true ]]; then
            echo "正在删除除当前分支外的所有本地分支..."
            current_branch=$(git rev-parse --abbrev-ref HEAD)

            echo "Deleting all local branches except for $current_branch..."

            # 获取除当前分支外的所有本地分支并删除
            branches_to_delete=$(git branch | grep -v "$current_branch" | grep -v '^\*' | xargs)
            if [[ -n "$branches_to_delete" ]]; then
                git branch -D $branches_to_delete
            else
                echo "没有需要删除的本地分支"
            fi
        fi
    else
        echo "警告: $dir 不是 Git 仓库，跳过"
    fi

    # 返回上级目录
    cd ..
done

echo ""
echo "脚本执行完成！"

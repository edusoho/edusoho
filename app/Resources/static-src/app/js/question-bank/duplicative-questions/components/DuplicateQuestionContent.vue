<template>
    <div class="duplicate-question-content">
        <div class="question-num">
            <button v-for="(item, index) in 20" :key="index" class="numbering">{{ item }}</button>
        </div>
        <div class="question-content mt8">
            <div class="question-head">
                <div class="question-head-item" v-for="(item, index) in headInfo" :key="index">
                    <div class="question-small-title">{{ item.title }}</div>
                    <div class="question-small-content">{{ item.content }}</div>
                </div>
            </div>
            <div class="question-body">
                <question-item />
            </div>
            <div class="question-foot">
                <a-button @click="onEdit" class="mr10">编辑</a-button>
                <a-button @click="confirm">删除</a-button>
            </div>

        </div>
    </div>
</template>
<script>
import QuestionItem from './Item'
export default {
    data() {
        return {
            headInfo: [
                {
                    title: '分类',
                    content: '杭州'
                },
                {
                    title: '类型',
                    content: '单选题'
                },
                {
                    title: '难度',
                    content: '一般'
                },
                {
                    title: '更新日期',
                    content: '2023-9-27'
                }
            ]
        }
    },
    components: {
        QuestionItem
    },
    methods: {
        confirm() {
            this.$confirm({
                title: '真的要删除该题目吗？',
                content: '删除题目，可能会影响课时的练习，请谨慎操作！',
                icon: 'exclamation-circle',
                okText: '确认',
                cancelText: '取消',
                onOk() {console.log("确认")},
                onCancel() {console.log("取消")}
            });
         },
         onEdit() {
            window.location.href = `/question_bank/${$("[name=questionBankId]").val()}/duplicative_question/1/update`
         }
    }
}
</script>
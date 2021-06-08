<template>
    <div v-show="categoryChoices">
        <el-select v-model="categoryData" v-on:change="updateCategory">
            <el-option value="0" :label="'category'|trans"></el-option>
            <el-option
                v-for="choice in categoryChoices"
                :key="choice.id"
                :label="choice.name|trim"
                :value="choice.id">
                <span :style="{ 'padding-left': (choice.depth - 1) * 8 + 'px' }">{{ choice.name|trim }}</span>
            </el-option>
        </el-select>
    </div>

</template>

<script>
    export default {
        name: "category",
        props: {
            type: {
                default: 'course'
            },
            category: {
                default: '0',
            },
        },
        filters: {
            trim: function (value = '') {
                return value.trim();
            }
        },
        methods: {
            getCategoryChoices() {
                let type = this.type ? this.type : 'course';
                this.$axios.get('/category/choices/' + type).then((response) => {
                     this.categoryChoices = this.parseChoicesData(response.data)
                }).catch(error => {
                    this.categoryChoices = this.categoryChoices || []
                })
            },
            updateCategory(value) {
                this.$emit('update:category', value);
            },
            parseChoicesData (data = []) {
                const choicesList = []

                data.forEach(item => {
                    this.getChoiceChildren(choicesList, item)
                })

                return choicesList
            },
            getChoiceChildren (choicesList, data) {
                const { name, id, depth, children } = data
                
                choicesList.push({ name, depth, id })
                
                if (Array.isArray(children) && children.length > 0) {
                    children.forEach(item => {
                        this.getChoiceChildren(choicesList, item)
                    })
                }
            }
        },
        watch: {
            category(value) {
                this.categoryData = value
            }
        },
        data() {
            return {
                categoryChoices: null,
                categoryData: this.category
            };
        },
        created() {
            this.getCategoryChoices();
        }
    }
</script>

<style scoped>

</style>
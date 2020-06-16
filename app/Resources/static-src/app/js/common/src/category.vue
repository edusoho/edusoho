<template>
    <div>
        <el-select v-model="category" v-on:change="updateCategory">
            <el-option value="0" :label="'course.base.category'|trans"></el-option>
            <el-option
                v-for="(label,value) in categoryChoices"
                :key="value"
                :label="label|trim"
                :value="value">
                {{ label }}
            </el-option>
        </el-select>
    </div>

</template>

<script>
    export default {
        name: "category",
        props: {
            type: 'course',
            category: '0',
        },
        filters: {
            trim: function (value) {
                return value.trim();
            }
        },
        methods: {
            getCategoryChoices() {
                let type = this.type ? this.type : 'course';
                this.$axios.get('/category/choices/' + type).then((response) => {
                    this.categoryChoices = response.data;
                });
            },
            updateCategory(value) {
                this.$emit('update:category', value);
            }
        },
        data() {
            this.getCategoryChoices();
            return {
                categoryChoices: {},
                category: '0',
            };
        }
    }
</script>

<style scoped>

</style>
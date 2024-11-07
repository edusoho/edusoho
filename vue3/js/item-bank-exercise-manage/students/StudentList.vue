<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {formatDate, getData, goto, open, trans} from '../../common';
import {computed, reactive, ref} from 'vue';
import Api from '../../../api';
import {message} from 'ant-design-vue';

const exerciseId = getData('student-list-app', 'exercise-id');
const enableAddAndRemove = getData('student-list-app', 'enable-add-and-remove');
const enableExport = getData('student-list-app', 'enable-export');

const tabs = [
  {
    key: 'students',
    text: '正式学员',
    link: `/item_bank_exercise/${exerciseId}/manage/students`,
  },
  {
    key: 'joinRecords',
    text: '加入记录',
    link: `/item_bank_exercise/${exerciseId}/manage/students/join/records`,
  },
  {
    key: 'exitRecords',
    text: '退出记录',
    link: `/item_bank_exercise/${exerciseId}/manage/students/exit/records`,
  },
];

const joinedChannelSelectOptions = [
  {
    value: 'buy_join',
    label: '购买加入',
  },
  {
    value: 'free_join',
    label: '免费加入',
  },
  {
    value: 'import_join',
    label: '教师添加',
  },
];

const learnDeadlineSelectOptions = [
  {
    value: 'deadlineAfter',
    label: '有效期内',
  },
  {
    value: 'deadlineBefore',
    label: '有效期外',
  },
];

const students = ref([]);
const followUsers = reactive({});
const table = {
  columns: [
    {
      key: 'user',
      title: '学员',
      width: 300,
    },
    {
      key: 'mobile',
      title: '手机号',
      width: 150,
    },
    {
      key: 'joinedChannel',
      title: '加入方式',
      width: 300,
    },
    {
      key: 'joinTime',
      title: '加入时间',
      width: 200,
    },
    {
      key: 'learnDeadline',
      title: '学习有效期',
      width: 200,
    },
    {
      key: 'operation',
      title: '操作',
      fixed: 'right',
      width: 182,
    },
  ],
  loading: false,
  rowSelection: reactive({
    hideDefaultSelections: true,
    onChange: (selectedRowKeys) => {
      table.rowSelection.selectedRowKeys = selectedRowKeys;
      table.isSelectAll = selectedRowKeys.length === students.value.length;
    },
    selectedRowKeys: [],
  }),
  isSelectAll: false,
  onSelectAllChange: () => {
    table.isSelectAll = !table.isSelectAll;
    if (table.isSelectAll) {
      table.rowSelection.selectedRowKeys = students.value.map(student => student.user.id);
    } else {
      table.rowSelection.selectedRowKeys = [];
    }
  },
};

const permissions = ref([]);
const fetchPermissions = async () => {
  permissions.value = await Api.me.getPermissions(['admin_v2', 'web']);
};
fetchPermissions();

const isAdmin = computed(() => {
  return permissions.value.includes('admin_v2');
});

const isTeacher = computed(() => {
  return permissions.value.includes('web');
});

const formState = reactive({
  joinDate: undefined,
  joinDateGreaterThan: undefined,
  joinDateLessThan: undefined,
  joinedChannel: undefined,
  learnDeadline: undefined,
  userKeyword: undefined,
});

const pagination = {
  current: 1,
  total: 0,
  pageSize: 10,
  showTotal: total => {
    return trans('question.bank.paper.pageTotal', {total});
  },
  onChange: (page, pageSize) => {
    pagination.current = page;
    pagination.pageSize = pageSize;
    fetchStudents();
  },
};

const fetchStudents = async () => {
  table.loading = true;
  const params = getFormParams();
  params.role = 'student';
  params.offset = (pagination.current - 1) * pagination.pageSize;
  params.limit = pagination.pageSize;
  const resp = await Api.itemBankExerciseMember.search(exerciseId, params);
  students.value = resp.data;
  pagination.total = resp.paging.total;
  table.loading = false;

  const followStatus = await Api.me.getFollowStatus(students.value.map(student => student.user.id));
  students.value.forEach((student, index) => {
    followUsers[student.user.id] = ['friend', 'following'].includes(followStatus[index]);
  });
};
fetchStudents();

const resetForm = () => {
  formState.joinDate = undefined;
  formState.joinDateGreaterThan = undefined;
  formState.joinDateLessThan = undefined;
  formState.joinedChannel = undefined;
  formState.learnDeadline = undefined;
  formState.userKeyword = undefined;
  fetchStudents();
};

function getFormParams() {
  const params = {
    startTimeGreaterThan: formState.joinDateGreaterThan,
    startTimeLessThan: formState.joinDateLessThan,
    joinedChannel: formState.joinedChannel,
    userKeyword: formState.userKeyword,
  };
  if (formState.learnDeadline) {
    params[formState.learnDeadline] = Math.round(Date.now() / 1000);
  }
  return params;
}

const onDateChange = (date, dateString) => {
  formState.joinDate = date;
  formState.joinDateGreaterThan = dateString[0] ? Date.parse(dateString[0] + ' 00:00:00') / 1000 : undefined;
  formState.joinDateLessThan = dateString[1] ? Date.parse(dateString[1] + ' 00:00:00') / 1000 + 86400 : undefined;
};

let exporting = false;
let totalCount = 0;
const $modal = $('#modal');
const onExport = async () => {
  if (exporting) {
    return;
  }
  exporting = true;
  const response = await Api.exportData.try('item-bank-exercise-students', {
    exerciseId,
  });
  if (response.success) {
    totalCount = response.counts.reduce((accumulator, currentValue) => accumulator + currentValue);
  } else {
    message.error(trans(response.message, response.parameters));
    return;
  }
  $modal.html($('#export-modal').html());
  $modal.modal({backdrop: 'static', keyboard: false});
  await exportData(0, '', '');

  exporting = false;
};

const exportData = async (start, fileName, name) => {
  const params = getFormParams();
  params.exerciseId = exerciseId;
  params.start = start;
  params.fileName = fileName;
  params.name = name;
  const response = await Api.exportData.pre('item-bank-exercise-students', params);
  if (!response.success) {
    message.error(trans(response.message));
    return;
  }
  if (response.status === 'finish') {
    if (!response.csvName) {
      $modal.modal('hide');
      message.warning('unexpected error, try again');
    }
    window.location.href = `/export/item-bank-exercise-students?fileNames[]=${response.csvName}`;
    $modal.find('#progress-bar').width('100%').parent().removeClass('active');
    setTimeout(() => {
      message.success($modal.find('.modal-title').data('success'));
      $modal.modal('hide');
    },500);
  } else {
    const progress = response.start / totalCount  * 100 + '%';
    $modal.find('#progress-bar').width(progress);
    await exportData(response.start, response.fileName, response.name);
  }
};

const onBatchRemove = async () => {
  if (table.rowSelection.selectedRowKeys.length === 0) {
    message.error(trans('course.manage.student.batch_remove.select_tips'));
    return;
  }
  if (!confirm(trans('course.manage.students_delete_hint'))) {
    return;
  }
  const result = await Api.itemBankExerciseMember.batchRemove(exerciseId, table.rowSelection.selectedRowKeys);
  if (result.success) {
    message.success(trans('member.delete_success_hint'));
    table.rowSelection.selectedRowKeys = [];
    await fetchStudents();
  } else {
    message.error(`${trans('member.delete_fail_hint')}:${result.message}`);
  }
};

const onBatchUpdateExpiryDate = async () => {
  if (table.rowSelection.selectedRowKeys.length === 0) {
    message.error(trans('course.manage.student.add_expiry_day.select_tips'));
    return;
  }
  const response = await fetch(`/item_bank_exercise/${exerciseId}/manage/student/deadline?` + new URLSearchParams({ids: table.rowSelection.selectedRowKeys}));
  const html = await response.text();
  $modal.html(html).modal('show');
};

const onUpdateAllExpiryDate = async () => {
  const response = await fetch(`/item_bank_exercise/${exerciseId}/manage/student/deadline?all=1`);
  const html = await response.text();
  $modal.html(html).modal('show');
};

const wholeMobiles = reactive({});

const mobile = computed(() => {
  return (userId, maskMobile) => {
    if (wholeMobiles[userId]) {
      return wholeMobiles[userId];
    }
    return maskMobile ? maskMobile : '-';
  };
});

const openEyeVisible = computed(() => {
  return (userId, mobile) => {
    if (mobile.length === 0) {
      return false;
    }
    return !!wholeMobiles[userId];
  };
});

const closeEyeVisible = computed(() => {
  return (userId, mobile) => {
    if (mobile.length === 0) {
      return false;
    }
    return !wholeMobiles[userId];
  };
});

const showWholeMobile = async (userId, encryptedMobile) => {
  const result = await Api.security.decryptMobile(encryptedMobile);
  wholeMobiles[userId] = result?.mobile;
};

const followOrUnfollow = async userId => {
  if (followUsers[userId]) {
    await Api.me.unfollow(userId);
    message.success('取消关注成功');
  } else {
    await Api.me.follow(userId);
    message.success('关注成功');
  }
  followUsers[userId] = !followUsers[userId];
};

const removeStudent = async userId => {
  if (!confirm(trans('course.manage.student_delete_hint'))) {
    return;
  }
  const result = await Api.itemBankExerciseMember.remove(exerciseId, userId);
  if (result.success) {
    message.success(trans('member.delete_success_hint'));
    table.rowSelection.selectedRowKeys = table.rowSelection.selectedRowKeys.filter(key => key !== userId);
    await fetchStudents();
  } else {
    message.error(`${trans('member.delete_fail_hint')}:${result.message}`);
  }
};
</script>

<template>
  <ant-config-provider>
    <div class="flex flex-col grow shrink-0 justify-start items-start self-stretch gap-20 bg-white min-h-800">
      <div class="flex w-full justify-between items-center">
        <span class="text-16 font-medium leading-24 text-black/[.88]">学员管理</span>
        <div v-if="enableAddAndRemove" class="flex items-center gap-20">
          <button class="student-header-operate-button"
                  data-toggle="modal"
                  data-target="#modal"
                  :data-url="`/item_bank_exercise/${exerciseId}/manage/students/add`"
          >
            <img class="w-16 h-16" src="../../../img/student-manage/add.png" alt="">
            <span>添加学员</span>
          </button>
          <button class="student-header-operate-button"
                  data-toggle="modal"
                  data-target="#modal"
                  data-backdrop="static"
                  data-keyboard="false"
                  :data-url="`/importer/exercise-member/index?exerciseId=${exerciseId}`"
          >
            <img class="w-16 h-16" src="../../../img/student-manage/import.png" alt="">
            <span>批量导入</span>
          </button>
        </div>
      </div>
      <div class="flex flex-col w-full items-start gap-20">
        <div class="flex items-start gap-32 self-stretch border-0 border-b border-solid border-b-[#F0F2F5]">
          <div v-for="tab in tabs" class="flex px-0 py-12 justify-center items-center cursor-pointer" :class="tab.key === 'students' ? 'border-0 border-b-2 border-solid border-b-[--primary-color]' : ''">
            <span class="text-[#1E2226] text-14 font-normal leading-22" :class="tab.key === 'students' ? 'text-[--primary-color] font-semibold' : ''" @click="goto(tab.link)">{{ tab.text }}</span>
          </div>
        </div>
        <div class="flex flex-col items-start self-stretch gap-20">
          <div class="flex flex-col items-start self-stretch gap-20 p-20 rounded-6 bg-[#FAFAFA]">
            <div class="flex items-center gap-20 self-stretch">
            <span class="shrink-0">
              加入时间：<a-range-picker
              class="rounded-6 border border-solid border-[#DFE2E6]"
              @change="onDateChange"
              :value="formState.joinDate"
            />
            </span>
              <a-select
                allowClear
                placeholder="加入方式"
                :options="joinedChannelSelectOptions"
                v-model:value="formState.joinedChannel"
              />
              <a-select
                allowClear
                placeholder="学习有效期"
                :options="learnDeadlineSelectOptions"
                v-model:value="formState.learnDeadline"
              />
              <a-input
                class="h-32 rounded-6 border border-solid border-[#E5E6EB]"
                placeholder="请输入用户名/邮箱/手机号"
                v-model:value="formState.userKeyword"
              />
            </div>
            <div class="flex items-center gap-20 self-stretch">
              <button class="flex py-0 px-15 justify-center items-center rounded-6 border border-solid border-[--primary-color] bg-white h-32" @click="fetchStudents">
                <span class="text-[--primary-color] text-14 font-normal leading-22">搜索</span>
              </button>
              <button class="flex py-0 px-15 justify-center items-center rounded-6 border border-solid border-[#E5E6EB] bg-white shadow-[0_2px_0_0_rgba(0, 0, 0, 0.02)] h-32" @click="resetForm">
                <span class="text-[#37393D] text-14 font-normal leading-22">重置</span>
              </button>
              <div v-if="enableExport" class="flex items-center gap-8 cursor-pointer" @click="onExport">
                <img class="w-16 h-16" src="../../../img/student-manage/export.png" alt="" style="filter: drop-shadow(1000px 0 0 var(--primary-color)); transform: translate(-1000px);">
                <span class="text-[--primary-color] text-14 font-normal leading-22">导出搜索结果</span>
              </div>
            </div>
          </div>
          <div v-show="students.length > 0" class="flex items-start gap-20">
            <button v-show="enableAddAndRemove" class="student-operate-button" @click="onBatchRemove">批量移除</button>
            <button class="student-operate-button" @click="onBatchUpdateExpiryDate">批量修改有效期</button>
            <button class="student-operate-button" @click="onUpdateAllExpiryDate">全员修改有效期</button>
          </div>
        </div>
        <div class="flex flex-col items-start gap-16 w-full">
          <a-table
            class="w-full"
            :columns="table.columns"
            :data-source="students"
            :row-key="student => student.user.id"
            :pagination="false"
            :loading="table.loading"
            :row-selection="{selectedRowKeys: table.rowSelection.selectedRowKeys, onChange: table.rowSelection.onChange}"
            :scroll="{ x: 1000, y: 300 }"
          >
            <template #headerCell="{ column }">
              <template v-if="column.key === 'learnDeadline'">
                <div class="flex items-center self-stretch gap-8 whitespace-nowrap">
                  学习有效期
                  <a-tooltip placement="top" title="取加入方式中学习有效期最长的生效和展示">
                    <img class="w-16 h-16" src="../../../img/tip.png" alt="">
                  </a-tooltip>
                </div>
              </template>
            </template>
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'user'">
                <div class="flex items-center gap-12 shrink-0">
                  <img :src="record.user.avatar.small" class="w-40 h-40 rounded-40 cursor-pointer" alt="" @click="open(`/user/${record.user.uuid}`)">
                  <a-tooltip placement="top" :overlayStyle="{ whiteSpace: 'normal' }">
                    <template #title>{{ record.user.nickname }}<span v-if="record.remark">{{ `(${record.remark})` }}</span></template>
                    <span class="text-[#1D2129] hover:text-[--primary-color] text-14 font-normal leading-22 cursor-pointer max-w-160 overflow-hidden text-ellipsis whitespace-nowrap" @click="open(`/user/${record.user.uuid}`)">
                      {{ record.user.nickname }}
                      <span v-if="record.remark" class="text-12 text-[#999999]">{{ `(${record.remark})` }}</span>
                    </span>
                  </a-tooltip>
                </div>
              </template>
              <template v-else-if="column.key === 'mobile'">
                <div class="flex gap-8 min-w-50">
                  <span class="text-[#37393D] text-14 font-normal leading-22">
                    {{ mobile(record.user.id, record.user.verifiedMobile) }}
                  </span>
                  <img v-show="openEyeVisible(record.user.id, record.user.verifiedMobile)" class="w-24 h-24" src="../../../img/open-eye.png" alt="">
                  <img v-show="closeEyeVisible(record.user.id, record.user.verifiedMobile)" @click="showWholeMobile(record.user.id, record.user.encryptedMobile)" class="w-24 h-24 cursor-pointer" src="../../../img/close-eye.png" alt="">
                </div>
              </template>
              <template v-else-if="column.key === 'joinedChannel'">
                <a-tooltip placement="top" :overlayStyle="{ whiteSpace: 'normal' }">
                  <template #title>{{ record.joinedChannelText }}</template>
                  <div class="text-[#37393D] text-14 font-normal leading-22 max-w-250 w-fit truncate">{{ record.joinedChannelText }}</div>
                </a-tooltip>
              </template>
              <template v-else-if="column.key === 'joinTime'">
                <div class="truncate">{{ formatDate(record.createdTime, 'YYYY-MM-DD HH:mm') }}</div>
              </template>
              <template v-else-if="column.key === 'learnDeadline'">
                <div class="min-w-100">
                  {{ record.deadline == 0 ? '长期有效' : formatDate(record.deadline, 'YYYY-MM-DD HH:mm') }}
                </div>
              </template>
              <template v-else-if="column.key === 'operation'">
                <div class="flex justify-end items-center gap-16 shrink-0">
                  <span v-if="record.user.canSendMessage" class="text-[--primary-color] text-14 font-normal leading-22 cursor-pointer" data-toggle="modal" data-target="#modal" :data-url="`/message/create/${record.user.id}`">发私信</span>
                  <span v-if="isAdmin" class="text-[--primary-color] text-14 font-normal leading-22 cursor-pointer" data-toggle="modal" data-target="#modal" :data-url="`/item_bank_exercise/${exerciseId}/manage/students/${record.user.id}/show`">查看资料</span>
                  <a-dropdown placement="bottomRight" trigger="['click']">
                    <span class="flex items-center cursor-pointer">
                      <img class="w-20 h-20" src="../../../img/more.png" alt="" style="filter: drop-shadow(1000px 0 0 var(--primary-color)); transform: translate(-1000px);">
                    </span>
                    <template #overlay>
                      <a-menu>
                        <a-menu-item data-toggle="modal" data-target="#modal" :data-url="`/item_bank_exercise/${exerciseId}/manage/student/${record.user.id}/remark`">
                          备注
                        </a-menu-item>
                        <a-menu-item @click="followOrUnfollow(record.user.id)">
                          {{ followUsers[record.user.id] ? '取消关注' : '关注' }}
                        </a-menu-item>
                        <a-menu-item data-toggle="modal" data-target="#modal" :data-url="`/item_bank_exercise/${exerciseId}/manage/student/deadline?ids=${record.user.id}`" :disabled="!isAdmin && !isTeacher">
                          修改有效期
                        </a-menu-item>
                        <a-menu-item @click="removeStudent(record.user.id)" :disabled="!enableAddAndRemove">
                          移除
                        </a-menu-item>
                      </a-menu>
                    </template>
                  </a-dropdown>
                </div>
              </template>
            </template>
          </a-table>
          <div class="flex justify-between items-center self-stretch pb-28 px-8 w-full">
            <div class="">
              <a-checkbox
                :indeterminate="table.rowSelection.selectedRowKeys.length > 0 && !table.isSelectAll"
                :checked="table.isSelectAll"
                :disabled="students.length === 0"
                @change="table.onSelectAllChange">
                <span class="text-[#37393D] text-14 font-normal leading-22">全选</span>
              </a-checkbox>
              <span class="text-[#37393D] text-14 font-normal leading-22">选择 {{ table.rowSelection.selectedRowKeys.length }} 项</span>
            </div>
            <a-pagination
              show-quick-jumper
              show-size-changer
              :show-total="pagination.showTotal"
              :total="pagination.total"
              v-model="pagination.current"
              @change="pagination.onChange"
            />
          </div>
        </div>
      </div>
    </div>
  </ant-config-provider>
</template>

<style lang="less">
.student-header-operate-button {
  display: flex;
  height: 32px;
  padding: 0 15px;
  justify-content: center;
  align-items: center;
  gap: 8px;
  border-radius: 6px;
  border: 1px solid var(--primary-color);
  background: var(--primary-color);

  span {
    color: #FFF;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 22px;
  }
}

.student-operate-button {
  display: flex;
  height: 32px;
  padding: 0 15px;
  justify-content: center;
  align-items: center;
  border-radius: 6px;
  border: 1px solid #E5E6EB;
  background: #FFF;
  box-shadow: 0 2px 0 0 rgba(0, 0, 0, 0.02);

  color: #37393D;
  font-size: 14px;
  font-style: normal;
  font-weight: 400;
  line-height: 22px;
}

.ant-message {
  left: 50%;
  transform: translateX(-50%)
}
</style>

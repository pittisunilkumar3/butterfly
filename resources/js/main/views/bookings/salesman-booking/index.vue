<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.salesman_bookings`)" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.bookings`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.salesman_bookings`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
                <a-space>
                    <a-button
                        v-if="
                            table.selectedRowKeys.length > 0 &&
                            (permsArray.includes('salesman_bookings_delete') ||
                                permsArray.includes('admin'))
                        "
                        type="primary"
                        @click="showSelectedDeleteConfirm"
                        danger
                    >
                        <template #icon><DeleteOutlined /></template>
                        {{ $t("common.delete") }}
                    </a-button>
                </a-space>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                <a-row :gutter="[16, 16]" justify="end">
                    <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="6">
                        <a-select
                            v-model:value="extraFilters.campaign_id"
                            :placeholder="
                                $t('common.select_default_text', [$t('lead.campaign')])
                            "
                            :allowClear="true"
                            style="width: 100%"
                            optionFilterProp="title"
                            show-search
                            @change="setUrlData"
                        >
                            <a-select-option
                                v-for="allCampaign in allCampaigns"
                                :key="allCampaign.xid"
                                :title="allCampaign.name"
                                :value="allCampaign.xid"
                            >
                                {{ allCampaign.name }}
                            </a-select-option>
                        </a-select>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="6">
                        <a-select
                            v-model:value="extraFilters.user_id"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('salesman.salesman'),
                                ])
                            "
                            :allowClear="true"
                            style="width: 100%"
                            optionFilterProp="title"
                            show-search
                            @change="setUrlData"
                        >
                            <a-select-option
                                v-for="allUsers in allUsers"
                                :key="allUsers.xid"
                                :title="allUsers.name"
                                :value="allUsers.xid"
                            >
                                {{ allUsers.name }}
                            </a-select-option>
                        </a-select>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="6">
                        <DateRangePicker
                            @dateTimeChanged="
                                (changedDateTime) => {
                                    extraFilters.dates = changedDateTime;
                                    setUrlData();
                                }
                            "
                        />
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <a-row class="mt-20">
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                disabled: false,
                                name: record.xid,
                            }),
                        }"
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'reference_number'">
                                <a-button
                                    type="link"
                                    class="p-0"
                                    @click="showViewDrawer(record)"
                                >
                                    {{
                                        record.reference_number != "" &&
                                        record.reference_number != undefined
                                            ? record.reference_number
                                            : "---"
                                    }}
                                </a-button>
                            </template>
                            <template v-if="column.dataIndex === 'date_time'">
                                {{ formatDateTime(record.salesman_booking.date_time) }}
                            </template>
                            <template v-if="column.dataIndex === 'campaign'">
                                {{
                                    record.campaign && record.campaign.name
                                        ? record.campaign.name
                                        : "-"
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'actioner'">
                                {{
                                    record.salesman_booking &&
                                    record.salesman_booking.user &&
                                    record.salesman_booking.user.name
                                        ? record.salesman_booking.user.name
                                        : "-"
                                }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-space v-if="record.x_last_action_by == user.xid">
                                    <a-tooltip :title="$t('lead.go_resume_call')">
                                        <a-button
                                            type="primary"
                                            @click="goAndResumeLead(record)"
                                        >
                                            <template #icon>
                                                <PlayCircleOutlined />
                                            </template>
                                        </a-button>
                                    </a-tooltip>
                                    <DeleteBooking
                                        bookingType="salesman_bookings"
                                        @success="setUrlData"
                                        :xLeadId="record.xid"
                                        :showDeleteText="false"
                                    />
                                </a-space>
                                <span v-else>
                                    <a-tooltip>
                                        <template #title>
                                            {{
                                                $t(
                                                    "salesman_booking.you_are_not_last_actioner"
                                                )
                                            }}
                                        </template>
                                        <InfoCircleOutlined />
                                    </a-tooltip>
                                </span>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </admin-page-table-content>

    <!-- Global Compaonent -->
    <view-lead-details
        :visible="isViewDrawerVisible"
        :lead="viewDrawerData"
        @close="hideViewDrawer"
    />
</template>

<script>
import { ref, createVNode, onMounted } from "vue";
import { Modal, notification } from "ant-design-vue";
import {
    PlayCircleOutlined,
    ExclamationCircleOutlined,
    InfoCircleOutlined,
    DeleteOutlined,
    PlusOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { forEach } from "lodash-es";
import { useStore } from "vuex";
import apiAdmin from "../../../../common/composable/apiAdmin";
import datatable from "../../../../common/composable/datatable";
import common from "../../../../common/composable/common";
import viewDrawer from "../../../../common/composable/viewDrawer";
import fields from "./fields";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import DeleteBooking from "../DeleteBooking.vue";

export default {
    components: {
        PlayCircleOutlined,
        InfoCircleOutlined,
        DeleteOutlined,
        PlusOutlined,
        AdminPageHeader,
        DateRangePicker,
        DeleteBooking,
    },
    setup() {
        const { permsArray, getCampaignUrl, user, formatDateTime } = common();
        const { url, columns, hashableColumns } = fields();
        const { t } = useI18n();
        const store = useStore();
        const router = useRouter();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const newTable = datatable();
        const extraFilters = ref({
            campaign_id: undefined,
            dates: [],
            user_id: undefined,
        });
        const allCampaigns = ref([]);
        const allUsers = ref([]);
        const leadDrawer = viewDrawer();

        onMounted(() => {
            const campaignsUrl = getCampaignUrl();
            const campaignsPromise = axiosAdmin.get(campaignsUrl);
            const usersPromise = axiosAdmin.get("all-users?log_type=salesman_bookings");

            Promise.all([campaignsPromise, usersPromise]).then(
                ([campaignsResponse, usersResponse]) => {
                    allCampaigns.value = campaignsResponse.data;
                    allUsers.value = usersResponse.data.users;

                    if (
                        permsArray.value.includes("leads_view_all") ||
                        permsArray.value.includes("admin")
                    ) {
                    }

                    newTable.hashable.value = [...hashableColumns];

                    setUrlData();
                }
            );
        });

        const setUrlData = () => {
            newTable.tableUrl.value = {
                url,
                extraFilters,
            };
            newTable.hashable.value = [...hashableColumns];

            newTable.table.pagination = { ...newTable.table.pagination, current: 1 };
            newTable.fetch({
                page: 1,
            });
        };

        const goAndResumeLead = (record) => {
            Modal.confirm({
                title: t("common.are_you_sure") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("lead.are_you_want_to_resume_this_lead"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    router.push({
                        name: "admin.call_manager.take_action",
                        params: {
                            id: record.xid,
                        },
                    });
                },
                onCancel() {},
            });
        };

        const showSelectedDeleteConfirm = () => {
            Modal.confirm({
                title: t("common.delete") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`salesman_booking.selected_delete_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    const allDeletePromise = [];
                    forEach(newTable.table.selectedRowKeys, (selectedRow) => {
                        allDeletePromise.push(
                            axiosAdmin.post(`salesman-bookings`, {
                                x_lead_id: selectedRow,
                                booking_type: "salesman_bookings",
                            })
                        );
                    });

                    Promise.all(allDeletePromise).then((successResponse) => {
                        // Update Visible Subscription Modules
                        store.dispatch("auth/updateVisibleSubscriptionModules");

                        newTable.fetch({
                            page: newTable.currentPage.value,
                        });

                        notification.success({
                            message: t("common.success"),
                            description: t(`salesman_booking.deleted`),
                            placement: "bottomRight",
                        });
                    });
                },
                onCancel() {},
            });
        };

        return {
            ...newTable,
            ...leadDrawer,
            url,
            columns,
            formatDateTime,
            hashableColumns,
            allCampaigns,
            allUsers,
            permsArray,
            extraFilters,
            user,

            setUrlData,
            goAndResumeLead,
            showSelectedDeleteConfirm,
        };
    },
};
</script>

<template>
    <a-row v-if="showCampaignStatus">
        <a-col :span="24">
            <a-tabs
                v-model:activeKey="extraFilters.campaign_status"
                @change="campaignTypeChanged"
            >
                <a-tab-pane key="active">
                    <template #tab>
                        <span>
                            <PlayCircleOutlined />
                            {{ $t("campaign.active_campaign") }}
                        </span>
                    </template>
                </a-tab-pane>

                <a-tab-pane
                    v-if="
                        permsArray.includes('view_completed_campaigns') ||
                        permsArray.includes('admin')
                    "
                    key="completed"
                >
                    <template #tab>
                        <span>
                            <StopOutlined />
                            {{ $t("campaign.completed_campaign") }}
                        </span>
                    </template>
                </a-tab-pane>
            </a-tabs>
        </a-col>
    </a-row>

    <a-row
        v-if="
            showTableSearch ||
            showDateFilter ||
            showUserFilter ||
            permsArray.includes('leads_view_all') ||
            permsArray.includes('admin')
        "
        :gutter="[15, 15]"
        class="mb-20"
    >
        <a-col v-if="showTableSearch" :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
            <a-select
                v-model:value="extraFilters.campaign_id"
                :placeholder="$t('common.select_default_text', [$t('lead.campaign')])"
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
        <a-col
            v-if="
                showUserFilter ||
                permsArray.includes('leads_view_all') ||
                permsArray.includes('admin')
            "
            :xs="24"
            :sm="24"
            :md="12"
            :lg="6"
            :xl="6"
        >
            <a-select
                v-model:value="filters.user_id"
                :placeholder="$t('common.select_default_text', [$t('user.user')])"
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
        <a-col
            v-if="
                showDateFilter ||
                permsArray.includes('leads_view_all') ||
                permsArray.includes('admin')
            "
            :xs="24"
            :sm="24"
            :md="8"
            :lg="8"
            :xl="8"
        >
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

    <a-row class="mt-20">
        <a-col :span="24">
            <div class="table-responsive" v-if="columns && columns.length > 0">
                <a-table
                    :columns="columns"
                    :row-key="(record) => record.xid"
                    :data-source="table.data"
                    :pagination="table.pagination"
                    :loading="table.loading"
                    @change="handleTableChange"
                    :scroll="scrollStyle"
                >
                    <template #bodyCell="{ column, record }">
                        <template
                            v-for="allFormFieldName in allFormFieldNames"
                            :key="allFormFieldName.xid"
                        >
                            <template
                                v-if="
                                    column.dataIndex ===
                                    convertStringToKey(allFormFieldName.field_name)
                                "
                            >
                                {{
                                    findFieldValue(
                                        allFormFieldName.similar_field_names,
                                        record.lead.lead_data
                                    )
                                }}
                            </template>
                        </template>
                        <template v-if="column.dataIndex === 'time_taken'">
                            {{ formatTimeDuration(record.time_taken) }}
                        </template>
                        <template v-if="column.dataIndex === 'date_time'">
                            {{ formatDateTime(record.date_time) }}
                        </template>
                        <template v-if="column.dataIndex === 'reference_number'">
                            <a-button
                                v-if="showLeadDetails"
                                type="link"
                                class="p-0"
                                @click="showViewDrawer(record.lead)"
                            >
                                {{
                                    record.lead.reference_number != "" &&
                                    record.lead.reference_number != undefined
                                        ? record.lead.reference_number
                                        : "---"
                                }}
                            </a-button>
                            <span v-else>{{ record.lead.reference_number }}</span>
                        </template>
                        <template v-if="column.dataIndex === 'campaign'">
                            {{
                                record.lead &&
                                record.lead.campaign &&
                                record.lead.campaign.name
                                    ? record.lead.campaign.name
                                    : "-"
                            }}
                        </template>
                        <template v-if="column.dataIndex === 'actioner'">
                            {{ record.user && record.user.name ? record.user.name : "-" }}
                        </template>
                        <template v-if="column.dataIndex == 'action'">
                            <slot :record="record" name="action"></slot>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>

    <!-- Global Compaonent -->
    <view-lead-details
        :visible="isViewDrawerVisible"
        :lead="viewDrawerData"
        @close="hideViewDrawer"
    />
</template>

<script>
import { onMounted, ref } from "vue";
import { PlayCircleOutlined, StopOutlined } from "@ant-design/icons-vue";
import { forEach } from "lodash-es";
import datatable from "../../../common/composable/datatable";
import common from "../../../common/composable/common";
import viewDrawer from "../../../common/composable/viewDrawer";
import fields from "./fields";
import DateRangePicker from "../../../common/components/common/calendar/DateRangePicker.vue";

export default {
    props: {
        pageName: {
            default: "index",
        },
        leadId: {
            default: undefined,
        },
        showCampaignStatus: {
            default: false,
        },
        showTableSearch: {
            default: true,
        },
        showUserFilter: {
            default: false,
        },
        showDateFilter: {
            default: true,
        },
        showLeadDetails: {
            default: true,
        },
        showFormFields: {
            default: true,
        },
        showAction: {
            default: true,
        },
        logType: {
            default: "call_log",
        },
        scrollStyle: {
            default: {},
        },
    },
    components: {
        PlayCircleOutlined,
        StopOutlined,

        DateRangePicker,
    },
    setup(props) {
        const {
            permsArray,
            appSetting,
            formatDateTime,
            convertStringToKey,
            getCampaignUrl,
            user,
            formatTimeDuration,
        } = common();
        const leadDrawer = viewDrawer();
        const newTable = datatable();
        const {
            url,
            columns,
            filterableColumns,
            allFormFieldNames,
            hashableColumns,
            allCampaigns,
            allUsers,
            getPrefetchData,
        } = fields(props);
        const extraFilters = ref({
            campaign_id: undefined,
            page_name: props.pageName,
            campaign_status: "active",
            lead_id: props.leadId != undefined ? props.leadId : undefined,
            log_type: props.logType,
            dates: [],
        });
        const filters = ref({
            log_type: props.logType,
            user_id: undefined,
        });

        onMounted(() => {
            getPrefetchData().then((response) => {
                if (
                    props.logType != "salesman_bookings" &&
                    props.pageName != "lead_action"
                ) {
                    filters.value = {
                        ...filters.value,
                        user_id: user.value.xid,
                    };
                }

                newTable.hashable.value = [...hashableColumns];

                setUrlData();
            });
        });

        const setUrlData = () => {
            newTable.tableUrl.value = {
                url,
                filters,
                extraFilters,
            };
            newTable.table.filterableColumns = filterableColumns;
            newTable.hashable.value = [...hashableColumns];

            newTable.table.pagination = { ...newTable.table.pagination, current: 1 };
            newTable.fetch({
                page: 1,
            });
        };

        const findFieldValue = (allowedFieldName, leadDatas) => {
            var resultString = "-";

            forEach(leadDatas, (leadData) => {
                if (allowedFieldName.includes(leadData.field_name)) {
                    resultString = leadData.field_value;
                }
            });

            return resultString;
        };

        const campaignTypeChanged = (value) => {
            extraFilters.value = {
                ...extraFilters.value,
                campaign_id: undefined,
            };

            const campaignsUrl = getCampaignUrl(extraFilters.value.campaign_status);
            const campaignsPromise = axiosAdmin.get(campaignsUrl);

            Promise.all([campaignsPromise]).then(([campaignsResponse]) => {
                allCampaigns.value = campaignsResponse.data;

                setUrlData();
            });
        };

        return {
            ...newTable,
            ...leadDrawer,
            permsArray,
            appSetting,
            formatDateTime,
            formatTimeDuration,
            columns,
            filterableColumns,
            allFormFieldNames,
            convertStringToKey,
            findFieldValue,
            allCampaigns,
            allUsers,
            filters,
            extraFilters,
            setUrlData,

            campaignTypeChanged,
        };
    },
};
</script>

<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.dashboard`)" style="padding: 0px" />
        </template>
    </AdminPageHeader>

    <div class="dashboard-page-content-container">
        <a-row :gutter="[8, 8]" class="mt-30 mb-10">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <DateRangePicker
                    ref="serachDateRangePicker"
                    @dateTimeChanged="
                        (changedDateTime) => (filters.dates = changedDateTime)
                    "
                />
            </a-col>
        </a-row>

        <div class="mt-30 mb-20">
            <a-row :gutter="[15, 15]">
                <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                    <StateWidget bgColor="#08979c">
                        <template #image>
                            <CopyrightCircleOutlined
                                style="color: #fff; font-size: 24px"
                            />
                        </template>
                        <template #description>
                            <h2 v-if="responseData.stateData">
                                {{ responseData.stateData.campaign_count }}
                            </h2>
                            <p>{{ $t("dashboard.active_campaigns") }}</p>
                        </template>
                    </StateWidget>
                </a-col>

                <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                    <StateWidget bgColor="#389e0d">
                        <template #image>
                            <ScheduleOutlined style="color: #fff; font-size: 24px" />
                        </template>
                        <template #description>
                            <h2 v-if="responseData.stateData">
                                {{ responseData.stateData.total_follow_ups }}
                            </h2>
                            <p>{{ $t("dashboard.total_follow_up") }}</p>
                        </template>
                    </StateWidget>
                </a-col>

                <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                    <StateWidget bgColor="#d46b08">
                        <template #image>
                            <MobileOutlined style="color: #fff; font-size: 24px" />
                        </template>
                        <template #description>
                            <h2 v-if="responseData.stateData">
                                {{ responseData.stateData.lead_count }}
                            </h2>
                            <p>{{ $t("dashboard.call_made") }}</p>
                        </template>
                    </StateWidget>
                </a-col>

                <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                    <StateWidget bgColor="#ffa39e">
                        <template #image>
                            <ClockCircleOutlined style="color: #fff; font-size: 24px" />
                        </template>
                        <template #description>
                            <h2 v-if="responseData.stateData">
                                <small>
                                    {{
                                        formatTimeDuration(
                                            responseData.stateData.total_times
                                        )
                                    }}
                                </small>
                            </h2>
                            <p>{{ $t("dashboard.total_duration") }}</p>
                        </template>
                    </StateWidget>
                </a-col>
            </a-row>
        </div>

        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <a-card :title="$t('dashboard.active_actioned_campaigns')">
                    <ActionedCampaigns :data="responseData" />
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
                <a-card :title="$t('dashboard.call_made')">
                    <CallMade :data="responseData" />
                    <template #extra>
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.leads.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
        </a-row>

        <a-row :gutter="[18, 18]" class="mt-30 mb-20">
            <a-col :xs="24" :sm="24" :md="12" :lg="16" :xl="16">
                <a-card
                    :title="$t('salesman_booking.salesman_booking')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="responseData && responseData.allAppointments"
                >
                    <SalesmanBooking :responseData="responseData" />
                    <template #extra>
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.bookings.salesman_bookings.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
                <a-card
                    :title="$t('lead_follow_up.follow_up')"
                    :bodyStyle="{ padding: '0px' }"
                    v-if="responseData && responseData.allFollowUps"
                >
                    <Followups :responseData="responseData" />
                    <template #extra>
                        <a-button
                            class="mt-10"
                            type="link"
                            @click="
                                $router.push({
                                    name: 'admin.bookings.lead_follow_up.index',
                                })
                            "
                        >
                            {{ $t("common.view_all") }}
                            <DoubleRightOutlined />
                        </a-button>
                    </template>
                </a-card>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { onMounted, reactive, ref, watch } from "vue";
import {
    CopyrightCircleOutlined,
    MobileOutlined,
    ScheduleOutlined,
    ClockCircleOutlined,
    DoubleRightOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import common from "../../common/composable/common";
import AdminPageHeader from "../../common/layouts/AdminPageHeader.vue";
import StateWidget from "../../common/components/common/card/StateWidget.vue";
import ActionedCampaigns from "../components/charts/dashboard/ActionedCampaigns.vue";
import CallMade from "../components/charts/dashboard/CallMade.vue";
import SalesmanBooking from "./dashboard/SalesmanBookings.vue";
import Followups from "./dashboard/Followups.vue";
import DateRangePicker from "../../common/components/common/calendar/DateRangePicker.vue";

export default {
    components: {
        AdminPageHeader,
        StateWidget,
        ActionedCampaigns,
        CallMade,
        SalesmanBooking,
        Followups,
        DateRangePicker,

        MobileOutlined,
        CopyrightCircleOutlined,
        ClockCircleOutlined,
        ScheduleOutlined,
        DoubleRightOutlined,
    },
    setup() {
        const { formatTimeDuration } = common();
        const { t } = useI18n();
        const responseData = ref([]);
        const filters = reactive({
            dates: [],
        });

        onMounted(() => {
            getInitData();
        });

        const getInitData = () => {
            const dashboardPromise = axiosAdmin.post("dashboard", filters);

            Promise.all([dashboardPromise]).then(([dashboardResponse]) => {
                responseData.value = dashboardResponse.data;
            });
        };

        watch([filters], (newVal, oldVal) => {
            getInitData();
        });

        return {
            formatTimeDuration,
            filters,
            responseData,
        };
    },
};
</script>

<style lang="less">
.ant-card-extra,
.ant-card-head-title {
    padding: 0px;
}

.ant-card-head-title {
    margin-top: 10px;
}
</style>

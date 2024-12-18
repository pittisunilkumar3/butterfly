<template>
    <div class="right-timeline-div">
        <perfect-scrollbar
            :options="{
                wheelSpeed: 1,
                swipeEasing: true,
                suppressScrollX: true,
            }"
        >
            <a-timeline>
                <a-timeline-item v-for="allLeadLog in allLeadLogs" :key="allLeadLog.xid">
                    <template #dot>
                        <PhoneOutlined v-if="allLeadLog.log_type == 'call_log'" />
                        <MailOutlined v-if="allLeadLog.log_type == 'email'" />
                        <FileTextOutlined v-else-if="allLeadLog.log_type == 'notes'" />
                        <ScheduleOutlined
                            v-else-if="allLeadLog.log_type == 'lead_follow_up'"
                        />
                        <ShoppingCartOutlined
                            v-else-if="allLeadLog.log_type == 'salesman_bookings'"
                        />
                    </template>
                    <span v-if="allLeadLog.log_type == 'call_log'">
                        {{
                            $t("lead.call_log_timeline_message", [
                                allLeadLog.user.name,
                                formatDateTime(allLeadLog.date_time),
                            ])
                        }}
                    </span>
                    <span v-else-if="allLeadLog.log_type == 'email'">
                        {{
                            $t("lead.email_timeline_message", [
                                allLeadLog.user.name,
                                formatDateTime(allLeadLog.date_time),
                            ])
                        }}
                    </span>
                    <span v-else-if="allLeadLog.log_type == 'notes'">
                        {{
                            $t("lead.notes_timeline_message", [
                                allLeadLog.user.name,
                                formatDateTime(allLeadLog.date_time),
                            ])
                        }}
                    </span>
                    <span v-else-if="allLeadLog.log_type == 'lead_follow_up'">
                        {{
                            $t("lead_follow_up.follow_up_timeline_message", [
                                allLeadLog.creator.name,
                                allLeadLog.user.name,
                                formatDateTime(allLeadLog.date_time),
                            ])
                        }}
                    </span>
                    <span v-else-if="allLeadLog.log_type == 'salesman_bookings'">
                        {{
                            $t("salesman_booking.salesman_bookings_timeline_message", [
                                allLeadLog.creator.name,
                                allLeadLog.user.name,
                                formatDateTime(allLeadLog.date_time),
                            ])
                        }}
                    </span>
                </a-timeline-item>
            </a-timeline>
        </perfect-scrollbar>
    </div>
    <div
        :style="{
            position: 'absolute',
            right: 0,
            bottom: 0,
            width: '100%',
            borderTop: '1px solid #e9e9e9',
            padding: '10px 16px',
            background: '#fff',
            zIndex: 1,
            textAlign: 'center',
        }"
    >
        <a-pagination v-model:current="currentPage" simple :total="totalRecords" />
    </div>
</template>

<script>
import { onMounted, ref, watch } from "vue";
import {
    FileTextOutlined,
    PhoneOutlined,
    ShoppingCartOutlined,
    ScheduleOutlined,
    MailOutlined,
} from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { emit } from "process";

export default {
    props: {
        leadId: {
            default: undefined,
        },
        refresh: {
            default: undefined,
        },
    },
    emits: ["dataFetched"],
    components: {
        FileTextOutlined,
        PhoneOutlined,
        ShoppingCartOutlined,
        ScheduleOutlined,
        MailOutlined,
    },
    setup(props, { emit }) {
        const { formatDateTime } = common();
        const url = `lead-logs?fields=id,xid,log_type,time_taken,date_time,user_id,x_user_id,user{id,xid,name},created_by_id,x_created_by_id,creator{id,xid,name},lead_id,x_lead_id,lead{id,xid,reference_number,lead_data,campaign_id,x_campaign_id},lead:campaign{id,xid,name}&log_type=all&page_name=lead_action&lead_id=${props.leadId}&order=id desc`;
        const allLeadLogs = ref([]);
        const currentPage = ref(1);
        const totalRecords = ref(0);
        const perPageRecords = ref(10);

        onMounted(() => {
            fetchInitData(1);
        });

        const fetchInitData = (pageNumber) => {
            const offset = (pageNumber - 1) * perPageRecords.value;
            var urlPath = `${url}&offset=${offset}&limit=${perPageRecords.value}`;

            axiosAdmin.get(urlPath).then((response) => {
                allLeadLogs.value = response.data;

                totalRecords.value = response.meta.paging.total;

                emit("dataFetched");
            });
        };

        watch(currentPage, (newValue, oldValue) => {
            fetchInitData(newValue);
        });

        watch(
            () => props.refresh,
            (newValue, oldValue) => {
                if (newValue == true) {
                    fetchInitData(1);
                }
            }
        );

        return {
            allLeadLogs,
            formatDateTime,

            totalRecords,
            currentPage,
            perPageRecords,
        };
    },
};
</script>

<style lang="less">
.right-timeline-div .ps {
    height: calc(100vh - 205px);
    padding: 5px;
    margin-top: 15px;
}
</style>

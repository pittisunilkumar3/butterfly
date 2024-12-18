<template>
    <a-tooltip :title="pageTitle">
        <a-button :type="btnType" :shape="btnShape" size="small" @click="showModal">
            <slot></slot>
        </a-button>
    </a-tooltip>

    <a-modal
        :open="visible"
        :closable="false"
        :centered="true"
        :title="pageTitle"
        @ok="onSubmit"
        :destroyOnClose="true"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="
                            bookingType == 'salesman_bookings'
                                ? $t('salesman.salesman')
                                : $t('user.staff_member')
                        "
                        name="user_id"
                        :help="rules.user_id ? rules.user_id.message : null"
                        :validateStatus="rules.user_id ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.user_id"
                            :placeholder="
                                $t('common.select_default_text', [
                                    bookingType == 'salesman_bookings'
                                        ? $t('salesman.salesman')
                                        : $t('user.staff_member'),
                                ])
                            "
                            :allowClear="true"
                            optionFilterProp="title"
                            show-search
                        >
                            <a-select-option
                                v-for="user in users"
                                :key="user.xid"
                                :value="user.xid"
                                :title="user.name"
                            >
                                {{ user.name }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="
                            bookingType == 'salesman_bookings'
                                ? $t('lead.booking_time')
                                : $t('lead_follow_up.follow_up_time')
                        "
                        name="date_time"
                        :help="rules.date_time ? rules.date_time.message : null"
                        :validateStatus="rules.date_time ? 'error' : null"
                        class="required"
                    >
                        <DateTimePicker
                            :dateTime="formData.date_time"
                            :isFutureDateDisabled="false"
                            @dateTimeChanged="
                                (changedDateTime) =>
                                    (formData.date_time = changedDateTime)
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.notes')"
                        name="notes"
                        :help="rules.notes ? rules.notes.message : null"
                        :validateStatus="rules.notes ? 'error' : null"
                        class="required"
                    >
                        <a-textarea
                            v-model:value="formData.notes"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('common.notes'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button key="submit" type="primary" :loading="loading" @click="onSubmit">
                <template #icon>
                    <SaveOutlined />
                </template>
                {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
            </a-button>
            <DeleteBooking
                v-if="
                    (leadDetails &&
                        leadDetails.xid &&
                        bookingType == 'salesman_bookings' &&
                        leadDetails.x_salesman_booking_id != undefined) ||
                    (leadDetails &&
                        leadDetails.xid &&
                        bookingType == 'lead_follow_up' &&
                        leadDetails.x_lead_follow_up_id != undefined)
                "
                :bookingType="bookingType"
                @success="
                    () => {
                        visible = false;
                        getInitData();
                    }
                "
                :xLeadId="leadDetails.xid"
            />
            <a-button key="back" @click="hideModal">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-modal>
</template>

<script>
import { defineComponent, ref, onMounted, createVNode, computed } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../../common/composable/apiAdmin";
import common from "../../../common/composable/common";
import DateTimePicker from "../../../common/components/common/calendar/DateTimePicker.vue";
import DeleteBooking from "./DeleteBooking.vue";

export default defineComponent({
    props: {
        leadId: {
            default: null,
        },
        bookingType: {
            default: null,
        },
        btnType: {
            default: "primary",
        },
        btnShape: {
            default: "rectangle",
        },
    },
    emits: ["success"],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DeleteOutlined,

        DateTimePicker,
        DeleteBooking,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const addEditType = ref("add");
        const formData = ref({
            lead_id: props.leadId,
            booking_type: props.bookingType,
            date_time: undefined,
            user_id: undefined,
            notes: "",
        });
        const visible = ref(false);
        const pageTitle = ref("");
        const leadDetails = ref({});
        const users = ref([]);
        const leadDetailsUrl =
            props.bookingType == "salesman_bookings"
                ? `leads/${props.leadId}?fields=id,xid,salesman_booking_id,x_salesman_booking_id,salesmanBooking{id,xid,log_type,user_id,x_user_id,date_time,notes},salesmanBooking:user{id,xid,name}`
                : `leads/${props.leadId}?fields=id,xid,lead_follow_up_id,x_lead_follow_up_id,leadFollowUp{id,xid,log_type,user_id,x_user_id,date_time,notes},leadFollowUp:user{id,xid,name}`;

        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { appSetting } = common();
        const langKey = computed(() => {
            return props.bookingType == "salesman_bookings"
                ? "salesman_booking"
                : "lead_follow_up";
        });

        onMounted(() => {
            pageTitle.value =
                props.bookingType == "salesman_bookings"
                    ? t("menu.salesman_bookings")
                    : t("menu.lead_follow_up");

            getInitData();
        });

        const getInitData = () => {
            const leadDetailsPromise = axiosAdmin.get(leadDetailsUrl);
            const usersPromise = axiosAdmin.get(
                `leads/campaign-members?lead_id=${props.leadId}&booking_type=${props.bookingType}`
            );

            Promise.all([leadDetailsPromise, usersPromise]).then(
                ([leadDetailssResponse, usersResponse]) => {
                    var responseData = leadDetailssResponse.data;

                    if (
                        props.bookingType == "salesman_bookings" &&
                        responseData.salesman_booking &&
                        responseData.salesman_booking.xid
                    ) {
                        formData.value = {
                            ...formData.value,
                            date_time: responseData.salesman_booking.date_time,
                            user_id: responseData.salesman_booking.x_user_id,
                            notes: responseData.salesman_booking.notes,
                        };

                        addEditType.value = "edit";
                    } else if (
                        props.bookingType == "lead_follow_up" &&
                        responseData.lead_follow_up &&
                        responseData.lead_follow_up.xid
                    ) {
                        formData.value = {
                            ...formData.value,
                            date_time: responseData.lead_follow_up.date_time,
                            user_id: responseData.lead_follow_up.x_user_id,
                            notes: responseData.lead_follow_up.notes,
                        };

                        addEditType.value = "edit";
                    } else {
                        formData.value = {
                            ...formData.value,
                            date_time: undefined,
                            user_id: undefined,
                            notes: "",
                        };
                    }

                    leadDetails.value = responseData;
                    users.value = usersResponse.data.users;
                }
            );
        };

        const onSubmit = () => {
            const langSuffix = addEditType.value == "add" ? "created" : "updated";

            addEditRequestAdmin({
                url: "leads/create-booking",
                data: formData.value,
                successMessage: t(`${langKey.value}.${langSuffix}`),
                success: (res) => {
                    emit("success", res.booking);
                    hideModal();
                    getInitData();
                },
            });
        };

        const showModal = () => {
            visible.value = true;
        };

        const hideModal = () => {
            visible.value = false;
        };

        return {
            loading,
            rules,
            onSubmit,
            appSetting,

            formData,
            addEditType,
            pageTitle,
            hideModal,
            showModal,
            visible,
            users,

            leadDetails,
            getInitData,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

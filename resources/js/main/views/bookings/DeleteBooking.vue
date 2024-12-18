<template>
    <a-tooltip :title="pageTitle">
        <a-button
            :style="{
                background: '#ff4d4f',
                borderColor: '#ff4d4f',
            }"
            type="primary"
            @click="deleteModal"
            :loading="loading"
        >
            <template v-if="showDeleteIcon" #icon>
                <DeleteOutlined />
            </template>
            <template v-if="showDeleteText">{{ $t("common.delete") }}</template>
        </a-button>
    </a-tooltip>
</template>
<script>
import { defineComponent, ref, onMounted, createVNode } from "vue";
import { ExclamationCircleOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { Modal, notification } from "ant-design-vue";
import apiAdmin from "../../../common/composable/apiAdmin";

export default defineComponent({
    props: {
        bookingType: {
            default: null,
        },
        xLeadId: {
            default: null,
        },
        showDeleteIcon: {
            default: true,
        },
        showDeleteText: {
            default: true,
        },
    },
    emits: ["success"],
    components: {
        DeleteOutlined,
        ExclamationCircleOutlined,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const pageTitle = ref("");
        const { addEditRequestAdmin, loading } = apiAdmin();

        onMounted(() => {
            pageTitle.value =
                props.bookingType == "salesman_bookings"
                    ? t("salesman_booking.delete_salesman_booking")
                    : t("lead_follow_up.delete_follow_up");
        });

        const deleteModal = () => {
            const deleteUrl =
                props.bookingType == "salesman_bookings"
                    ? "salesman-bookings"
                    : "lead-follow-ups/delete";
            const langTermKey =
                props.bookingType == "salesman_bookings"
                    ? "salesman_booking"
                    : "lead_follow_up";

            Modal.confirm({
                title: t("common.delete") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t(`${langTermKey}.delete_message`),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    addEditRequestAdmin({
                        url: deleteUrl,
                        data: {
                            x_lead_id: props.xLeadId,
                            booking_type: props.bookingType,
                        },
                        success: (res) => {
                            emit("success");

                            notification.success({
                                message: t("common.success"),
                                description: t(`${langTermKey}.deleted`),
                                placement: "bottomRight",
                            });
                        },
                    });
                },
                onCancel() {},
            });
        };

        return {
            pageTitle,
            loading,
            deleteModal,
        };
    },
});
</script>

<template>
    <a-modal
        :open="visible"
        :maskClosable="false"
        :closable="false"
        :footer="null"
        centered
    >
        <a-result>
            <template #title>
                <span :style="{ color: '#7676e3' }">
                    {{ $t("lead.skip_or_delete") }}
                </span>
            </template>
            <template #subTitle>
                {{ $t("lead.skip_delete_message") }}
            </template>
            <template #icon>
                <InfoCircleOutlined />
            </template>
            <template #extra>
                <a-row :gutter="[10, 10]">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-popconfirm
                            :title="$t('lead.come_back_confirm_message')"
                            :okText="$t('common.yes')"
                            :cancelText="$t('common.no')"
                            @confirm="onSkip"
                        >
                            <a-button
                                type="primary"
                                :style="{ backgroundColor: '#52c41a', border: '#52c41a' }"
                                block
                            >
                                <LogoutOutlined />
                                {{ $t("lead.combe_back_later") }}
                            </a-button>
                        </a-popconfirm>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-popconfirm
                            :title="$t('lead.skip_delete_message')"
                            :okText="$t('common.yes')"
                            :cancelText="$t('common.no')"
                            @confirm="skipAndDelete"
                        >
                            <a-button
                                type="primary"
                                :style="{ backgroundColor: '#ff4d4f', border: '#ff4d4f' }"
                                :loading="loading"
                                block
                            >
                                <template #icon>
                                    <DeleteOutlined />
                                </template>
                                {{ $t("lead.skip_and_delete") }}
                            </a-button>
                        </a-popconfirm>
                    </a-col>
                </a-row>
                <a-row class="mt-10">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-button
                            @click="closeModal"
                            type="primary"
                            :style="{ backgroundColor: '#faad14', border: '#faad14' }"
                            block
                        >
                            <CloseOutlined />
                            {{ $t("common.cancel") }}
                        </a-button>
                    </a-col>
                </a-row>
            </template>
        </a-result>
    </a-modal>
</template>

<script>
import { onMounted, ref, createVNode, watch } from "vue";
import {
    CloseOutlined,
    LogoutOutlined,
    DeleteOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import { notification } from "ant-design-vue";
import apiAdmin from "../../../common/composable/apiAdmin";

export default {
    props: ["visible", "leadId"],
    emits: ["close", "onSkipDelete", "onSkip"],
    components: {
        InfoCircleOutlined,
        DeleteOutlined,
        LogoutOutlined,
        CloseOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        const skipAndDelete = () => {
            addEditRequestAdmin({
                url: `campaigns/skip-delete-lead`,
                data: {
                    lead_id: props.leadId,
                },
                success: (res) => {
                    emit("onSkipDelete", res.lead);

                    notification.success({
                        message: t("common.success"),
                        description: t("lead.skip_delete_success_message"),
                        placement: "bottomRight",
                    });
                },
            });
        };

        const closeModal = () => {
            emit("close");
        };

        const onSkip = () => {
            emit("onSkip");
        };

        return {
            loading,

            closeModal,
            skipAndDelete,
            onSkip,
        };
    },
};
</script>

<style></style>

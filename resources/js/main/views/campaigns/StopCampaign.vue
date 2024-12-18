<template>
    <div v-if="permsArray.includes('stop_campaign') || permsArray.includes('admin')">
        <a-button :type="btnType" :class="btnClass" @click="showConfirm" :danger="danger">
            <template #icon>
                <slot name="icon"></slot>
            </template>
            <slot></slot>
        </a-button>

        <a-modal v-model:open="visible" :closable="false" :maskClosable="false">
            <a-comment>
                <a-alert message="Error Text" type="error" />

                <template #avatar>
                    <InfoCircleOutlined :style="{ fontSize: '32px', color: '#faad14' }" />
                </template>
                <template #content>
                    <p>
                        We supply a series of design principles, practical patterns and
                        high quality design resources (Sketch and Axure), to help people
                        create their product prototypes beautifully and efficiently.
                    </p>
                </template>
            </a-comment>
        </a-modal>
    </div>
</template>
<script>
import { defineComponent, ref, onMounted } from "vue";
import { InfoCircleOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";

export default defineComponent({
    props: {
        btnType: {
            default: "default",
        },
        btnClass: {
            default: "",
        },
        tooltip: {
            default: true,
        },
        campaign: {
            default: {},
        },
        visible: {
            default: false,
        },
        danger: {
            default: false,
        },
    },
    emits: ["success"],
    components: {
        InfoCircleOutlined,
    },
    setup(props, { emit }) {
        const { permsArray } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const visible = ref(false);
        const { t } = useI18n();

        const showConfirm = () => {
            visible.value = true;
        };

        const onSubmit = () => {
            addEditRequestAdmin({
                url: "leads/create-lead",
                data: formData.value,
                successMessage: t("lead.created"),
                success: (res) => {
                    emit("success");
                    onClose();
                },
            });
        };

        const onClose = () => {
            visible.value = false;
        };

        return {
            permsArray,
            visible,
            loading,
            rules,

            onSubmit,
            onClose,
            showConfirm,
        };
    },
});
</script>

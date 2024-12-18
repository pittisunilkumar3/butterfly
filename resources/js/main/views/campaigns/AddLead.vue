<template>
    <div v-if="permsArray.includes('leads_create') || permsArray.includes('admin')">
        <a-tooltip :title="$t('lead.add')">
            <a-button :type="btnType" :class="btnClass" @click="showAdd">
                <template #icon>
                    <slot name="icon"></slot>
                </template>
                <slot></slot>
            </a-button>
        </a-tooltip>

        <a-drawer
            :title="$t('lead.add')"
            :width="drawerWidth"
            :open="visible"
            :body-style="{ paddingBottom: '80px' }"
            :footer-style="{ textAlign: 'right' }"
            :maskClosable="false"
            @close="onClose"
        >
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col
                        v-for="leadData in formData.lead_data"
                        :key="leadData.id"
                        :xs="24"
                        :sm="24"
                        :md="12"
                        :lg="12"
                    >
                        <a-form-item
                            :label="leadData.field_name"
                            :name="leadData.field_name"
                            :help="rules.name ? rules.name.message : null"
                            :validateStatus="rules.name ? 'error' : null"
                        >
                            <a-input
                                v-model:value="leadData.field_value"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        leadData.field_name,
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
            <template #footer>
                <a-space>
                    <a-button
                        key="submit"
                        type="primary"
                        :loading="loading"
                        @click="onSubmit"
                    >
                        <template #icon>
                            <SaveOutlined />
                        </template>
                        {{ $t("common.create") }}
                    </a-button>
                    <a-button key="back" @click="onClose">
                        {{ $t("common.cancel") }}
                    </a-button>
                </a-space>
            </template>
        </a-drawer>
    </div>
</template>
<script>
import { defineComponent, ref, onMounted } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import { forEach } from "lodash-es";
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
    },
    emits: ["success"],
    components: {
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { permsArray } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const visible = ref(false);
        const formData = ref({});
        const { t } = useI18n();

        onMounted(() => {
            resetFormData();
        });

        const resetFormData = () => {
            var newLeadDataArray = [];

            if (
                props.campaign &&
                props.campaign.form &&
                props.campaign.form.form_fields
            ) {
                forEach(props.campaign.form.form_fields, (fieldValue) => {
                    newLeadDataArray.push({
                        id: Math.random().toString(36).slice(2),
                        field_name: fieldValue.name,
                        field_value: "",
                    });
                });
            }

            formData.value = {
                campaign_id: props.campaign.xid,
                lead_data: newLeadDataArray,
            };
        };

        const showAdd = () => {
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
            resetFormData();
            visible.value = false;
        };

        return {
            permsArray,
            visible,
            formData,
            loading,
            rules,

            onSubmit,
            onClose,
            showAdd,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

<template>
    <a-modal
        :open="visible"
        :closable="false"
        :centered="true"
        :title="pageTitle"
        @ok="onSubmit"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('form_field_name.field_name')"
                        name="field_name"
                        :help="rules.field_name ? rules.field_name.message : null"
                        :validateStatus="rules.field_name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.field_name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('form_field_name.field_name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-typography-title :level="5" :style="{ marginBottom: '10px' }">
                        {{ $t("form_field_name.similar_field_names") }}
                    </a-typography-title>
                    <DynamicFields
                        v-if="formData.similar_field_names"
                        :data="formData.similar_field_names"
                        :addText="$t('form_field_name.add_field')"
                        @onEntry="(allTags) => (formData.similar_field_names = allTags)"
                    />
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('form_field_name.visible')"
                        name="visible"
                        :help="rules.visible ? rules.visible.message : null"
                        :validatevisible="rules.visible ? 'error' : null"
                    >
                        <a-radio-group
                            v-model:value="formData.visible"
                            button-style="solid"
                            size="small"
                        >
                            <a-radio-button :value="1">
                                {{ $t("common.yes") }}
                            </a-radio-button>
                            <a-radio-button :value="0">
                                {{ $t("common.no") }}
                            </a-radio-button>
                        </a-radio-group>
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
            <a-button key="back" @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-modal>
</template>
<script>
import { defineComponent, ref, onMounted, computed } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import DynamicFields from "./DynamicFields.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,

        DynamicFields,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

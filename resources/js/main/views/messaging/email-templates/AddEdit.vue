<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="16" :lg="16">
                    <a-form-item
                        :label="$t('email_template.name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('email_template.name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                    <a-form-item
                        name="sharable"
                        :help="rules.sharable ? rules.sharable.message : null"
                        :validateStatus="rules.sharable ? 'error' : null"
                    >
                        <template #label>
                            <a-space>
                                {{ $t("email_template.sharable") }}
                                <a-tooltip>
                                    <template #title>
                                        {{ $t("email_template.sharable_message") }}
                                    </template>
                                    <InfoCircleOutlined />
                                </a-tooltip>
                            </a-space>
                        </template>
                        <a-radio-group
                            v-model:value="formData.sharable"
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
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('email_template.subject')"
                        name="subject"
                        :help="rules.subject ? rules.subject.message : null"
                        :validateStatus="rules.subject ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.subject"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('email_template.subject'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('email_template.body')"
                        name="body"
                        :help="rules.body ? rules.body.message : null"
                        :validateStatus="rules.body ? 'error' : null"
                    >
                        <QuillEditor
                            ref="textEditor"
                            v-model:content="formData.body"
                            :content="formData.body"
                            contentType="html"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('email_template.body'),
                                ])
                            "
                            style="height: 200px"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :span="24">
                    <a-form-item :label="$t('form.form')" name="form_id">
                        <span style="display: flex">
                            <a-select
                                v-model:value="selectedFormId"
                                :placeholder="
                                    $t('common.select_default_text', [$t('form.form')])
                                "
                                :allowClear="true"
                                optionFilterProp="label"
                                show-search
                                @change="formSelected"
                            >
                                <a-select-option
                                    v-for="allForm in allForms"
                                    :key="allForm.xid"
                                    :value="allForm.xid"
                                    :label="allForm.name"
                                    :form="allForm"
                                >
                                    {{ allForm.name }}
                                </a-select-option>
                            </a-select>
                            <FormAddButton @onAddSuccess="formAdded" />
                        </span>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row
                :gutter="16"
                v-if="
                    selectedForm &&
                    selectedForm.form_fields &&
                    selectedForm.form_fields.length > 0
                "
                class="mb-20"
            >
                <a-col
                    :xs="8"
                    :sm="8"
                    :md="6"
                    :lg="4"
                    v-for="selectedFormField in selectedForm.form_fields"
                    :key="selectedFormField.id"
                >
                    <a-button
                        @click="insertFormText(selectedFormField.name)"
                        type="link"
                        style="padding: 0px"
                    >
                        {{ selectedFormField.name }}
                    </a-button>
                </a-col>
            </a-row>

            <a-row :gutter="16" class="mt-10">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('email_template.status')"
                        name="status"
                        :help="rules.status ? rules.status.message : null"
                        :validateStatus="rules.status ? 'error' : null"
                    >
                        <a-radio-group
                            v-model:value="formData.status"
                            button-style="solid"
                            size="small"
                        >
                            <a-radio-button :value="1">
                                {{ $t("common.active") }}
                            </a-radio-button>
                            <a-radio-button :value="0">
                                {{ $t("common.inactive") }}
                            </a-radio-button>
                        </a-radio-group>
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
                    {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
                </a-button>
                <a-button key="back" @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, watch, computed, onMounted } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import apiAdmin from "../../../../common/composable/apiAdmin";
import FormAddButton from "../../forms/forms/AddButton.vue";

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
        InfoCircleOutlined,
        QuillEditor,
        FormAddButton,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const formUrl = "forms/all";
        const textEditor = ref(null);
        const allForms = ref([]);
        const selectedFormId = ref(undefined);
        const selectedForm = ref({});

        onMounted(() => {
            const formsPromise = axiosAdmin.get(formUrl);

            Promise.all([formsPromise]).then(([formsResponse]) => {
                allForms.value = formsResponse.data.data;
            });
        });

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

        const insertFormText = (mergeFieldText) => {
            const selectedPointArray = editor.value.getSelection(true);
            var newFieldTextValue =
                selectedPointArray.length > 0
                    ? `##${mergeFieldText}##`
                    : ` ##${mergeFieldText}## `;

            editor.value.deleteText(selectedPointArray.index, selectedPointArray.length);
            editor.value.insertText(selectedPointArray.index, newFieldTextValue);
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const editor = computed(() => {
            return textEditor.value.getQuill();
        });

        const formAdded = () => {
            axiosAdmin.get(formUrl).then((response) => {
                allForms.value = response.data.data;
            });
        };

        const formSelected = (value, option) => {
            selectedForm.value = option.form;
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal == true && textEditor.value) {
                    if (props.addEditType == "edit") {
                        textEditor.value.setHTML(props.formData.body);
                    } else {
                        textEditor.value.setHTML("");
                    }
                }

                selectedFormId.value = undefined;
                selectedForm.value = {};
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,

            insertFormText,
            textEditor,
            allForms,
            selectedFormId,
            formAdded,
            formSelected,
            selectedForm,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

<template>
    <a-tooltip :title="$t('lead.send_email')">
        <a-button type="primary" @click="sendEmail">
            <template #icon>
                <MailOutlined />
            </template>
        </a-button>
    </a-tooltip>

    <a-drawer
        :title="$t('lead.send_email')"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="hideModal"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('campaign.email_template')"
                        name="email_template_id"
                        :help="
                            rules.email_template_id
                                ? rules.email_template_id.message
                                : null
                        "
                        :validateStatus="rules.email_template_id ? 'error' : null"
                        class="required"
                    >
                        <span style="display: flex">
                            <a-select
                                v-model:value="formData.email_template_id"
                                :placeholder="
                                    $t('common.select_default_text', [
                                        $t('campaign.email_template'),
                                    ])
                                "
                                @change="emailTemplateChanged"
                            >
                                <a-select-option
                                    v-for="allEmailTemplate in allEmailTemplates"
                                    :key="allEmailTemplate.xid"
                                    :value="allEmailTemplate.xid"
                                >
                                    {{ allEmailTemplate.name }}
                                </a-select-option>
                            </a-select>
                            <EmailTemplateAddButton @onAddSuccess="emailTemplateAdded" />
                        </span>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.email')"
                        name="email"
                        :help="rules.email ? rules.email.message : null"
                        :validateStatus="rules.email ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.email"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('common.email'),
                                ])
                            "
                        />
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
                        :label="$t('email_template.message')"
                        name="message"
                        :help="rules.message ? rules.message.message : null"
                        :validateStatus="rules.message ? 'error' : null"
                        class="required"
                    >
                        <QuillEditor
                            ref="textEditor"
                            v-model:content="formData.message"
                            :content="formData.message"
                            contentType="html"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('email_template.message'),
                                ])
                            "
                            style="height: 200px"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row
                :gutter="16"
                v-if="
                    lead &&
                    lead.campaign &&
                    lead.campaign.form &&
                    lead.campaign.form.form_fields &&
                    lead.campaign.form.form_fields.length > 0
                "
                class="mb-20"
            >
                <a-col
                    :xs="8"
                    :sm="8"
                    :md="6"
                    :lg="4"
                    v-for="selectedFormField in lead.campaign.form.form_fields"
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
                        <SendOutlined />
                    </template>
                    {{ $t("common.send") }}
                </a-button>
                <a-button key="back" @click="hideModal">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, onMounted, computed } from "vue";
import { SendOutlined, MailOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { notification } from "ant-design-vue";
import { find, forEach, replace } from "lodash-es";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import apiAdmin from "../../../common/composable/apiAdmin";
import EmailTemplateAddButton from "../messaging/email-templates/AddButton.vue";

export default defineComponent({
    props: {
        lead: {
            default: {},
        },
        leadFormData: {
            default: {},
        },
        email: {
            default: null,
        },
    },
    emits: ["success"],
    components: {
        SendOutlined,
        MailOutlined,

        EmailTemplateAddButton,
        QuillEditor,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const visible = ref(false);
        const formData = ref({
            lead_id: props.lead.xid,
            email_template_id: undefined,
            email: props.email,
            subject: "",
            message: "",
        });
        const emailTemplateUrl = "email-templates/all";
        const allEmailTemplates = ref([]);
        const textEditor = ref(null);

        onMounted(() => {
            const emailTemplatesPromise = axiosAdmin.get(emailTemplateUrl);

            Promise.all([emailTemplatesPromise]).then(([emailTemplatesResponse]) => {
                allEmailTemplates.value = emailTemplatesResponse.data.email_templates;

                resetToCampaignEmailTemplate();
                emailTemplateChanged();
            });
        });

        const editor = computed(() => {
            return textEditor.value.getQuill();
        });

        const insertFormText = (mergeFieldText) => {
            const selectedPointArray = editor.value.getSelection(true);

            var leadDataInleadDataField = find(props.leadFormData.lead_data, [
                "field_name",
                mergeFieldText,
            ]);

            if (
                leadDataInleadDataField != undefined &&
                leadDataInleadDataField.field_value != undefined &&
                leadDataInleadDataField.field_value != ""
            ) {
                var newFieldTextValue =
                    selectedPointArray.length > 0
                        ? `${leadDataInleadDataField.field_value}`
                        : `${leadDataInleadDataField.field_value}`;
            } else {
                var newFieldTextValue =
                    selectedPointArray.length > 0
                        ? `##${insertedTextField}##`
                        : `##${insertedTextField}##`;
            }

            editor.value.deleteText(selectedPointArray.index, selectedPointArray.length);
            editor.value.insertText(selectedPointArray.index, newFieldTextValue);
        };

        const resetToCampaignEmailTemplate = () => {
            if (
                props.lead &&
                props.lead.campaign &&
                props.lead.campaign.email_template &&
                props.lead.campaign.email_template.xid
            ) {
                formData.value = {
                    ...formData.value,
                    email_template_id: props.lead.campaign.email_template.xid,
                };
            }
        };

        const emailTemplateChanged = () => {
            const selectedEmailTemplate = find(allEmailTemplates.value, [
                "xid",
                formData.value.email_template_id,
            ]);

            if (selectedEmailTemplate && selectedEmailTemplate.body) {
                var bodyMessage = selectedEmailTemplate.body;

                forEach(props.leadFormData.lead_data, (leadDataValue, leadDataKey) => {
                    if (
                        leadDataValue.field_value != undefined &&
                        leadDataValue.field_value != ""
                    ) {
                        bodyMessage = replace(
                            bodyMessage,
                            `##${leadDataValue.field_name}##`,
                            leadDataValue.field_value
                        );
                    }
                });

                formData.value = {
                    ...formData.value,
                    message: bodyMessage,
                    subject: selectedEmailTemplate.subject,
                };

                // Not execute at onMounted
                if (textEditor.value != undefined) {
                    textEditor.value.setHTML(bodyMessage);
                }
            }
        };

        const emailTemplateAdded = () => {
            axiosAdmin.get(emailTemplateUrl).then((response) => {
                allEmailTemplates.value = response.data.email_templates;
            });
        };

        const sendEmail = () => {
            formData.value = {
                ...formData.value,
                email: props.email,
            };

            emailTemplateChanged();

            visible.value = true;
        };

        const onSubmit = () => {
            addEditRequestAdmin({
                url: "leads/send-email",
                data: formData.value,
                success: (res) => {
                    if (res && res.success) {
                        visible.value = false;
                        notification.success({
                            message: t("common.success"),
                            description: t("email_template.mail_send_successfully"),
                            placement: "bottomRight",
                        });
                        emit("success");
                    } else {
                        notification.error({
                            message: t("common.error"),
                            description: t("email_template.mail_send_error_message"),
                            placement: "bottomRight",
                        });
                    }
                },
            });
        };

        const hideModal = () => {
            visible.value = false;
            loading.value = false;
            rules.value = {};

            resetToCampaignEmailTemplate();
        };

        return {
            loading,
            visible,
            rules,
            formData,

            sendEmail,
            onSubmit,
            hideModal,

            textEditor,
            allEmailTemplates,
            emailTemplateAdded,
            emailTemplateChanged,
            insertFormText,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

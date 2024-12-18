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
            <a-steps :current="currentStep" type="navigation">
                <a-step
                    :title="$t('campaign.basic_settings')"
                    :description="$t('campaign.basic_settings_description')"
                >
                    <template #icon>
                        <FileTextOutlined />
                    </template>
                </a-step>
                <a-step
                    :title="$t('campaign.about_campaign')"
                    :description="$t('campaign.about_campaign_description')"
                >
                    <template #icon>
                        <OrderedListOutlined />
                    </template>
                </a-step>
                <a-step
                    :title="$t('campaign.import_leads')"
                    :description="$t('campaign.import_leads_description')"
                />
            </a-steps>

            <a-divider />

            <template v-if="currentStep == 0">
                <a-row :gutter="16" class="mt-20">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('campaign.name')"
                            name="name"
                            :help="rules.name ? rules.name.message : null"
                            :validateStatus="rules.name ? 'error' : null"
                            class="required"
                        >
                            <a-input
                                v-model:value="formData.name"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('campaign.name'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('campaign.members')"
                            name="user_id"
                            :help="rules.user_id ? rules.user_id.message : null"
                            :validateStatus="rules.user_id ? 'error' : null"
                            class="required"
                        >
                            <span style="display: flex">
                                <a-select
                                    v-model:value="formData.user_id"
                                    mode="multiple"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('campaign.members'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="allStaffMember in allStaffMembers"
                                        :key="allStaffMember.xid"
                                        :value="allStaffMember.xid"
                                    >
                                        {{ allStaffMember.name }}
                                    </a-select-option>
                                </a-select>
                                <StaffMemberAddButton @onAddSuccess="staffMemberAdded" />
                            </span>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('campaign.form')"
                            name="form_id"
                            :help="rules.form_id ? rules.form_id.message : null"
                            :validateStatus="rules.form_id ? 'error' : null"
                            class="required"
                        >
                            <span style="display: flex">
                                <a-select
                                    v-model:value="formData.form_id"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('campaign.form'),
                                        ])
                                    "
                                    :allowClear="true"
                                    @change="formSelected"
                                >
                                    <a-select-option
                                        v-for="allForm in allForms"
                                        :key="allForm.xid"
                                        :value="allForm.xid"
                                        :form="allForm"
                                    >
                                        {{ allForm.name }}
                                    </a-select-option>
                                </a-select>
                                <FormAddButton @onAddSuccess="formAdded" />
                            </span>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
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
                                    :allowClear="true"
                                >
                                    <a-select-option
                                        v-for="allEmailTemplate in allEmailTemplates"
                                        :key="allEmailTemplate.xid"
                                        :value="allEmailTemplate.xid"
                                    >
                                        {{ allEmailTemplate.name }}
                                    </a-select-option>
                                </a-select>
                                <EmailTemplateAddButton
                                    @onAddSuccess="emailTemplateAdded"
                                />
                            </span>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16" class="mt-20">
                    <a-col :xs="24" :sm="24" :md="8" :lg="8">
                        <a-form-item
                            :label="$t('campaign.allow_reference_prefix')"
                            name="allow_reference_prefix"
                            :help="
                                rules.allow_reference_prefix
                                    ? rules.allow_reference_prefix.message
                                    : null
                            "
                            :validateStatus="
                                rules.allow_reference_prefix ? 'error' : null
                            "
                            class="required"
                        >
                            <a-switch
                                v-model:checked="formData.allow_reference_prefix"
                                :checkedValue="1"
                                :unCheckedValue="0"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col
                        v-if="formData.allow_reference_prefix"
                        :xs="24"
                        :sm="24"
                        :md="16"
                        :lg="16"
                    >
                        <a-form-item
                            :label="$t('campaign.reference_prefix')"
                            name="reference_prefix"
                            :help="
                                rules.reference_prefix
                                    ? rules.reference_prefix.message
                                    : null
                            "
                            :validateStatus="rules.reference_prefix ? 'error' : null"
                            class="required"
                        >
                            <a-input
                                v-model:value="formData.reference_prefix"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('campaign.reference_prefix'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </template>

            <template v-if="currentStep == 1">
                <a-alert
                    v-if="
                        !formData.detail_fields ||
                        (formData.detail_fields && formData.detail_fields.length == 0)
                    "
                    class="mt-20"
                    :description="$t('campaign.add_detail_field_description')"
                    type="info"
                    show-icon
                />

                <a-alert
                    v-if="
                        formData.detail_fields &&
                        formData.detail_fields.length > 0 &&
                        addFieldsButtonStatus
                    "
                    class="mt-20"
                    :description="$t('campaign.add_detail_field_error')"
                    type="error"
                    show-icon
                />

                <div class="mt-20">
                    <a-row
                        :gutter="16"
                        v-for="(detailField, index) in formData.detail_fields"
                        :key="detailField.id"
                    >
                        <a-col :span="7">
                            <a-form-item :name="['detail_fields', index, 'field_name']">
                                <a-input
                                    v-model:value="detailField.field_name"
                                    :placeholder="$t('campaign.field_name')"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="7">
                            <a-form-item :name="['detail_fields', index, 'field_value']">
                                <a-input
                                    v-model:value="detailField.field_value"
                                    :placeholder="$t('campaign.field_value')"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :span="7">
                            <a-form-item :name="['detail_fields', index, 'field_type']">
                                <a-select
                                    v-model:value="detailField.field_type"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('campaign.field_type'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option value="text">
                                        {{ $t("common.text") }}
                                    </a-select-option>
                                    <a-select-option value="link">
                                        {{ $t("common.link") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :span="3">
                            <MinusCircleOutlined
                                @click="removeDetailField(detailField)"
                            />
                        </a-col>
                    </a-row>
                </div>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item>
                            <a-button
                                type="dashed"
                                block
                                @click="addDetailField"
                                :disabled="addFieldsButtonStatus"
                            >
                                <PlusOutlined />
                                {{ $t("campaign.add_detail_field") }}
                            </a-button>
                        </a-form-item>
                    </a-col>
                </a-row>
            </template>

            <template v-if="currentStep == 2">
                <ImportLeads
                    acceptFormat=".csv"
                    :allFields="selectedFormFields"
                    @fileUploaded="leadFileUploaded"
                    @leadColumnChanged="leadColumnChanged"
                />
            </template>
        </a-form>
        <template #footer>
            <a-space>
                <a-button
                    v-if="currentStep != 0"
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="goBack"
                >
                    <DoubleLeftOutlined />
                    {{ $t("campaign.previous_step") }}
                </a-button>
                <a-button
                    v-if="currentStep != 2"
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="onSubmit"
                    :disabled="currentStep == 1 && addFieldsButtonStatus"
                >
                    {{ $t("campaign.next_step") }}
                    <DoubleRightOutlined />
                </a-button>
                <a-button
                    v-if="currentStep == 2"
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="submitForm"
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
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    FileTextOutlined,
    OrderedListOutlined,
    DoubleRightOutlined,
    DoubleLeftOutlined,
    MinusCircleOutlined,
} from "@ant-design/icons-vue";
import { some, forEach } from "lodash-es";
import apiAdmin from "../../../common/composable/apiAdmin";
import StaffMemberAddButton from "../users/StaffAddButton.vue";
import FormAddButton from "../forms/forms/AddButton.vue";
import EmailTemplateAddButton from "../messaging/email-templates/AddButton.vue";
import ImportLeads from "./ImportLeads.vue";

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
        FileTextOutlined,
        OrderedListOutlined,
        DoubleRightOutlined,
        DoubleLeftOutlined,
        MinusCircleOutlined,

        ImportLeads,
        StaffMemberAddButton,
        FormAddButton,
        EmailTemplateAddButton,
    },
    setup(props, { emit }) {
        const currentStep = ref(0);
        const {
            addEditFileRequestAdmin,
            addEditRequestAdmin,
            loading,
            rules,
        } = apiAdmin();
        const formUrl = "forms?fields=xid,name,form_fields&limit=10000";
        const emailTemplateUrl = "email-templates/all";
        const allForms = ref([]);
        const allStaffMembers = ref([]);
        const allEmailTemplates = ref([]);
        const staffMembersUrl = "users?limit=10000";
        const leadFile = ref(undefined);
        const importLeadColumns = ref(undefined);
        const selectedFormFields = ref([]);

        onMounted(() => {
            const staffMemberPromise = axiosAdmin.get(staffMembersUrl);
            const formsPromise = axiosAdmin.get(formUrl);
            const emailTemplatesPromise = axiosAdmin.get(emailTemplateUrl);

            Promise.all([staffMemberPromise, formsPromise, emailTemplatesPromise]).then(
                ([staffMemberResponse, formsResponse, emailTemplatesResponse]) => {
                    allStaffMembers.value = staffMemberResponse.data;
                    allForms.value = formsResponse.data;
                    allEmailTemplates.value = emailTemplatesResponse.data.email_templates;
                }
            );
        });

        const removeDetailField = (item) => {
            let index = props.formData.detail_fields.indexOf(item);
            if (index !== -1) {
                props.formData.detail_fields.splice(index, 1);
            }
        };

        const addDetailField = () => {
            props.formData.detail_fields.push({
                field_name: "",
                field_value: "",
                field_type: "text",
                id: Math.random().toString(36).slice(2),
            });
        };

        const onSubmit = () => {
            if (currentStep.value == 0) {
                if (
                    props.formData.name == "" ||
                    props.formData.user_id.length == 0 ||
                    props.formData.form_id == undefined ||
                    props.formData.email_template_id == undefined ||
                    (props.formData.allow_reference_prefix &&
                        props.formData.reference_prefix == "")
                ) {
                    submitForm();
                } else {
                    rules.value = {};
                    setStep(1);
                }
            } else {
                setStep(currentStep.value + 1);
            }
        };

        const submitForm = () => {
            var newFormData = { ...props.formData };

            if (
                newFormData.user_id == undefined ||
                (newFormData.user_id != undefined &&
                    typeof newFormData.user_id == "object" &&
                    newFormData.user_id.length == 0)
            ) {
                newFormData = { ...props.formData, user_id: undefined };
            }

            if (leadFile.value != undefined) {
                newFormData.file = leadFile.value;
            }

            newFormData.import_lead_fields = importLeadColumns.value;

            addEditFileRequestAdmin({
                url: props.url,
                fieldTypes: {
                    json: ["detail_fields", "user_id", "import_lead_fields"],
                    file: ["file"],
                },
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const goBack = () => {
            currentStep.value = currentStep.value - 1;
            props.formData.current_step = currentStep.value;
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const staffMemberAdded = () => {
            axiosAdmin.get(staffMembersUrl).then((response) => {
                allStaffMembers.value = response.data;
            });
        };

        const formAdded = () => {
            axiosAdmin.get(formUrl).then((response) => {
                allForms.value = response.data;
            });
        };

        const emailTemplateAdded = () => {
            axiosAdmin.get(emailTemplateUrl).then((response) => {
                allEmailTemplates.value = response.data.email_templates;
            });
        };

        const setStep = (stepNumber) => {
            currentStep.value = stepNumber;
            props.formData.current_step = stepNumber;
        };

        const addFieldsButtonStatus = computed(() => {
            if (
                props.formData.detail_fields &&
                props.formData.detail_fields.length == 0
            ) {
                return false;
            } else {
                return (
                    some(props.formData.detail_fields, { field_name: "" }) ||
                    some(props.formData.detail_fields, { field_value: "" })
                );
            }
        });

        const leadFileUploaded = (file) => {
            leadFile.value = file;
        };

        const leadColumnChanged = (allColumns) => {
            importLeadColumns.value = allColumns;
        };

        const formSelected = (seletedValue, options) => {
            if (options && options.form && options.form.form_fields) {
                var allFields = [];

                forEach(options.form.form_fields, (formFieldValue, formFieldKey) => {
                    allFields.push(formFieldValue.name);
                });
                selectedFormFields.value = allFields;
            } else {
                selectedFormFields.value = [];
            }
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                // For campaign members
                if (
                    newVal &&
                    props.addEditType == "edit" &&
                    props.data &&
                    props.data.campaign_users &&
                    props.data.campaign_users.length > 0
                ) {
                    props.formData.user_id = props.data.campaign_users.map(function (el) {
                        return el.x_user_id;
                    });
                } else {
                    props.formData.user_id = undefined;
                }

                // For Form Fields
                formSelected("", props.data);

                setStep(0);
            }
        );

        return {
            currentStep,
            loading,
            rules,
            allStaffMembers,
            allForms,
            allEmailTemplates,
            onClose,
            onSubmit,
            staffMemberAdded,
            formAdded,
            emailTemplateAdded,
            goBack,
            submitForm,

            removeDetailField,
            addDetailField,
            addFieldsButtonStatus,

            leadFileUploaded,
            leadColumnChanged,
            selectedFormFields,
            formSelected,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
        };
    },
});
</script>

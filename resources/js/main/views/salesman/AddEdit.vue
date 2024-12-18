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
            <a-row>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        :label="$t('salesman.profile_image')"
                        name="profile_image"
                        :help="rules.profile_image ? rules.profile_image.message : null"
                        :validateStatus="rules.profile_image ? 'error' : null"
                    >
                        <Upload
                            :formData="formData"
                            folder="user"
                            imageField="profile_image"
                            @onFileUploaded="
                                (file) => {
                                    formData.profile_image = file.file;
                                    formData.profile_image_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="18" :lg="18">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salesman.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('salesman.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salesman.status')"
                                name="status"
                                :help="rules.status ? rules.status.message : null"
                                :validateStatus="rules.status ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('salesman.status'),
                                        ])
                                    "
                                    :allowClear="true"
                                >
                                    <a-select-option value="enabled"
                                        >Enabled</a-select-option
                                    >
                                    <a-select-option value="disabled"
                                        >Disabled</a-select-option
                                    >
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salesman.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('salesman.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('salesman.phone')"
                                name="phone"
                                :help="rules.phone ? rules.phone.message : null"
                                :validateStatus="rules.phone ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.phone"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('salesman.phone'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <!-- <a-row>
                        <a-col :span="24">
                            <a-form-item
                                :label="$t('salesman.password')"
                                name="password"
                                :help="rules.password ? rules.password.message : null"
                                :validateStatus="rules.password ? 'error' : null"
                                class="required"
                            >
                                <a-input-password
                                    v-model:value="formData.password"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('salesman.password'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row> -->
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('salesman.address')"
                        name="address"
                        :help="rules.address ? rules.address.message : null"
                        :validateStatus="rules.address ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.address"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('salesman.address'),
                                ])
                            "
                            :auto-size="{ minRows: 2, maxRows: 3 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import Upload from "../../../common/core/ui/file/Upload.vue";
import RoleAddButton from "../settings/roles/AddButton.vue";
import common from "../../../common/composable/common";

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
        Upload,
        RoleAddButton,
    },
    setup(props, { emit }) {
        const { permsArray, appSetting } = common();
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

            permsArray,
            appSetting,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

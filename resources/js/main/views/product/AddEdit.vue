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
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        :label="$t('product.logo')"
                        name="logo"
                        :help="rules.logo ? rules.logo.message : null"
                        :validateStatus="rules.logo ? 'error' : null"
                    >
                        <Upload
                            :formData="formData"
                            folder="product"
                            imageField="logo"
                            @onFileUploaded="
                                (file) => {
                                    formData.logo = file.file;
                                    formData.logo_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="18" :lg="18">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('product.product_type')"
                                name="product_type"
                                :help="
                                    rules.product_type ? rules.product_type.message : null
                                "
                                :validateStatus="rules.product_type ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.product_type"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('product.product_type'),
                                        ])
                                    "
                                    :allowClear="true"
                                    show-search
                                >
                                    <a-select-option key="products" value="product">
                                        {{ $t("common.product") }}
                                    </a-select-option>
                                    <a-select-option key="service" value="service">
                                        {{ $t("common.service") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('product.price')"
                                name="price"
                                :help="rules.price ? rules.price.message : null"
                                :validateStatus="rules.price ? 'error' : null"
                                class="required"
                            >
                                <a-input-number
                                    v-model:value="formData.price"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('product.price'),
                                        ])
                                    "
                                    min="0"
                                    style="width: 100%"
                                >
                                    <template #addonBefore>
                                        {{ appSetting.currency.symbol }}
                                    </template>
                                </a-input-number>
                            </a-form-item>
                        </a-col>
                    </a-row>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('product.tax_label')"
                        name="tax_label"
                        :help="rules.tax_label ? rules.tax_label.message : null"
                        :validateStatus="rules.tax_label ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.tax_label"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('product.tax_label'),
                                ])
                            "
                        >
                        </a-input>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('product.tax_rate')"
                        name="tax_rate"
                        :help="rules.tax_rate ? rules.tax_rate.message : null"
                        :validateStatus="rules.tax_rate ? 'error' : null"
                        class="required"
                    >
                        <a-input-number
                            v-model:value="formData.tax_rate"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('product.tax_rate'),
                                ])
                            "
                            min="0"
                            style="width: 100%"
                        >
                        </a-input-number>
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
import { defineComponent } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import Upload from "../../../common/core/ui/file/Upload.vue";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
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
    },
    setup(props, { emit }) {
        const { appSetting } = common();
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
            appSetting,
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>

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
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.display_name')"
                        name="display_name"
                        :help="rules.display_name ? rules.display_name.message : null"
                        :validateStatus="rules.display_name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.display_name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.display_name'),
                                ])
                            "
                            v-on:keyup="formData.name = slugify($event.target.value)"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('role.role_name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.role_name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.description')"
                        name="description"
                        :help="rules.description ? rules.description.message : null"
                        :validateStatus="rules.description ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.description"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('role.description'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('role.permissions')"
                        name="permissions"
                        :help="rules.permissions ? rules.permissions.message : null"
                        :validateStatus="rules.permissions ? 'error' : null"
                    >
                        <div class="d-flex flex-column scroll-y">
                            <div class="tbl-responsive">
                                <a-checkbox-group v-model:value="checkedPermissions">
                                    <table
                                        class="table align-middle table-row-dashed row-gap"
                                    >
                                        <tbody class="text-gray-600 fw-bold">
                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.staff_members") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'users_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.salesmans") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'salesmans_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'salesmans_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'salesmans_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'salesmans_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.campaigns") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'campaigns_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'campaigns_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'campaigns_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'campaigns_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("campaign.campaign_view") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'campaigns_view_all'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t("common.view_all")
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'view_completed_campaigns'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t(
                                                                        "campaign.view_completed_campaigns"
                                                                    )
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.leads") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'leads_view_all'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t("common.view_all")
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'leads_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'leads_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.email_templates") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_templates_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_templates_view_all'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t("common.view_all")
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_templates_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_templates_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_templates_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.expense_categories") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expense_categories_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.expenses") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'expenses_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.products") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'products_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>



                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.forms") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'forms_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'forms_view_all'
                                                                    ]
                                                                "
                                                            >
                                                                {{
                                                                    $t("common.view_all")
                                                                }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'forms_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'forms_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'forms_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.form_field_names") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'form_field_names_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'form_field_names_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'form_field_names_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'form_field_names_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.translations") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'translations_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.roles") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'roles_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.currencies") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_view'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.view") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_create'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.add") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                        <label
                                                            class="form-check form-check-custom"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'currencies_delete'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.delete") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.company") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'companies_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.storage_settings") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'storage_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-gray-800">
                                                    {{ $t("menu.email_settings") }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <label
                                                            class="form-check form-check-custom me-5 me-lg-20"
                                                        >
                                                            <a-checkbox
                                                                :value="
                                                                    permissions[
                                                                        'email_edit'
                                                                    ]
                                                                "
                                                            >
                                                                {{ $t("common.edit") }}
                                                            </a-checkbox>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </a-checkbox-group>
                            </div>
                        </div>
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
import { defineComponent, ref, onMounted, computed, watch } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";

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
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const roles = ref([]);
        const { t } = useI18n();
        const permissions = ref([]);
        const checkedPermissions = ref([]);
        const { slugify } = common();

        onMounted(() => {
            axiosAdmin
                .get("permissions?fields=id,xid,name,display_name&limit=10000")
                .then((response) => {
                    const permissionArray = [];
                    response.data.map((res) => {
                        permissionArray[res.name] = res.xid;
                    });
                    permissions.value = permissionArray;
                });
        });

        const onSubmit = () => {
            const newFormData = {
                ...props.formData,
                permissions: checkedPermissions.value,
            };

            addEditRequestAdmin({
                url: props.url,
                data: newFormData,
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

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (newVal && props.addEditType == "edit") {
                    props.data.perms.forEach((value) => {
                        checkedPermissions.value.push(value.xid);
                    });
                } else {
                    checkedPermissions.value = [];
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,
            permissions,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            checkedPermissions,
            slugify,
        };
    },
});
</script>

<style lang="less">
.flex-column {
    flex-direction: column !important;
}

.d-flex {
    display: flex !important;
}

.tbl-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
}

.align-middle {
    vertical-align: middle !important;
}
.table > tbody {
    vertical-align: inherit;
}
.text-gray-600 {
    color: #7e8299 !important;
}
.fw-bold {
    font-weight: 500 !important;
}
tbody,
td,
tfoot,
th,
thead,
tr {
    border-color: inherit;
    border-style: solid;
    border-width: 0;
}

.table.table-row-dashed tr {
    border-bottom-width: 1px;
    border-bottom-style: dashed;
    border-bottom-color: #eff2f5;
}

.table td:first-child,
.table th:first-child,
.table tr:first-child {
    padding-left: 0;
}

.form-check.form-check-custom {
    display: flex;
    align-items: center;
    padding-left: 0;
    margin: 0;
}

.me-9 {
    margin-right: 2.25rem !important;
}

.table.row-gap td,
.table.row-gap th {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.me-lg-20 {
    margin-right: 5rem !important;
}

.ant-checkbox-group {
    width: 100%;
}
</style>

import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "form-field-names?fields=id,xid,field_name,similar_field_names,visible";
    const addEditUrl = "form-field-names";
    const hashableColumns = [];
    const { t } = useI18n();

    const initData = {
        field_name: "",
        similar_field_names: [],
        visible: 1
    };

    const columns = [
        {
            title: t("form_field_name.field_name"),
            dataIndex: "field_name",
        },
        {
            title: t("form_field_name.similar_field_names"),
            dataIndex: "similar_field_names",
        },
        {
            title: t("form_field_name.visible"),
            dataIndex: "visible",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "field_name",
            value: t("form_field_name.field_name")
        },
        {
            key: "similar_field_names",
            value: t("form_field_name.similar_field_names")
        },
        {
            key: "deletable",
            value: t("form_field_name.deletable")
        },
        {
            key: "visible",
            value: t("form_field_name.visible")
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
    }
}

export default fields;

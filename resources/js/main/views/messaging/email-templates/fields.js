import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "email-templates?fields=id,xid,name,subject,body,status,sharable";
    const addEditUrl = "email-templates";
    const hashableColumns = [];
    const { t } = useI18n();

    const initData = {
        name: "",
        subject: "",
        body: "",
        status: 1,
        sharable: 0
    };

    const columns = [
        {
            title: t("email_template.name"),
            dataIndex: "name",
        },
        {
            title: t("email_template.subject"),
            dataIndex: "subject",
        },
        {
            title: t("email_template.status"),
            dataIndex: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("email_template.name")
        },
        {
            key: "subject",
            value: t("email_template.subject")
        },
        {
            key: "body",
            value: t("email_template.body")
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

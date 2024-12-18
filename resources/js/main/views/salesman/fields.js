import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const url = "salesmans?fields=id,xid,name,email,profile_image,profile_image_url,phone,address,status,created_at";
    const addEditUrl = "salesmans";

    const initData = {
        name: "",
        email: "",
        password: "12345678",
        profile_image: undefined,
        profile_image_url: undefined,
        phone: "",
        address: "",
        status: "enabled",
    };

    const columns = [
        {
            title: t("salesman.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("salesman.email"),
            dataIndex: "email",
        },
        {
            title: t("salesman.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("salesman.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("salesman.name")
        },
        {
            key: "email",
            value: t("salesman.email")
        },
        {
            key: "phone",
            value: t("salesman.phone")
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        addEditUrl,
    }
}

export default fields;

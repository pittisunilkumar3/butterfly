import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const url = "users?fields=id,xid,user_type,name,email,profile_image,profile_image_url,phone,address,status,created_at,role_id,x_role_id,role{id,xid,name,display_name}";
    const addEditUrl = "users";
    const hashableColumns = ['role_id'];

    const initData = {
        name: "",
        email: "",
        password: "",
        profile_image: undefined,
        profile_image_url: undefined,
        phone: "",
        address: "",
        status: "enabled",
        user_type: "staff_members",
        role_id: undefined,
    };

    const columns = [
        {
            title: t("user.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("user.email"),
            dataIndex: "email",
        },
        {
            title: t("user.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("user.status"),
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
            value: t("user.name")
        },
        {
            key: "email",
            value: t("user.email")
        },
        {
            key: "phone",
            value: t("user.phone")
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        addEditUrl,
        hashableColumns,
    }
}

export default fields;

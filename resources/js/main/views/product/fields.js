import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "products?fields=id,xid,name,price,product_type,tax_rate,tax_label,logo,logo_url";
    const addEditUrl = "products";
    const { t } = useI18n();

    const initData = {
        name: "",
        price: "",
        product_type: 'product',
        tax_rate: 0,
        tax_label: "",
        logo: undefined,
        logo_url: undefined
    };

    const columns = [
        {
            title: t("product.logo"),
            dataIndex: "logo",
        },
        {
            title: t("product.product_type"),
            dataIndex: "product_type",
        },
        {
            title: t("product.name"),
            dataIndex: "name",
        },
        {
            title: t("product.price"),
            dataIndex: "price",
        },
        {
            title: t("product.tax_label"),
            dataIndex: "tax_label",
        },
        {
            title: t("product.tax_rate"),
            dataIndex: "tax_rate",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name")
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
    }
}

export default fields;

import { computed } from "vue";

export default function useDateTimeFormatter() {
    const formatDateTime = computed(() => (dateTime: string) => {
        const dt = new Date(dateTime);

        let dd = dt.getDate().toString();
        let mm = (dt.getMonth() + 1).toString();
        let h = (dt.getHours()).toString();
        let min = (dt.getMinutes()).toString();

        if (dt.getDate() < 10) dd = "0" + dd;
        if ((dt.getMonth() + 1) < 10) mm = "0" + mm;
        if (dt.getHours() < 10) h = "0" + h;
        if (dt.getMinutes() < 10) min = "0" + min;

        return dd + "." +
            mm + "." +
            dt.getFullYear().toString() + " " +
            h + ":" + min;
    });

    return { formatDateTime };
}

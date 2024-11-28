import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const experimentsList = async ({ page, itemsPerPage, sortBy, search }) => {
    const url = `/experiments?page=${
        page || 1
    }&perPage=${itemsPerPage}&search=${search || ""}`;
    const sortByQueryParams = sortBy
        ? `&sortByKey=${sortBy.key}&sortByOrder=${sortBy.order}`
        : "";

    try {
        const { data } = await api.get(url + sortByQueryParams);

        return data;
    } catch (err) {
        console.error(err.message);

        return err;
    }
};

const experimentSave = async (experimentData) => {
    const isUpdate = !!experimentData.id;
    const url = isUpdate ? `/experiments/${experimentData.id}` : "/experiments";

    const formData = new FormData();
    if (experimentData.file) {
        formData.append("file", experimentData.file);
    }

    if (experimentData.name) {
        formData.append("name", experimentData.name);
    }

    formData.append("context", experimentData.context);
    formData.append("output", experimentData.output);
    formData.append("save", experimentData.save);

    try {
        const data = await api.post(url, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        return data;
    } catch (err) {
        console.error(err.message);

        throw err;
    }
};

const experimentDetail = async (experimentId) => {
    const url = `/experiments/${experimentId}`;

    try {
        const { data } = await api.get(url);
        return data;
    } catch (err) {
        console.error(err.message);

        return err;
    }
};

const experimentSimulate = async ({ id, context }) => {
    const url = `/experiments/${id}/simulate`;

    try {
        const { data } = await api.post(url, { context });

        return data;
    } catch (err) {
        console.error(err.message);
        
        throw err;
    }
};

const experimentDestroy = async (id) => {
    const url = `/experiments/${id}`;

    try {
        const { data } = await api.delete(url);

        return data;
    } catch (err) {
        console.error(err.message);

        return err;
    }
};

const experimentContext = async (file) => {
    const url = "/experiments/get_context";
    const formData = new FormData();
    formData.append("file", file);

    try {
        const data = await api.post(url, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        return data;
    } catch (err) {
        console.error(err.message);

        return err;
    }
};

export const useExperimentsListMutation = () =>
    useMutation({ mutationFn: experimentsList });
export const useExperimentSaveMutation = () =>
    useMutation({ mutationFn: experimentSave });
export const useExperimentDetailMutation = () =>
    useMutation({ mutationFn: experimentDetail });
export const useExperimentSimulateMutation = () =>
    useMutation({ mutationFn: experimentSimulate });
export const useExperimentDestroyMutation = () =>
    useMutation({ mutationFn: experimentDestroy });
export const useExperimentContextMutation = () =>
    useMutation({ mutationFn: experimentContext });

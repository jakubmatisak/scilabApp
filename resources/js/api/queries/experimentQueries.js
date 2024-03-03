import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const experimentsList = async ({ page, itemsPerPage, sortBy }) => {
    const url = `/experiments?page=${page || 1}&perPage=${itemsPerPage}`;
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
    formData.append("name", experimentData.name);
    formData.append("context", experimentData.context);
    formData.append("output", experimentData.output);

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

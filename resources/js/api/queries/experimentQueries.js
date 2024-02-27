import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const experimentsList = async () => {
    try {
        const { data } = await api.get("/experiments");
        return data;
    } catch(err) {
        console.error(err.message);
        
        return err;
    }
};

const experimentCreate = async (experimentData) => {
    const formData = new FormData();
    formData.append('file', experimentData.file);
    formData.append("name", experimentData.name);
    formData.append("context", experimentData.context);
    formData.append("output", experimentData.output);

    try {
        const data = await api.post("/experiments", formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            },
        });
        
        return data;
    }catch (err) {
        console.error(err.message);
    }
};

export const useExperimentsListMutation = () => useMutation({mutationFn: experimentsList});
export const useExperimentSaveMutation = () => useMutation({mutationFn: experimentCreate});
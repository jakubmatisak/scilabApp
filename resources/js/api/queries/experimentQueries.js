import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const experimentsList = () =>
    api.get("/experiments");

export const useExperimentsListMutation = () => useMutation({mutationFn: experimentsList});
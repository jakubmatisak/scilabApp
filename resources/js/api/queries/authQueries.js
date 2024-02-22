import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const signIn = async (signInCredentials) => {
    const response = await api.post("/auth/login", signInCredentials);
    return response.data;
};

const signUp = async (signUpCredentials) => {
    const response = await api.post("/auth/register", signUpCredentials);
    return response.data;
};

export const useSignInMutation = () => useMutation({ mutationFn: signIn });
export const useSignUpMutation = () => useMutation({ mutationFn: signUp });

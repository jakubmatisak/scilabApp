import { useMutation } from "@tanstack/vue-query";
import api from "../api";

const signIn = (signInCredentials) =>
    api.post("/auth/login", signInCredentials);

const signUp = (signUpCredentials) =>
    api.post("/auth/register", signUpCredentials);

const getCurrentLoggedUser = () => 
    api.get("/user");

export const useSignInMutation = () => useMutation({ mutationFn: signIn });
export const useSignUpMutation = () => useMutation({ mutationFn: signUp });
export const useCurrentLoggedUserMutation = () => useMutation({mutationFn: getCurrentLoggedUser});
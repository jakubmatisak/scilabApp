import { useMutation, useQuery } from "@tanstack/vue-query";
import api from "../api";

const userList = () => api.get("users");
const userDetail = async (id) => {
    const url = `user/${id}`;

    try {
        const { data } = await api.get(url);
        return data;
    } catch (err) {
        console.error(err.message);

        return err;
    }
};

export const useUserListQuery = () =>
    useQuery({ queryKey: ["userList"], queryFn: userList });
export const useUserDetailMutation = () =>
    useMutation({ mutationFn: userDetail });

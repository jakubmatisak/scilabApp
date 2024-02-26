export const parseDate = (dateString) => {
    const date = new Date(dateString);

    return `${date.getDate()}-${date.getMonth()}-${date.getFullYear()}`;
};
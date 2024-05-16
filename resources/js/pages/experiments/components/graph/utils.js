const transformArrayToSCVContent = (data) => {
    const rows = data.map((row) => row.join("\t"));
    return rows.join("\n");
};

const transformObjectsToCSVContent = (data, includeHeader) => {
    const headerContent = Object.keys(data[0]).join("\t");
    const rows = data.map((row) => Object.values(row).join("\t"));
    if (includeHeader) {
        return [headerContent, ...rows].join("\n");
    }

    return rows.join("\n");
};

export const transformDataToCSVContent = (data, includeHeader) => {
    if (Array.isArray(data[0])) {
        return transformArrayToSCVContent(data);
    }

    return transformObjectsToCSVContent(data, includeHeader);
};

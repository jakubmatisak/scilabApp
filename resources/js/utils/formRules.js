const onlyUnique = (value, index, array) => {
    return array.indexOf(value) === index;
};

export const arrayContainsUniqueItems = (arrayString) => {
    const array = JSON.parse(arrayString);
    const uniqueArray = array.filter(onlyUnique);
    return uniqueArray.length === array.length;
};

export const objectContainsUniqueKeys = (objectString) => {
    const object = JSON.parse(objectString);
    const keys = Object.keys(object);
    const uniqueKeys = keys.filter(onlyUnique);
    return uniqueKeys.length === keys.length;
};

export const onlyStrings = (arrayString) => {
    const array = JSON.parse(arrayString);
    const notString = array.find((item) => typeof item !== "string");

    return !notString;
};

export const isArrayString = (arrayString) => {
    try {
        const o = JSON.parse(arrayString);

        if (o && typeof o === "object" && Array.isArray(o)) {
            return true;
        }
    } catch (e) {
        /* empty */
    }

    return false;
};

export const isJsonString = (jsonString) => {
    try {
        const o = JSON.parse(jsonString);

        if (o && typeof o === "object") {
            return true;
        }
    } catch (e) {
        /* empty */
    }

    return false;
};

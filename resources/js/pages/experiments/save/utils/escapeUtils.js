export const escapeCharacters = (string) => {
    const escapedBackslash = escapeBackslash(string);
    const escapedQuotes = escapeQuotes(escapedBackslash);

    return escapedQuotes;
};

const escapeQuotes = (string) => {
    if (typeof string === "string") {
        return string.replaceAll('"', '\\"');
    }

    return string;
};

const escapeBackslash = (string) => {
    if (typeof string === "string") {
        return string.replaceAll("\\", "\\\\");
    }

    return string;
};

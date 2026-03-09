export function validateString (userInput: string) : boolean {
    // DESC: Validate the length
    if (userInput.length < 10 || userInput.length > 140) {
        return false;
    }

    // DESC: Validate the content
    const regexPattern = /^[a-zA-Z0-9\p{P}\s]*$/u;
    if (regexPattern.test(userInput) === false) {
        return false;
    }

    return true;
}
import { ApiError } from './errors.js';

export function handleError(error) {
    if (error instanceof ApiError) {
        // Handle API errors
        console.error(`API Error (${error.status}): ${error.message}`);
    } else {
        // Generic error handling
        console.error(`Error: ${error.message}`);
    }
    // Here you can also send the error to a logging/monitoring service.
}

export function normalizeErrors(errors) {
    const normalizedErrors = {};
    for (const key in errors) {
        normalizedErrors[key] = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
    }
    return normalizedErrors;
}


// export function normalizeErrors(errors, defaultFieldName = 'field') {
//     const normalizedErrors = {};
//     for (const key in errors) {
//         // If the key is numeric, default to the specified field name
//         const normalizedKey = isNaN(key) ? key : defaultFieldName;
//         normalizedErrors[normalizedKey] = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
//     }
//     return normalizedErrors;
// }
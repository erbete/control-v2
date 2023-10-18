const baseURL = import.meta.env.VITE_API_BASE_URL;

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

const extractXSRFToken = (): string => {
    const nameLenPlus = ("XSRF-TOKEN".length + 1);
    const value = document.cookie
        .split(";")
        .map(c => c.trim())
        .filter(cookie => {
            return cookie.substring(0, nameLenPlus) === "XSRF-TOKEN=";
        })
        .map(cookie => {
            return decodeURIComponent(cookie.substring(nameLenPlus));
        })[0] || "";

    return value;
};

export class TimeoutError extends Error {
    constructor(msg: string) {
        super(msg);
        Object.setPrototypeOf(this, TimeoutError.prototype);
    }
}

export const makeRequest = (method: "GET" | "POST" | "PUT") => async (url: string, body?: any, timeout: number = 8000): Promise<Response> => {
    const controller = new AbortController();
    const { signal } = controller;

    const request = fetch(baseURL + url, {
        method,
        headers: {
            ...headers,
            ...(method !== "GET" ? { "X-XSRF-TOKEN": extractXSRFToken() } : {}),
        },
        body: method === "GET" ? undefined : JSON.stringify(body),
        credentials: "include",
        signal: signal,
    });

    const timer = setTimeout(() => controller.abort(), timeout);

    try {
        return await request;
    } catch (_) {
        throw new TimeoutError("Forespørselen ble avbrutt grunnet en serverfeil eller treg ytelse.");
    } finally {
        clearTimeout(timer);
    }
};

export const get = makeRequest("GET");
export const post = makeRequest("POST");
export const put = makeRequest("PUT");
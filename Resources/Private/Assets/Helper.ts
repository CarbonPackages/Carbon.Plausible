const storage = localStorage;
const storeKey = "plausible_ignore";

export function trackingIsDisabled(): boolean {
    return storage[storeKey] === "true";
}

export function disableTracking() {
    storage[storeKey] = "true";
}

export function enableTracking() {
    delete storage[storeKey];
}

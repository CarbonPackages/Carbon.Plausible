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

export function replaceDomainPlaceholders() {
    const domain = window.location.hostname;

    const walker = document.createTreeWalker(
        document.documentElement,
        NodeFilter.SHOW_TEXT,
    );

    let node;

    while ((node = walker.nextNode())) {
        if (node.nodeValue.includes("{domain}")) {
            node.nodeValue = node.nodeValue.replaceAll("{domain}", domain);
        }
    }
}

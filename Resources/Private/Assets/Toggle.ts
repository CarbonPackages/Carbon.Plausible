import {
    trackingIsDisabled,
    enableTracking,
    disableTracking,
} from "./Helper.js";

const disabledStatus = selectorAll(".-plausible-disabled");
const enabledStatus = selectorAll(".-plausible-enabled");

setStatus(trackingIsDisabled());

selectorAll(".-plausible-status").forEach((element) => {
    element.style.display = null;
});
selectorAll(".-plausible-button").forEach((element) => {
    element.addEventListener("click", () => setStatus(toggleTracking()));
    element.style.display = null;
});

function setStatus(disable: boolean) {
    disabledStatus.forEach(
        (message) => (message.style.display = disable ? null : "none"),
    );
    enabledStatus.forEach(
        (message) => (message.style.display = disable ? "none" : null),
    );
}

function selectorAll(selector: string): HTMLElement[] {
    return [...document.querySelectorAll(selector)];
}

function toggleTracking() {
    const status = trackingIsDisabled();
    status ? enableTracking() : disableTracking();
    return !status;
}

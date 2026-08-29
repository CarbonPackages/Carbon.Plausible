import {
    trackingIsDisabled,
    enableTracking,
    disableTracking,
} from "./Helper.js";

const disabledStatus = getElements("disabled");
const enabledStatus = getElements("enabled");

setStatus(trackingIsDisabled());

getElements("status").forEach((element) => {
    element.style.display = null;
});
getElements("button").forEach((element) => {
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

function getElements(selector: string): HTMLElement[] {
    return [...document.querySelectorAll(`[data-plausible="${selector}"]`)];
}

function toggleTracking() {
    const status = trackingIsDisabled();
    status ? enableTracking() : disableTracking();
    return !status;
}

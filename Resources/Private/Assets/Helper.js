const doc = document;

/**
 * Select an single element
 *
 * @param {string} selector - The element to select
 * @param {NodeElement} base - The basis element
 * @returns {NodeElement}
 */
const selector = (selector, base = doc) => (base ? base.querySelector(selector) : null);

/**
 * Select an multiple elements
 *
 * @param {string} selector - The elements to select
 * @param {NodeElement} base - The basis element
 * @returns {Array<NodeElement>}
 */
const selectorAll = (selector, base = doc) => (base ? [...base.querySelectorAll(selector)] : []);

/**
 * Check if tracking is disabled.
 *
 * @return {boolean}
 */
const trackingIsDisabled = () => localStorage.plausible_ignore === "true";

/**
 * Disable tracking by setting the local storage entry to true.
 */
const disableTracking = () => (localStorage.plausible_ignore = true);

/**
 * Disable tracking by deleting the local storage entry.
 */
const enableTracking = () => delete localStorage.plausible_ignore;

/**
 * Toogle tracking
 *
 * @returns {boolean} - The new status of tracking
 */
const toggleTracking = () => {
    const status = trackingIsDisabled();
    status ? enableTracking() : disableTracking();
    return !status;
};

/**
 * Disable tracking and forward to the homepage.
 */
const disableAndForward = () => {
    const timeout = trackingIsDisabled() ? 0 : 5000;
    disableTracking();
    setTimeout(() => {
        window.location = "/";
    }, timeout);
};

/**
 * Toggle class of document
 *
 * @param {string} className - The class to toggle
 * @param {boolean} state - The state of the class
 * @returns {boolean}
 */
const docClassListToggle = (className, force) => doc.documentElement.classList.toggle(className, force);

export {
    selector,
    selectorAll,
    trackingIsDisabled,
    enableTracking,
    disableTracking,
    toggleTracking,
    disableAndForward,
    docClassListToggle,
};

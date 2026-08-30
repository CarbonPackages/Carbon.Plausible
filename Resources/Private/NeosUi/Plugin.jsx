import manifest from "@neos-project/neos-ui-extensibility";
import React, { Suspense, lazy } from "react";
import LoadingAnimation from "carbon-neos-loadinganimation/LoadingWithStyleX";

const views = {
    StatisticView: () => import("./StatisticView"),
    TrackingView: () => import("./TrackingView"),
};

function generateLazyComponent(component) {
    const LazyComponent = lazy(component);
    return (props) => (
        <Suspense fallback={<LoadingAnimation isLoading={true} />}>
            <LazyComponent {...props} />
        </Suspense>
    );
}

const viewKeys = Object.keys(views);
const loadedViews = viewKeys.map((key) => generateLazyComponent(views[key]));

manifest("Carbon.Plausible:Editor", {}, (globalRegistry) => {
    const viewsRegistry = globalRegistry.get("inspector").get("views");

    viewKeys.forEach((key, index) => {
        viewsRegistry.set(`Carbon.Plausible/${key}`, {
            component: loadedViews[index],
        });
    });
});

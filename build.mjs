import esbuild from "esbuild";
import extensibilityMap from "@neos-project/neos-ui-extensibility/extensibilityMap.json" with { type: "json" };
import stylex from "@stylexjs/unplugin";

const watch = process.argv.includes("--watch");
const dev = process.argv.includes("--dev");
const editor = process.argv.includes("--editor");
const minify = !dev && !watch;

/** @type {import("esbuild").BuildOptions} */
const defaultOptions = {
    logLevel: "info",
    bundle: true,
    minify,
    sourcemap: watch,
    legalComments: "none",
};

if (minify) {
    defaultOptions.drop = ["debugger"];
    defaultOptions.pure = ["console.log"];
    defaultOptions.dropLabels = ["DEV"];
}

const options = [
    {
        ...defaultOptions,
        entryPoints: ["Resources/Private/NeosUi/Plugin.jsx"],
        outdir: "Resources/Public",
        alias: extensibilityMap,
        format: "esm",
        splitting: true,
        target: "es2020",
        loader: {
            ".js": "jsx",
        },
        metafile: true,
        plugins: [
            stylex.esbuild({
                useCSSLayers: false,
                classNamePrefix: "plausible-",
                dev: false,
                lightningcssOptions: {
                    minify: true,
                },
            }),
        ],
    },
    {
        ...defaultOptions,
        entryPoints: [
            "Resources/Private/Assets/Toggle.ts",
            "Resources/Private/Assets/LocalStorage.ts",
        ],
        outdir: "Resources/Public",
    },
];

if (watch) {
    options.forEach((opt) => esbuild.context(opt).then((ctx) => ctx.watch()));
} else {
    options.forEach((opt) => esbuild.build(opt));
}

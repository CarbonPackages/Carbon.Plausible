[![Latest stable version]][packagist] [![Total downloads]][packagist] [![License]][packagist] [![GitHub forks]][fork] [![GitHub stars]][stargazers] [![GitHub watchers]][subscription]

# Carbon.Plausible Package for Neos CMS

Easily integrate [Plausible Analytics][plausible] into your [Neos site][neos].

## Introduction

[Plausible] is a lightweight and open-source website analytics tool. It doesn't use cookies and is fully compliant with GDPR, CCPA, and PECR. This plugin is meant to remove all friction from adding the [Plausible Analytics tracking script code] to your Neos site. All you need to do is define your [Plausible domain] in your Neos [`Settings.yaml`] file.

## Features

- Multi-site compatibility
- Backend module
- Check if the requested domain matches the defined domain to track
- Enabled per default only on `Production` environment
- Embed stats directly into the Neos Backend
- Proxies the needed JS files (cached for 6 hours) and API from Plausible

### Multi-site compatibility

If you run a multi-site setup, we got you covered! You can set different trackings for the sites based on the root node name.

### Backend module

This package adds a backend module to your Neos instance, which helps check your configuration and opt-out your browser for tracking.

![screenshot of backend module as administrator]

If a backend user is not an administrator, he'll get a different view:

![screenshot of backend module as editor]

It also checks if the resulting javascript path doesn't return a 404 error:

![error in the backend module]

### NodeType mixins for disable tracking on a document or set custom events

This package contains two mixins:

- [Carbon.Plausible:Mixin.CustomEvent]: This allows you to set [custom events] to a document via the inspector. Of course, you can do this also directly in your JavaScript or Fusion
- [Carbon.Plausible:Mixin.DoNotTrack]: This allows you to disable the tracking for a specific document

![set options in the inspector]

### Opt out and exclude your visits from the analytics

By default, Plausible Analytics tracks every visitor to your website. When you're working on your site, you might not want to record your own visits and page views. To prevent counting your visits, you can set a special localStorage flag in the browser. Here's how.

- Go to `your-domain.tld/~/disable-tracking`. This sets the flag and redirects the visitor to the homepage. Great for people without access to the Neos Backend.
- As an Editor, you can enable/disable the flag also in the Plausible management module: `your-domain.tld/neos/management/plausible`
- Add the component [Carbon.Plausible:Component.Toggle] to a document and click the button.
- You can do this also by yourself by following the [excluding guide on plausible.io]

### Tracking custom event goals with `data-analytics`

To use this feature, you have to enable the `dataAnalyticsTracking` setting. Register events in the HTML with the use of an attribute tag `data-analytics`.

**Note: Watch your quotes!** Especially in the props as we want to be able to create an object.

```html
<!-- Tracking a form -->
<form>
  ...
  <button type="submit" data-analytics='"Contact"'>Send Message...</button>
</form>

<!-- Tracking a link -->
<a
  href="/register"
  data-analytics='"Register", {"props":{"plan":"Navigation"}}'
>
  Register
</a>
```

## Installation

Run the following command in your site package

```bash
composer require --no-update carbon/plausible
```

Then run `composer update` in your project root.

## Configuration

### Single-site setup

If you have a single site setup, you can adjust the configuration under the key `Carbon.Plausible.default` in your [`Settings.yaml`]:

| Key                     | Default | Description                                                                                                                                                                                                                                                                                                                                                                                      |
| ----------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `domain`                | `null`  | Set here the [plausible domain]. This setting is required                                                                                                                                                                                                                                                                                                                                        |
| `reverseProxy`          | `true`  | Proxies the needed Javascript (cached for 6 hours) and API calls from Plausible. If set, the settings `host` has no effect                                                                                                                                                                                                                                                                       |
|                         |
| `host`                  | `null`  | If you have set a [custom domain], you can set it here. Example: `stats.uhlmann.pro`. For new instances you cannot set up a [custom domain], as it is depreciated. To enable this setting, you have to set `reverseProxy` to `false`.                                                                                                                                                            |
| `hashBasedRouting`      | `false` | Automatically follow frontend navigation when using [hash-based routing]                                                                                                                                                                                                                                                                                                                         |
| `exclusions`            | `false` | [Exclude certain pages from being tracked]                                                                                                                                                                                                                                                                                                                                                       |
| `outboundLinks`         | `false` | Automatically [track clicks on outbound links] from your website                                                                                                                                                                                                                                                                                                                                 |
| `fileDownloads`         | `false` | Automatically [track file downloads]                                                                                                                                                                                                                                                                                                                                                             |
| `compat`                | `false` | Compatibility mode for tracking users on Internet Explorer                                                                                                                                                                                                                                                                                                                                       |
| `local`                 | `false` | Allow analytics to track on localhost too which is useful in hybrid apps                                                                                                                                                                                                                                                                                                                         |
| `manual`                | `false` | [Don't trigger pageviews automatically.] Also allows you to [specify custom locations] to redact URLs with identifiers. You can also use it to track [custom query parameters]                                                                                                                                                                                                                   |
| `customEvents`          | `false` | If you want to set [custom events] in your javascript, set this to `true` or a string. If set to a string, this whole string gets included on every document. If you set custom events via Fusion or the [Carbon.Plausible:Mixin.CustomEvent] mixin, you don't have to set it to `true`. The snippet gets activated automatically if needed. The inline javascript get's minified with [JShrink] |
| `dataAnalyticsTracking` | `false` | If you want to enable `data-analytics` for tracking links and form submits set this to `true`                                                                                                                                                                                                                                                                                                    |
| `sharedLink`            | `null`  | If you have opened up your [website stats to the public] or created a [private and secure link], enter it to enable the embedded view of the stats in the backend                                                                                                                                                                                                                                |

### Multi-site setup

If you run multiple sites on one Neos installation, you can set this under the key `Carbon.Plausible.sites` in your [`Settings.yaml`]. Be aware that if you set one value in `Carbon.Plausible.default`, these are set as the new fallback value. For example, if you set `Carbon.Plausible.default.outboundLinks` to `true`, is `outboundLinks` set to `true` per default for all sites. Of course, you can disable this again if you set this to `false` on your site setting.

Example:

```yaml
Carbon:
  Plausible:
    sites:
      myfirstsite:
        domain: domain.com
        outboundLinks: true
        sharedLink: https://plausible.io/share/domain.com?auth=abcdefghijklmnopqrstu
      mysecondsite:
        domain: domain.org
        hashBasedRouting: true
        exclusions: "/blog4, /rule/*, /how-to-*, /*/admin"
        sharedLink: https://plausible.io/domain.org
      mythirdsite:
        domain: domain.net
        customEvent: "plausible('Download', {props: {method: 'HTTP'}})"
        exclusions:
          - /blog4
          - /rule/*
          - /how-to-*
          - /*/admin
```

The key of the site (e.g. `myfirstsite`) is the root node name found under Administration » Sites Management.

## Fusion Components

### Carbon.Plausible:Component.TrackingCode

The main Fusion component is [Carbon.Plausible:Component.TrackingCode]. This component gets included into [Neos.Neos:Page] under the path `plausibleTrackingCode`. So if you want to add a [custom event][custom events] to a ceratin document, you can do it like this:

```
prototype(Vendor.Site:Document.NotFound) < prototype(Neos.Neos:Page) {
    plausibleTrackingCode.customEvents = 'plausible("404",{ props: { path: document.location.pathname } });'
}
```

### Carbon.Plausible:Component.Toggle

[Carbon.Plausible:Component.Toggle] is a small component to let the user if he wants to opt-out from tracking.

[packagist]: https://packagist.org/packages/carbon/plausible
[latest stable version]: https://poser.pugx.org/carbon/plausible/v/stable
[total downloads]: https://poser.pugx.org/carbon/plausible/downloads
[license]: https://poser.pugx.org/carbon/plausible/license
[github forks]: https://img.shields.io/github/forks/CarbonPackages/Carbon.Plausible.svg?style=social&label=Fork
[github stars]: https://img.shields.io/github/stars/CarbonPackages/Carbon.Plausible.svg?style=social&label=Stars
[github watchers]: https://img.shields.io/github/watchers/CarbonPackages/Carbon.Plausible.svg?style=social&label=Watch
[fork]: https://github.com/CarbonPackages/Carbon.Plausible/fork
[stargazers]: https://github.com/CarbonPackages/Carbon.Plausible/stargazers
[subscription]: https://github.com/CarbonPackages/Carbon.Plausible/subscription
[screenshot of backend module as administrator]: https://user-images.githubusercontent.com/4510166/130530340-11ffb59c-ff27-4d61-8012-5ed4b67c6fcb.png
[screenshot of backend module as editor]: https://user-images.githubusercontent.com/4510166/130530343-478daf32-50a1-4051-8d01-a97c0f726622.png
[error in the backend module]: https://user-images.githubusercontent.com/4510166/130530345-41c17f25-014a-4d96-9a61-f0a67697f09e.png
[set options in the inspector]: https://user-images.githubusercontent.com/4510166/105755934-41248e00-5f4c-11eb-87dc-e4a4434943b0.gif
[neos]: https://www.neos.io
[plausible]: https://plausible.io
[plausible analytics tracking script code]: https://docs.plausible.io/plausible-script
[plausible domain]: https://docs.plausible.io/add-website
[carbon.plausible:mixin.customevent]: Configuration/NodeTypes.Mixin.CustomEvent.yaml
[carbon.plausible:mixin.donottrack]: Configuration/NodeTypes.Mixin.DoNotTrack.yaml
[custom events]: https://plausible.io/docs/custom-event-goals
[`settings.yaml`]: Configuration/Settings.Carbon.yaml
[custom domain]: https://plausible.io/docs/custom-domain
[excluding guide on plausible.io]: https://plausible.io/docs/excluding
[track clicks on outbound links]: https://plausible.io/docs/outbound-link-click-tracking
[hash-based routing]: https://plausible.io/docs/hash-based-routing
[exclude certain pages from being tracked]: https://plausible.io/docs/excluding-pages
[carbon.plausible:component.trackingcode]: Resources/Private/Fusion/Component/TrackingCode.fusion
[carbon.plausible:component.toggle]: Resources/Private/Fusion/Component/Toggle.fusion
[neos.neos:page]: Resources/Private/Fusion/Override/Page.fusion
[jshrink]: https://github.com/tedious/JShrink
[don't trigger pageviews automatically.]: https://plausible.io/docs/script-extensions#scriptmanualjs
[specify custom locations]: https://plausible.io/docs/custom-locations
[custom query parameters]: https://plausible.io/docs/custom-query-params
[track file downloads]: https://plausible.io/docs/file-downloads-tracking

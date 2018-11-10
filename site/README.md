## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build

# run unit tests
npm run unit

# run e2e tests
npm run e2e

# run all tests
npm test


## this is what i needed to do before the tests worked:
- https://stackoverflow.com/questions/45034581/laravel-5-4-cross-env-is-not-recognized-as-an-internal-or-external-command
- https://stackoverflow.com/questions/20800933/running-karma-after-installation-results-in-karma-is-not-recognized-as-an-inte

```

##Config

create api-config.js in the root of the cms project

```
export default {
    base_url: "http://localhost/lekkerland-loy/api/web/app_dev.php"
}
```

##Tests


// spreek de "data" objecten rechtstreeks aan


module.exports = {
    'init': function (browser) {
        // automatically uses dev Server port from /config.index.js
        // default: http://localhost:8080
        // see nightwatch.conf.js
        const devServer = browser.globals.devServerURL

        //maybe check inbouwen als de navbar ingeklapt is?
        browser
            .url(devServer)
            .waitForElementVisible('.app', 5000)
            // .assert.elementPresent('.container')
            .waitForElementVisible('input[name="email"]', 6000)
            .waitForElementVisible('input[name="password"]', 6000)
            //api-config firebase test user gebruiken?
            .execute(setLoginData, [['edwin.ten.brinke+admin@extendas.com','test12']])
            .waitForElementVisible('#btn-sign-in', 1000)
            .click('#btn-sign-in')
            //wait for dashboard
            .waitForElementVisible('.logout', 10000)

    },

    'navigation': function (browser) {
        browser
            .assert.containsText('.nav-link[href="#/dashboard"]', 'Dashboard')
            .assert.containsText('.nav-link[href="#/locations"]', 'Locaties')
            .assert.containsText('.nav-link[href="#/templates"]', 'Sjablonen')
            .assert.containsText('.nav-link[href="#/point_promotions"]', 'LekkerOpWeg acties')
            .assert.containsText('.nav-link[href="#/point_goals"]', 'Perfect acties')
    },

    'locations': function (browser) {
        browser
            .click('.nav-link[href="#/locations"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .assert.containsText('.card-header', 'Locaties')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
            .click('.btn-primary > .icon-plus')
            .execute(locationValues)
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.btn-warning > .fa-edit', 10000)
            .click('.btn-warning > .fa-edit')
            //wait for edit data to be loaded
            .waitForElementVisible('#tokheim_id', 10000)
            .execute(locationValues, [false])
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            //diffrent check because location edit page contains a data table
            .waitForElementVisible('.btn-success > .fa-plus', 10000)
    },
    'stamp_card': function (browser) {
        browser
            .click('.nav-link[href="#/locations"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .assert.containsText('.card-header', 'Locaties')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
            .waitForElementVisible('.btn-success > .fa-plus', 10000)
            .click('.btn-success')
            .waitForElementVisible('.dropdown-toggle', 10000)
            .execute(stampCardValues)
            .click('.dropdown-toggle')
            .waitForElementVisible('.highlight', 10000)
            .keys(browser.Keys.ENTER)
            .click('button[type="submit"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .waitForElementVisible('.btn-warning > .fa-edit', 10000)
            .click('.btn-warning > .fa-edit')
            //wait for edit data to be loaded
            .waitForElementVisible('#start_date', 10000)
            .execute(stampCardValues, [false])
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('#tokheim_id', 10000)

    },
    'templates': function (browser) {
        browser
            .click('.nav-link[href="#/templates"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .assert.containsText('.card-header', 'Sjablonen')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
            .click('.btn-primary > .icon-plus')
            .execute(templateValues)
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.btn-warning > .fa-edit', 10000)
            .click('.btn-warning > .fa-edit')
            .waitForElementVisible('#template_name', 10000)
            .execute(templateValues, [false])
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
    },
    'point_promotions': function (browser) {
        browser
            .click('.nav-link[href="#/point_promotions"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .assert.containsText('.card-header', 'LekkerOpWeg acties')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
            .click('.btn-primary > .icon-plus')
            .execute(pointPromotionValues)
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.btn-warning > .fa-edit', 10000)
            .click('.btn-warning > .fa-edit')
            .waitForElementVisible('#priority1', 10000)
            .execute(pointPromotionValues, [false])
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
    },
    'point_goals': function (browser) {
        browser
            .click('.nav-link[href="#/point_goals"]')
            .waitForElementVisible('.vgt-wrap', 10000)
            .assert.containsText('.card-header', 'Perfect acties')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)
            .click('.btn-primary > .icon-plus')
            .execute(pointGoalValues)
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.btn-warning > .fa-edit', 10000)
            .click('.btn-warning > .fa-edit')
            .waitForElementVisible('#priority', 10000)
            .execute(pointGoalValues, [false])
            .waitForElementVisible('button[type="submit"]', 10000)
            .click('button[type="submit"]')
            .waitForElementVisible('.vgt-table tbody tr td span', 10000)

    },

    // last test, also ends the browser
    'logout': function (browser) {
        browser
            .click(".logout")
            .waitForElementVisible('input[name="email"]', 6000)
            .end();
    }
};

let setLoginData = function (value) {
    window.Vue.$children[0].$children[0].email = value[0];
    window.Vue.$children[0].$children[0].password = value[1];
};

let locationValues = function (create = true) {
    if (create) {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data = {
            'latitude': 52.2215372,
            'longitude': 6.8936619,
            'name': 'test',
            'street': 'test straat',
            'street_number': '33',
            'zipcode': '7545ZT',
            'city': 'Enschede',
            'contact_name': 'piet',
            'segment': 'A',
            'contact_phone': '06-54254254',
            'contact_email': 'test@test.nl',
            'file_name': '12345678.TRX'
        }
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].place.formatted_address = 'test adress';
    } else {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data.name = 'test2';
    }
};

let stampCardValues = function (create = true) {
    if (create) {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].$children[0].data.start_date = '2018-04-01 06:49:00';
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].$children[0].data.end_date = '2018-04-30 06:49:00';
    } else {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].$children[0].data.start_date = '2018-04-01 06:49:00';
    }
};

let templateValues = function (create = true) {
    if( create ) {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data = {
            'banner_title': 'banner_title',
            'banner_text' : 'banner_text',
            'banner_button_text' : 'banner_button',
            'banner_image' : 'banner_image',
            'banner_background_color' : '#ffffff',
            'banner_text_color' : '#000000',
            'activation_barcode' : '1232136712721',
            'stamp_rate' : 0.5,
            'stamp_unit' : 1,
            'name' : 'name',
            'terms' : 'terms',
            'stamp_image' : 'stamp_image',
            'stamp_empty_image' : 'stamp_empty_image',
            'background_color' : 'background_color',
            'text_color' : 'text_color',
            'product_barcodes' : [
                {'barcode' : '987654321'},
                {'barcode' : '087654321'},
            ]
        };
    } else {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data.name = 'name2';
    }

};

let pointPromotionValues = function (create = true) {
    if ( create ) {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data = {
            'priority': 1,
            'segment': 'A',
            'banner_title': 'banner_title',
            'banner_text': 'banner_text',
            'banner_button_text': 'banner_button',
            'banner_image': 'banner_image',
            'banner_background_color': '#ffffff',
            'banner_text_color': '#000000',
            'image': 'http://blabla.bla/image',
            'point_rate': 1,
            'point_unit': 0,
            'start_date': '2018-01-01 10:10:10',
            'end_date': '2099-01-01 10:10:10',
            'duration': 0,
            'description': 'back side',
            'product_barcodes': [
                {'barcode': '123456789'},
                {'barcode': '023456789'},
            ]
        };
    } else {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data.description = 'blabla';
    }
};

let pointGoalValues = function (create = true) {
    if ( create ) {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data = {
            'priority': 1,
            'banner_title' : 'banner_title',
            'banner_text' : 'banner_text',
            'banner_button_text' : 'banner_button',
            'banner_image'  : 'banner_image',
            'banner_background_color' : '#ffffff',
            'banner_text_color' : '#000000',
            'title' : 'title',
            'name' : 'intern name',
            'start_date' : '2018-01-01 10:10:10',
            'end_date' : '2099-01-01 10:10:10',
            'discount' : 0,
            'description' : 'description',
            'detail_image' : 'detail_image_href',
            'points_needed' : 10,
            'amount_extra_pay' : 10,
            'vouchers' : [
                {'description' : 'test', 'needed_points' : 5, 'perfect_id' : '1asd33fa' },
                {'description' : 'test2', 'needed_points' : 15.5, 'perfect_id' : 'afalj1411'},
            ]
        };
    } else {
        window.Vue.$root.$children[0].$children[0].$children[5].$children[0].$children[0].data.description = 'intern name2';
    }
};
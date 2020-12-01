window.Vue = require("vue");
var moment = require("moment");

Vue.filter('timeformat', function (arg) {
    return moment(arg).format('MMMM Do YYYY, h:mm:ss a')
})

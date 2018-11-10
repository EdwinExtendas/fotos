import Vue from "vue"

Vue.filter(
    "dateToLocaleString",
    str => new Date(Date.parse(str)).toLocaleString()
);

//Just to ignore a stupid warning in console
export default {}
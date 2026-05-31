import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";

const dialogManager = new FormDialogManager();
const map = new Map<string, any>();
for (let i = 0; i < 100; i++) {
    map.set("ABC " + i, i)
}
dialogManager.ShowCheckboxSelect("TEST", "TEST", null, (v) => {
    console.log(v);

}, map, { checkboxSelectMinCount: 1, checkboxSelectMaxCount: 1 })

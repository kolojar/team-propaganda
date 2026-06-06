import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
const dialogManager = new FormDialogManager();
const map = new Map();
for (let i = 0; i < 1000; i++) {
    map.set("ABC " + i, { value: i });
}
dialogManager.ShowCheckboxSelect("TEST", "TEST", null, (v) => {
    console.log(v);
}, map, { checkboxSelectMinCount: 2 });
//# sourceMappingURL=admin.js.map
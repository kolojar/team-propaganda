import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
const dialogManager = new FormDialogManager();
const map = new Map();
for (let i = 0; i < 100; i++) {
    map.set("ABC " + i, { value: i });
}
dialogManager.ShowSelect("TEST", "TEST", null, (v) => {
    console.log(v);
}, map, { alwaysShownOptions: ["ABC 1"] });
//# sourceMappingURL=admin.js.map
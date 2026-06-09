import { FormDialogCheckboxSelectData, FormDialogManager } from "../formWebScripts/js/formDialogScript.js";

const dialogManager = new FormDialogManager();
const map  = new Map<string,FormDialogCheckboxSelectData<number>>();
for (let i = 0; i < 100; i++) {
    map.set("ABC " + i,{value: i})    
}
dialogManager.ShowSelect("TEST", "TEST", null, (v) => {
    console.log(v);

},map,{alwaysShownOptions: ["ABC 1"]})

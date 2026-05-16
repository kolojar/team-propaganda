import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(dialogManager, "subeventValidate", "./events.php", "./subevent.php", urlSearchParams.get("subevent"));
//Setup minimums and maximums
const startTime = document.getElementById("start_time");
const endTime = document.getElementById("end_time");
const date = document.getElementById("date");
startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue());
});
date.addEventListener("validation-done", () => {
    console.log(date.getValue() == date.getMinimum());
    if (date.getValue() == date.getMinimum()) {
        startTime.setMinimum(date.getAttribute("minTime"));
    }
    else {
        startTime.setMinimum("");
    }
    if (date.getValue() == date.getMaximum()) {
        endTime.setMaximum(date.getAttribute("maxTime"));
    }
    else {
        endTime.setMaximum("");
    }
});
date.validate();
//# sourceMappingURL=subevent.js.map
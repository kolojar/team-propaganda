import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupSaveCancelButtons(dialogManager, "subeventValidate", "./events.php", "./subevent.php", urlSearchParams.get("subevent"));
//Setup minimums and maximums
const startTime = document.getElementById("start_time");
const endTime = document.getElementById("end_time");
startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue());
});
//# sourceMappingURL=subevent.js.map
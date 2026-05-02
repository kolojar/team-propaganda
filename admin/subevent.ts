import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement } from "../formWebScripts/js/formScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
setupSaveCancelButtons(dialogManager, "subeventValidate", "./events.php", "./subevent.php", urlSearchParams.get("subevent") as string)

//Setup minimums and maximums
const startTime = (document.getElementById("start_time") as HTMLFormInputElement)
const endTime = (document.getElementById("end_time") as HTMLFormInputElement)

startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue())
})
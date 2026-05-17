import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager, null, "./events.php", "./subevent.php", urlSearchParams.get("subevent") as string,"subeventValidate")

//Setup minimums and maximums
const startTime = (document.getElementById("start_time") as HTMLFormInputElement)
const endTime = (document.getElementById("end_time") as HTMLFormInputElement)
const date = (document.getElementById("date") as HTMLFormInputElement)

startTime.addEventListener("validation-done", () => {
    endTime.setMinimum(startTime.getValue())
})
date.addEventListener("validation-done",() =>{
    console.log(date.getValue() == date.getMinimum());
    if(date.getValue() == date.getMinimum()) {
        startTime.setMinimum(date.getAttribute("minTime") as string)
    } else {
        startTime.setMinimum("")
    }
    if(date.getValue() == date.getMaximum()) {
        endTime.setMaximum(date.getAttribute("maxTime") as string)
    } else {
        endTime.setMaximum("")
    }
})
date.validate()
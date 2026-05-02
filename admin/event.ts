import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement } from "../formWebScripts/js/formScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
setupSaveCancelButtons(dialogManager, "eventValidate", "./events.php", "./event.php", urlSearchParams.get("event") as string)

//Setup minimums and maximums
const activeSince = (document.getElementById("active_since") as HTMLFormInputElement)
const activeUntil = (document.getElementById("active_until") as HTMLFormInputElement)
const registrationOpen = (document.getElementById("registration_open") as HTMLFormInputElement)
const registrationClose = (document.getElementById("registration_close") as HTMLFormInputElement)
const repeatStart = (document.getElementById("repeat_start") as HTMLFormInputElement)
console.log(repeatStart);

activeSince.addEventListener("validation-done", () => {
    activeUntil.setMinimum(activeSince.getValue())
    registrationOpen.setMinimum(activeSince.getValue())
    repeatStart.setMinimum(activeSince.getValue()+"T00:00")
})
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.setMaximum(activeUntil.getValue())
    registrationClose.setMaximum(activeUntil.getValue())
    repeatStart.setMaximum(activeUntil.getValue()+"T23:59")
})
registrationOpen.addEventListener("validation-done", () => {
    registrationClose.setMinimum(registrationOpen.getValue())
})
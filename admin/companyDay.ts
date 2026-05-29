import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager, null, "./events.php", "./companyDay.php", urlSearchParams.get("companyDay") as string, "eventValidate", null)

//Setup minimums and maximums
const activeSince = (document.getElementById("active_since") as HTMLFormInputElement)
const activeUntil = (document.getElementById("active_until") as HTMLFormInputElement)
const registrationOpen = (document.getElementById("registration_open") as HTMLFormInputElement)
const registrationClose = (document.getElementById("registration_close") as HTMLFormInputElement)
const date = document.getElementById("date") as HTMLFormInputElement;

activeSince.addEventListener("validation-done", () => {
    activeUntil.min = (activeSince.value)
    registrationOpen.min = (activeSince.value)
})
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.max = (activeUntil.value)
    registrationClose.max = (activeUntil.value)
    date.max = (activeUntil.value.split("T",2)[0]);
})
registrationOpen.addEventListener("validation-done", () => {
    registrationClose.min = (registrationOpen.value)
})
registrationClose.addEventListener("validation-done", () => {
    date.min = (registrationClose.value.split("T",2)[0])
})
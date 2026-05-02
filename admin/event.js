import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { setupSaveCancelButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupSaveCancelButtons(dialogManager, "eventValidate", "./events.php", "./event.php", urlSearchParams.get("event"));
//Setup minimums and maximums
const activeSince = document.getElementById("active_since");
const activeUntil = document.getElementById("active_until");
const registrationOpen = document.getElementById("registration_open");
const registrationClose = document.getElementById("registration_close");
const repeatStart = document.getElementById("repeat_start");
console.log(repeatStart);
activeSince.addEventListener("validation-done", () => {
    activeUntil.setMinimum(activeSince.getValue());
    registrationOpen.setMinimum(activeSince.getValue());
    repeatStart.setMinimum(activeSince.getValue() + "T00:00");
});
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.setMaximum(activeUntil.getValue());
    registrationClose.setMaximum(activeUntil.getValue());
    repeatStart.setMaximum(activeUntil.getValue() + "T23:59");
});
registrationOpen.addEventListener("validation-done", () => {
    registrationClose.setMinimum(registrationOpen.getValue());
});
//# sourceMappingURL=event.js.map
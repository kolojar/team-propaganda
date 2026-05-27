import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)
SetupSaveCancelButtons(dialogManager, null, "./events.php", "./event.php", urlSearchParams.get("event") as string, "eventValidate", onSaveFunc)

//Setup minimums and maximums
const activeSince = (document.getElementById("active_since") as HTMLFormInputElement)
const activeUntil = (document.getElementById("active_until") as HTMLFormInputElement)
const registrationOpen = (document.getElementById("registration_open") as HTMLFormInputElement)
const registrationClose = (document.getElementById("registration_close") as HTMLFormInputElement)
const repeatStart = (document.getElementById("repeat_start") as HTMLFormInputElement)

activeSince.addEventListener("validation-done", () => {
    const value = activeSince.getValue()
    activeUntil.setMinimum(new Date(value) <= new Date() ? activeUntil.getValue() : value)
    const value2 = registrationOpen.getValue()
    registrationOpen.setMinimum(new Date(value2) <= new Date() ? value2 : value)
    repeatStart.setMinimum(value + "T00:00")
})
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.setMaximum(activeUntil.getValue())
    registrationClose.setMaximum(activeUntil.getValue())
    repeatStart.setMaximum(activeUntil.getValue() + "T23:59")
})
registrationOpen.addEventListener("validation-done", () => {
    const value = registrationOpen.getValue();
    registrationClose.setMinimum(new Date(value) <= new Date() ? registrationClose.getValue() : value)
})

async function onSaveFunc(): Promise<boolean> {
    const price = document.getElementById("price") as HTMLFormInputElement;
    const [changed, _] = await price.validate();
    if (changed) {
        const currentTime = new Date()
        if (new Date(activeSince.getValue()) <= currentTime && currentTime <= new Date(activeUntil.getValue())) {
            SendToast("Nelze uložit změny!", "Nelze upravit cenu, pokud je událost aktivní.", "error")
            return Promise.resolve(false)
        }
    }
    return Promise.resolve(true)
}
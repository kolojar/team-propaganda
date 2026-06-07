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
    const value = activeSince.value
    activeUntil.min = (new Date(value) <= new Date() ? activeUntil.value : value)
    const value2 = registrationOpen.value
    registrationOpen.min = (new Date(value2) <= new Date() ? value2 : value)
    repeatStart.min = (value + "T00:00")
})
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.max = (activeUntil.value)
    registrationClose.max = (activeUntil.value)
    repeatStart.max = (activeUntil.value + "T23:59")
})
registrationOpen.addEventListener("validation-done", () => {
    const value = registrationOpen.value;
    registrationClose.min = (new Date(value) <= new Date() ? registrationClose.value : value)
})

async function onSaveFunc(): Promise<boolean> {
    const price = document.getElementById("price") as HTMLFormInputElement;
    const [changed, _a,_b] = await price.validate();
    if (changed) {
        const currentTime = new Date()
        if (new Date(activeSince.value) <= currentTime && currentTime <= new Date(activeUntil.value)) {
            SendToast("Nelze uložit změny!", "Nelze upravit cenu, pokud je událost aktivní.", "error")
            return Promise.resolve(false)
        }
    }
    return Promise.resolve(true)
}
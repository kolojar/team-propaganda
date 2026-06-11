import { SendToast } from "../formWebScripts/js/formScript.js";
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(null, "./events.php", "./event.php", urlSearchParams.get("event"), "eventValidate", onSaveFunc);
//Setup minimums and maximums
const activeSince = document.getElementById("active_since");
const activeUntil = document.getElementById("active_until");
const registrationOpen = document.getElementById("registration_open");
const registrationClose = document.getElementById("registration_close");
const repeatStart = document.getElementById("repeat_start");
activeSince.addEventListener("validation-done", () => {
    const value = activeSince.value;
    activeUntil.min = (new Date(value) <= new Date() ? activeUntil.value : value);
    const value2 = registrationOpen.value;
    registrationOpen.min = (new Date(value2) <= new Date() ? value2 : value);
    repeatStart.min = (value + "T00:00");
});
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.max = (activeUntil.value);
    registrationClose.max = (activeUntil.value);
    repeatStart.max = (activeUntil.value + "T23:59");
});
registrationOpen.addEventListener("validation-done", () => {
    const value = registrationOpen.value;
    registrationClose.min = (new Date(value) <= new Date() ? registrationClose.value : value);
});
async function onSaveFunc() {
    const price = document.getElementById("price");
    const [changed, _a, _b] = await price.validate();
    if (changed) {
        const currentTime = new Date();
        if (new Date(activeSince.value) <= currentTime && currentTime <= new Date(activeUntil.value)) {
            SendToast("Nelze uložit změny!", "Nelze upravit cenu, pokud je událost aktivní.", "error");
            return Promise.resolve(false);
        }
    }
    return Promise.resolve(true);
}
//# sourceMappingURL=event.js.map
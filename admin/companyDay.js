import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(null, "./events.php", "./companyDay.php", urlSearchParams.get("companyDay"), "eventValidate", null);
//Setup minimums and maximums
const activeSince = document.getElementById("active_since");
const activeUntil = document.getElementById("active_until");
const registrationOpen = document.getElementById("registration_open");
const registrationClose = document.getElementById("registration_close");
const date = document.getElementById("date");
activeSince.addEventListener("validation-done", () => {
    activeUntil.min = (activeSince.value);
    registrationOpen.min = (activeSince.value);
});
activeUntil.addEventListener("validation-done", () => {
    registrationOpen.max = (activeUntil.value);
    registrationClose.max = (activeUntil.value);
    date.max = (activeUntil.value.split("T", 2)[0]);
});
registrationOpen.addEventListener("validation-done", () => {
    registrationClose.min = (registrationOpen.value);
});
registrationClose.addEventListener("validation-done", () => {
    date.min = (registrationClose.value.split("T", 2)[0]);
});
//# sourceMappingURL=companyDay.js.map
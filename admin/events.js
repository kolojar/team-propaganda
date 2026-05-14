import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { setupTableDeleteButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
setupTableDeleteButtons(dialogManager, "./event.php", "event");
setupTableDeleteButtons(dialogManager, "./subevent.php", "subevent");
if (urlSearchParams.has("noEventId")) {
    SendToast("Nastavení události", "Není vybrána žádná událost!", "error");
}
if (urlSearchParams.has("noSubeventId")) {
    SendToast("Nastavení události", "Není vybrána žádná podudálost!", "error");
}
//Setup btnTableSelectEvent buttons
//for (const button of document.getElementsByClassName("btnTableSelectEvent")) {
//    button.addEventListener("click",() => {
//        
//    })
//}
//# sourceMappingURL=events.js.map
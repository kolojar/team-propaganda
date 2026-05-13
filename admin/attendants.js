import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { setupTableDeleteButtons } from "./sharedScripts.js";
const dialogManager = new FormDialogManager();
setupTableDeleteButtons(dialogManager, "./attendant.php", "attendant");
for (const button of document.getElementsByClassName("btnTableAddPayment")) {
    button.addEventListener("click", async () => {
        //Get bank account
        const bankAccount = await dialogManager.OpenPrompt("Zaplatit", "Zadejte číslo účtu pro případné vrácení peněz.", null, "text", "Číslo účtu", true, false);
        if (bankAccount == null) {
            SendToast("Zadat platbu", "Zadání platby bylo zrušeno.", "info");
            return;
        }
        //Get pay date
        const datePaid = await dialogManager.OpenPrompt("Zaplatit", "Zadejte datum provedení platby.", null, "datetime-local", "Datum platby", true, false);
        if (datePaid == null) {
            SendToast("Zadat platbu", "Zadání platby bylo zrušeno.", "info");
            return;
        }
        //Send request to PHP
        const progress = dialogManager.ShowProgress("Zadat platbu", "Probíhá odesílání dat na server, čekejte prosím...", () => { }, 0, false, true, true);
        const data = new FormData();
        data.set("action", "addPayment");
        data.set("bank_account", bankAccount);
        data.set("paid", datePaid);
        data.set("id", button.getAttribute("variableSymbol"));
        data.set("unregistered", button.hasAttribute("unregistered") ? "1" : "0");
        const [ok, responce] = await SendPOSTDataToServerAsync("./attendant.php", data);
        if (!ok) {
            progress.CloseDialog();
            SendToast("Zadat platbu", "Platbu se nepodařilo zadat!", "error");
            return;
        }
        SendToast("Zadat platbu", "Platbu uložena!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
for (const button of document.getElementsByClassName("btnRefundTable")) {
    button.addEventListener("click", () => {
        dialogManager.ShowConfirm("Opravdu chcete vrátit platbu?", "Číslo účtu: " + button.getAttribute("bankAccount") + "\nVariabilní symbol: " + button.getAttribute("variableSymbol") + "\nČástka: " + button.getAttribute("price") + " Kč", async (refund) => {
            if (!refund) {
                SendToast("Vrátit platbu", "Vrácení platby bylo zrušeno.", "info");
                return;
            }
            //Send XHR
            const progress = dialogManager.ShowProgress("Vrátit platbu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
            const formData = new FormData();
            formData.set("action", "removePayment");
            formData.set("id", button.getAttribute("variableSymbol"));
            const [ok, _] = await SendPOSTDataToServerAsync("./attendant.php", formData);
            if (!ok) {
                progress.CloseDialog();
                SendToast("Vrátit platbu", "Nepodařilo se vrátit platbu!", "error");
                return;
            }
            SendToast("Vrátit platbu", "Platba vrácena!", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }).AllowSelect(true);
    });
}
//# sourceMappingURL=attendants.js.map
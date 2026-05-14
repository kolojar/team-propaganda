import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
for (const button of document.getElementsByClassName("btnUnregisterTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.OpenConfirm("Odhlásit zájemce", "Opravdu chcete odhlásit zájemce?", true, true)) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info");
            return;
        }
        const reason = await dialogManager.OpenPrompt("Odhlásit zájemce", "Zadejte důvod odhlášení.", null, "text", "Důvod odhlášení", true, true);
        if (reason == null) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info");
            return;
        }
        //Send XHR
        const progress = dialogManager.ShowProgress("Odhlásit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
        const formData = new FormData();
        formData.set("action", "unregister");
        formData.set("id", button.getAttribute("variableSymbol"));
        formData.set("reason", reason);
        const [ok, _] = await SendPOSTDataToServerAsync("./attendant.php", formData);
        if (!ok) {
            progress.CloseDialog();
            SendToast("Odhlásit zájemce", "Nepodařilo se vrátit platbu!", "error");
            return;
        }
        SendToast("Odhlásit zájemce", "Zájemce odhlášen!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
for (const button of document.getElementsByClassName("btnDeleteTotalTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.OpenConfirm("Odstranit zájemce", "Opravdu chcete odstranit zájemce?", true, true)) {
            SendToast("Odstranit zájemce", "Odstranění zájmece zrušeno.", "info");
            return;
        }
        //Send XHR
        const progress = dialogManager.ShowProgress("Odstranit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
        const formData = new FormData();
        formData.set("action", "delete");
        formData.set("id", button.getAttribute("variableSymbol"));
        const [ok, _] = await SendPOSTDataToServerAsync("./attendant.php", formData);
        if (!ok) {
            progress.CloseDialog();
            SendToast("Odstranit zájemce", "Nepodařilo se odstranit zájemce!", "error");
            return;
        }
        SendToast("Odstranit zájemce", "Zájemce odstraněn!", "ok");
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    });
}
//# sourceMappingURL=attendants.js.map
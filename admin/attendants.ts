import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
import { setupTableDeleteButtons } from "../assets/sharedScripts.js";

const dialogManager = new FormDialogManager()
for (const button of document.getElementsByClassName("btnUnregisterTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odhlásit zájemce", "Opravdu chcete odhlásit zájemce?")) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info")
            return
        }
        const reason = await dialogManager.ShowPromptAsync<string | null>("Odhlásit zájemce", "Zadejte důvod odhlášení.", null, "text",{placeholder: "Důvod odhlášení"})
        if (reason == null) {
            SendToast("Odhlásit zájemce", "Odhlášení zájmece zrušeno.", "info")
            return
        }

        //Send XHR
        const progress = dialogManager.ShowProgress("Odhlásit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
        const formData = new FormData();
        formData.set("action", "unregister")
        formData.set("id", button.getAttribute("variableSymbol") as string)
        formData.set("reason", reason)
        const [ok, responce] = await SendPOSTDataToServerAsync("./payments.php", formData)
        if (!ok) {
            progress?.CloseDialog()
            SendToast("Odhlásit zájemce", "Nepodařilo se odhlásit zájemce!", "error")
            await dialogManager.ShowAlertAsync("Odhlásit zájemce","Nepodařilo se odhlásit zájemce, zkuste to prosím znovu a později.<br>Důvod: " + responce )
            return
        }
        SendToast("Odhlásit zájemce", "Zájemce odhlášen!", "ok")
        setTimeout(() => {
            window.location.reload()
        }, 1000)
    })
}

for (const button of document.getElementsByClassName("btnDeleteTotalTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odstranit zájemce", "Opravdu chcete odstranit zájemce?")) {
            SendToast("Odstranit zájemce", "Odstranění zájmece zrušeno.", "info")
            return
        }

        //Send XHR
        const progress = dialogManager.ShowProgress("Odstranit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
        const formData = new FormData();
        formData.set("action", "delete")
        formData.set("id", button.getAttribute("variableSymbol") as string)
        const [ok, _] = await SendPOSTDataToServerAsync("./attendant.php", formData)
        if (!ok) {
            progress?.CloseDialog()
            SendToast("Odstranit zájemce", "Nepodařilo se odstranit zájemce!", "error")
            return
        }
        SendToast("Odstranit zájemce", "Zájemce odstraněn!", "ok")
        setTimeout(() => {
            window.location.reload()
        }, 1000)
    })
}
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

for (const button of document.getElementsByClassName("btnDeleteTotalTable")) {
    button.addEventListener("click", async () => {
        if (!await GlobalDialogManager.ShowConfirmAsync("Odstranit zájemce", "Opravdu chcete odstranit zájemce?")) {
            SendToast("Odstranit zájemce", "Odstranění zájmece zrušeno.", "info")
            return
        }

        //Send XHR
        const progress = GlobalDialogManager.ShowProgress("Odstranit zájemce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
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
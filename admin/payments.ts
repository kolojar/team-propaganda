import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager()
for (const button of document.getElementsByClassName("btnTableAddPayment")) {
    button.addEventListener("click", async () => {
        //Get bank account
        const bankAccount = await dialogManager.OpenPrompt<null | string>("Zaplatit", "Zadejte číslo účtu pro případné vrácení peněz.", null, "text", "Číslo účtu", true, false)
        if (bankAccount == null) {
            SendToast("Zadat platbu", "Zadání platby bylo zrušeno.", "info")
            return
        }

        //Get pay date
        const datePaid = await dialogManager.OpenPrompt<null | string>("Zaplatit", "Zadejte datum provedení platby.", null, "datetime-local", "Datum platby", true, false)
        if (datePaid == null) {
            SendToast("Zadat platbu", "Zadání platby bylo zrušeno.", "info")
            return
        }

        //Send request to PHP
        const progress = dialogManager.ShowProgress("Zadat platbu", "Probíhá odesílání dat na server, čekejte prosím...", () => { }, 0, false, true, true)
        const data = new FormData();
        data.set("action", "addPayment")
        data.set("bank_account", bankAccount);
        data.set("paid", datePaid);
        data.set("id", button.getAttribute("variableSymbol") as string);
        data.set("email", button.getAttribute("email") as string);
        data.set("id_events", button.getAttribute("id-events") as string);
        data.set("unregistered", button.hasAttribute("unregistered") ? "1" : "0")
        const [ok, responce] = await SendPOSTDataToServerAsync("./payments.php", data)
        if (!ok) {
            progress.CloseDialog()
            SendToast("Zadat platbu", "Platbu se nepodařilo zadat!", "error")
            return
        }
        SendToast("Zadat platbu", "Platba uložena!", "ok")
        setTimeout(() => {
            window.location.reload();
        }, 1000)
    })
}

for (const button of document.getElementsByClassName("btnRefundTable")) {
    button.addEventListener("click", () => {
        dialogManager.ShowConfirm("Opravdu chcete vrátit platbu?", "Číslo účtu: " + button.getAttribute("bankAccount") + "<br>Variabilní symbol: " + button.getAttribute("variableSymbol") + "<br>Částka: " + button.getAttribute("price") + " Kč", async (refund: boolean) => {
            if (!refund) {
                SendToast("Vrátit platbu", "Vrácení platby bylo zrušeno.", "info")
                return
            }

            //Send XHR
            const progress = dialogManager.ShowProgress("Vrátit platbu", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
            const formData = new FormData();
            formData.set("action", "removePayment")
            formData.set("id", button.getAttribute("variableSymbol") as string)
            const [ok, _] = await SendPOSTDataToServerAsync("./payments.php", formData)
            if (!ok) {
                progress.CloseDialog()
                SendToast("Vrátit platbu", "Nepodařilo se vrátit platbu!", "error")
                return
            }
            SendToast("Vrátit platbu", "Platba vrácena!", "ok")
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        }).AllowSelect(true)
    })
}

for (const button of document.getElementsByClassName("btnRemoveNotPaidTable")) {
    button.addEventListener("click", async () => {
        if (!await dialogManager.OpenConfirm("Platba nedorazila", "Opravdu platba nedorazila? Někdy to může trvat několik dní.", true, true)) {
            SendToast("Platba nedorazila", "Systém bude nadále vyčkávat.", "info")
            return
        }

        //Send XHR
        const progress = dialogManager.ShowProgress("Platba nedorazila", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
        const formData = new FormData();
        formData.set("action", "removePayment")
        formData.set("id", button.getAttribute("variableSymbol") as string)
        const [ok, _] = await SendPOSTDataToServerAsync("./payment.php", formData)
        if (!ok) {
            progress.CloseDialog()
            SendToast("Platba nedorazila", "Přeřazení zájemce nebylo úspěšné!", "error")
            return
        }
        SendToast("Platba nedorazila", "Přeřazení zájemce bylo úspěšné!", "ok")
        setTimeout(() => {
            window.location.reload()
        }, 1000)
    })
}

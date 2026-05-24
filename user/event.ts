import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search)
const btnPay = document.getElementById("btnPay")
btnPay?.addEventListener("click", async () => {
    //Get payment info from server
    const progress = dialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false, true, true)
    const formData = new FormData()
    formData.set("action", "getBankAccount")
    const [ok, responce] = await SendPOSTDataToServerAsync("../settingsManager.php", formData)
    progress.CloseDialog();
    if (!ok) {
        SendToast("Načítání dat selhalo", "Data nemohla být načtena.", "error")
        dialogManager.ShowConfirm("Načítání dat selhalo", "Nelze načíst data, opakujte akci později.", () => { }, true, true)
        return
    }

    //Get bank account
    const bankAccount = await dialogManager.OpenPrompt<null | string>("Zaplatit", "Zadejte číslo účtu pro případné vrácení peněz.", null, "text", "Číslo účtu", true, false)
    if (bankAccount == null) {
        SendToast("Zaplatit", "Zadání platby bylo zrušeno.", "info")
        return
    }

    //Show info for payment
    dialogManager.ShowConfirm("Odešlete prosím platbu na následující účet", "Číslo účtu: <span class='fontMono'>" + responce + "</span><br>Variabilní symbol: <span class='fontMono'>" + btnPay.getAttribute("variableSymbol") + "</span><br>Částka: <span class='fontMono'>" + btnPay?.getAttribute("price") + "</span> Kč<br>Číslo účtu pro případné vrácení peněz: <span class='fontMono'>" + bankAccount + "</span>", async (pay: boolean) => {
        if (!pay) {
            SendToast("Zaplatit", "Zaplacení platby bylo zrušeno.", "info")
            return
        }

        //Write to DB
        const progress2 = dialogManager.ShowProgress("Zaplatit", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
        const formData2 = new FormData()
        formData2.set("action", "addPayment")
        formData2.set("bank_account", bankAccount)
        formData2.set("variable_symbol", urlSearchParams.get("variableSymbol") as string);
        const [ok2, responce2] = await SendPOSTDataToServerAsync("./event.php", formData2)
        progress2.CloseDialog()
        if (ok2) {
            SendToast("Zaplatit", "Platba přidána úspěšně.", "ok")
            await dialogManager.OpenAlert("Zaplatit", "Platba přidána úspěšně, nyní prosíme vyčkejte na její zpracování.", true, true)
            window.location.reload()
        } else {
            SendToast("Zaplatit", "Informace o zaplacení nemohly být uloženy.", "error")
            await dialogManager.OpenAlert("Zaplatit", "Informace o zaplacení nemohly být uloženy.<br>Prosíme, pokud jste pladbu již odeslali, aby jste počkali několik dní.<br>Pokud se platba nezapočítá do několika dní, kontaktujte nás.", true, true)
        }
    }).AllowSelect(true)
})

document.getElementById("btnRemoveAttendant")?.addEventListener("click", async () => {
    //Get confirmation
    const reason = await dialogManager.OpenPrompt<string|null>("Odhlásit zájemce z akce?", "Opravdu chcete odhlásit zájemce z akce? Tento krok nelze vzít zpět. Sdělte nám prosím důvod.",null,"textarea","Zadejte důvod odhlášení", true, true)
    if (reason == null) {
        SendToast("Odhlásit zájemce z akce", "Odlášení bylo zrušeno - zájemce zůstává přihlášený.", "info")
        return
    }

    //Write to DB
    const progress = dialogManager.ShowProgress("Odhlásit zájemce z akce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
    const formData = new FormData();
    formData.set("action", "unregisterFromEvent")
    formData.set("id", urlSearchParams.get("variableSymbol") as string)
    formData.set("reason",reason)
    const [ok, responce] = await SendPOSTDataToServerAsync("./event.php", formData)
    progress.CloseDialog()
    if (ok) {
        SendToast("Odhlásit zájemce z akce", "Zájemce odhlášen úspěšně.", "ok")
        await dialogManager.OpenAlert("Zaplatit", "Zájemce odhlášen úspěšně. Pokud jste provedli pladbu, tak prosíme vyčkejte na její vrácení.", true, true)
        window.location.href = "./index.php"
    } else {
        SendToast("Odhlásit zájemce z akce", "Zájemce nemohl být odhlášen.", "error")
        await dialogManager.OpenAlert("Odhlásit zájemce z akce", "Informace o odhlášení nemohly být uloženy, opakujte akci později.", true, true)
    }
})
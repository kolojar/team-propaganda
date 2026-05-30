import { SetupSaveCancelButtons } from "../assets/sharedScripts.js"
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js"
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search)

for (const element of document.getElementsByClassName("siteInfo")) {
    SetupSaveCancelButtons(dialogManager, element as HTMLElement, ".", "./site.php", element.getAttribute("site") as string)
}

for (let element of document.getElementsByClassName("rmSite")) {
    element.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odstranit stánek", "Opravdu chcete smazat tento stánek?")) {
            SendToast("Odstranit stánek", "Odstranění stánek bylo zrušeno.", "info")
            return
        }
        let data = new FormData()
        data.append("action", "delete")
        data.append("id", element.getAttribute("site") as string)
        let [ok, res] = await SendPOSTDataToServerAsync("./site.php", data)
        if (ok) {
            SendToast("Odpověď serveru.", res, "ok")
            window.location.reload()
        } else {
            SendToast("Odpověď serveru", res, "error")
        }
    })
}

const btnPay = document.getElementById("btnPay")
btnPay?.addEventListener("click", async () => {
    //Get payment info from server
    const progress = dialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false)
    const formData = new FormData()
    formData.set("action", "getBankAccount")
    const [ok, responce] = await SendPOSTDataToServerAsync("../settingsManager.php", formData)
    progress?.CloseDialog();
    if (!ok) {
        SendToast("Načítání dat selhalo", "Data nemohla být načtena.", "error")
        dialogManager.ShowConfirm("Načítání dat selhalo", "Nelze načíst data, opakujte akci později.", () => { })
        return
    }

    //Get bank account
    const bankAccount = await dialogManager.ShowPromptAsync<null | string>("Zaplatit", "Zadejte číslo účtu pro případné vrácení peněz.", null, "text",{placeholder: "Číslo účtu"})
    if (bankAccount == null) {
        SendToast("Zaplatit", "Zadání platby bylo zrušeno.", "info")
        return
    }

    //Show info for payment
    if(! await dialogManager.ShowConfirmAsync("Odešlete prosím platbu na následující účet", "Číslo účtu: <span class='fontMono'>" + responce + "</span><br>Variabilní symbol: <span class='fontMono'>" + btnPay.getAttribute("variableSymbol") + "</span><br>Částka: <span class='fontMono'>" + btnPay?.getAttribute("price") + "</span> Kč<br>Číslo účtu pro případné vrácení peněz: <span class='fontMono'>" + bankAccount + "</span>",{allowSelect: true})) {
            SendToast("Zaplatit", "Zaplacení platby bylo zrušeno.", "info")
            return
        }

        //Write to DB
        const progress2 = dialogManager.ShowProgress("Zaplatit", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
        const formData2 = new FormData()
        formData2.set("action", "addPayment")
        formData2.set("bank_account", bankAccount)
        formData2.set("variable_symbol", urlSearchParams.get("variableSymbol") as string);
        const [ok2, responce2] = await SendPOSTDataToServerAsync("./event.php", formData2)
        progress2?.CloseDialog()
        if (ok2) {
            SendToast("Zaplatit", "Platba přidána úspěšně.", "ok")
            await dialogManager.ShowAlertAsync("Zaplatit", "Platba přidána úspěšně, nyní prosíme vyčkejte na její zpracování.")
            window.location.reload()
        } else {
            SendToast("Zaplatit", "Informace o zaplacení nemohly být uloženy.", "error")
            await dialogManager.ShowAlertAsync("Zaplatit", "Informace o zaplacení nemohly být uloženy.<br>Prosíme, pokud jste pladbu již odeslali, aby jste počkali několik dní.<br>Pokud se platba nezapočítá do několika dní, kontaktujte nás.")
        }
})

document.getElementById("btnRemoveAttendant")?.addEventListener("click", async () => {
    //Get confirmation
    const reason = await dialogManager.ShowPromptAsync<string | null>("Odhlásit zájemce z akce?", "Opravdu chcete odhlásit zájemce z akce? Tento krok nelze vzít zpět. Sdělte nám prosím důvod.", null, "textarea", {placeholder:"Zadejte důvod odhlášení"})
    if (reason == null) {
        SendToast("Odhlásit zájemce z akce", "Odlášení bylo zrušeno - zájemce zůstává přihlášený.", "info")
        return
    }

    //Write to DB
    const progress = dialogManager.ShowProgress("Odhlásit zájemce z akce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)
    const formData = new FormData();
    formData.set("action", "unregisterFromEvent")
    formData.set("id", urlSearchParams.get("variableSymbol") as string)
    formData.set("reason", reason)
    const [ok, responce] = await SendPOSTDataToServerAsync("./event.php", formData)
    progress?.CloseDialog()
    if (ok) {
        SendToast("Odhlásit zájemce z akce", "Zájemce odhlášen úspěšně.", "ok")
        await dialogManager.ShowAlertAsync("Zaplatit", "Zájemce odhlášen úspěšně. Pokud jste provedli pladbu, tak prosíme vyčkejte na její vrácení.")
        window.location.href = "./index.php"
    } else {
        SendToast("Odhlásit zájemce z akce", "Zájemce nemohl být odhlášen.", "error")
        await dialogManager.ShowAlertAsync("Odhlásit zájemce z akce", "Informace o odhlášení nemohly být uloženy, opakujte akci později.")
    }
})

document.getElementById("btnRemoveCD")?.addEventListener("click", async (e) => {
    if (!await dialogManager.ShowConfirmAsync("Odhlásit firmu ze dne firem?", "Opravdu chcete odhlásit Vaši firmu z tohoto dne firem?")) return;
    let data = new FormData()
    data.append("action", "rmcd")
    data.append("id", (e.target as HTMLButtonElement).getAttribute("comp") as string)
    data.append("idCD", urlSearchParams.get("cd") as string)
    let [ok, res] = await SendPOSTDataToServerAsync("./event.php", data);
    if (ok) {
        SendToast("Odhlášení.", res, "ok")
        window.location.href = "./"
    } else {
        SendToast("Odpověď serveru", res, "error")
    }

})

document.getElementById("btnAddSite")?.addEventListener("click", async (e) => {
    if (!await dialogManager.ShowConfirmAsync("Přidat nový stánek?", "Opravdu chcete přidat nový stánek pro tento dne firem?")) return;
    let data = new FormData()
    data.append("action", "addSite")
    data.append("id", (e.target as HTMLButtonElement).getAttribute("comp") as string)
    data.append("idCD", urlSearchParams.get("cd") as string)
    let [ok, res] = await SendPOSTDataToServerAsync("./event.php", data);
    if (ok) {
        SendToast("Odpověď serveru.", res, "ok")
        window.location.reload()
    } else {
        SendToast("Odpověď serveru", res, "error")
    }

})

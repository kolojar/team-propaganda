var _a, _b, _c, _d;
import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
for (const element of document.getElementsByClassName("siteInfo")) {
    SetupSaveCancelButtons(dialogManager, element, ".", "./site.php", element.getAttribute("site"));
}
for (const element of document.getElementsByClassName("presInfo")) {
    SetupSaveCancelButtons(dialogManager, element, ".", "./pres.php", element.getAttribute("site"));
}
for (let element of document.getElementsByClassName("rmSite")) {
    element.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odstranit stánek", "Opravdu chcete smazat tento stánek?")) {
            SendToast("Odstranit stánek", "Odstranění stánek bylo zrušeno.", "info");
            return;
        }
        let data = new FormData();
        data.append("action", "delete");
        data.append("id", element.getAttribute("site"));
        let [ok, res] = await SendPOSTDataToServerAsync("./site.php", data);
        if (ok) {
            SendToast("Odpověď serveru.", res, "ok");
            window.location.reload();
        }
        else {
            SendToast("Odpověď serveru", res, "error");
        }
    });
}
for (let element of document.getElementsByClassName("rmPres")) {
    element.addEventListener("click", async () => {
        if (!await dialogManager.ShowConfirmAsync("Odstranit prezentaci", "Opravdu chcete smazat tuto prezentaci?")) {
            SendToast("Odstranit prezentaci", "Odstranění prezentace bylo zrušeno.", "info");
            return;
        }
        let data = new FormData();
        data.append("action", "delete");
        data.append("id", element.getAttribute("site"));
        let [ok, res] = await SendPOSTDataToServerAsync("./pres.php", data);
        if (ok) {
            SendToast("Odpověď serveru.", res, "ok");
            window.location.reload();
        }
        else {
            SendToast("Odpověď serveru", res, "error");
        }
    });
}
const btnPay = document.getElementById("btnPay");
btnPay === null || btnPay === void 0 ? void 0 : btnPay.addEventListener("click", async () => {
    //Get payment info from server
    const progress = dialogManager.ShowProgress("Načítání dat", "Probíhá načítání dat, čekejte prosím...", () => { }, 0, false);
    const formData = new FormData();
    formData.set("action", "getBankAccount");
    const [ok, responce] = await SendPOSTDataToServerAsync("../assets/settingsManager.php", formData);
    if (!ok) {
        progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
        SendToast("Načítání dat selhalo", "Data nemohla být načtena.", "error");
        dialogManager.ShowConfirm("Načítání dat selhalo", "Nelze načíst data, opakujte akci později.", () => { });
        return;
    }
    const formData3 = new FormData();
    formData3.set("action", "getVariableSymbol");
    const [ok3, responce3] = await SendPOSTDataToServerAsync("./event.php", formData3);
    progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
    if (!ok3) {
        SendToast("Načítání dat selhalo", "Data nemohla být načtena.", "error");
        dialogManager.ShowConfirm("Načítání dat selhalo", "Nelze načíst data, opakujte akci později.<br>Důvod:" + responce3, () => { });
        return;
    }
    //Get bank account
    const bankAccount = await dialogManager.ShowPromptAsync("Zaplatit", "Zadejte číslo účtu pro případné vrácení peněz.", null, "text", { placeholder: "Číslo účtu" });
    if (bankAccount == null) {
        SendToast("Zaplatit", "Zadání platby bylo zrušeno.", "info");
        return;
    }
    //Show info for payment
    if (!await dialogManager.ShowConfirmAsync("Odešlete prosím platbu na následující účet", "Číslo účtu: <span class='fontMono'>" + responce + "</span><br>Variabilní symbol: <span class='fontMono'>" + responce3 + "</span><br>Částka: <span class='fontMono'>" + (btnPay === null || btnPay === void 0 ? void 0 : btnPay.getAttribute("price")) + "</span> Kč<br>Číslo účtu pro případné vrácení peněz: <span class='fontMono'>" + bankAccount + "</span>", { allowSelect: true })) {
        SendToast("Zaplatit", "Zaplacení platby bylo zrušeno.", "info");
        return;
    }
    //Write to DB
    const progress2 = dialogManager.ShowProgress("Zaplatit", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const formData2 = new FormData();
    formData2.set("action", "addPayment");
    formData2.set("bank_account", bankAccount);
    formData2.set("id_registered_attendants", responce3);
    const [ok2, responce2] = await SendPOSTDataToServerAsync("./event.php", formData2);
    progress2 === null || progress2 === void 0 ? void 0 : progress2.CloseDialog();
    if (ok2) {
        SendToast("Zaplatit", "Platba přidána úspěšně.", "ok");
        await dialogManager.ShowAlertAsync("Zaplatit", "Platba přidána úspěšně, nyní prosíme vyčkejte na její zpracování.");
        window.location.reload();
    }
    else {
        SendToast("Zaplatit", "Informace o zaplacení nemohly být uloženy.", "error");
        await dialogManager.ShowAlertAsync("Zaplatit", "Informace o zaplacení nemohly být uloženy.<br>Prosíme, pokud jste pladbu již odeslali, aby jste počkali několik dní.<br>Pokud se platba nezapočítá do několika dní, kontaktujte nás.");
    }
});
(_a = document.getElementById("btnRemoveAttendant")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    //Get confirmation
    const reason = await dialogManager.ShowPromptAsync("Odhlásit zájemce z akce?", "Opravdu chcete odhlásit zájemce z akce? Tento krok nelze vzít zpět. Sdělte nám prosím důvod.", null, "textarea", { placeholder: "Zadejte důvod odhlášení" });
    if (reason == null) {
        SendToast("Odhlásit zájemce z akce", "Odlášení bylo zrušeno - zájemce zůstává přihlášený.", "info");
        return;
    }
    //Write to DB
    const progress = dialogManager.ShowProgress("Odhlásit zájemce z akce", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const formData = new FormData();
    formData.set("action", "unregisterFromEvent");
    formData.set("attendant", urlSearchParams.get("attendant"));
    formData.set("event", urlSearchParams.get("event"));
    formData.set("reason", reason);
    const [ok, responce] = await SendPOSTDataToServerAsync("./event.php", formData);
    progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
    if (ok) {
        SendToast("Odhlásit zájemce z akce", "Zájemce odhlášen úspěšně.", "ok");
        await dialogManager.ShowAlertAsync("Zaplatit", "Zájemce odhlášen úspěšně. Pokud jste provedli pladbu, tak prosíme vyčkejte na její vrácení.");
        window.location.href = "./index.php";
    }
    else {
        SendToast("Odhlásit zájemce z akce", "Zájemce nemohl být odhlášen.", "error");
        await dialogManager.ShowAlertAsync("Odhlásit zájemce z akce", "Informace o odhlášení nemohly být uloženy, opakujte akci později.<br>Důvod: " + responce);
    }
});
(_b = document.getElementById("btnRemoveCD")) === null || _b === void 0 ? void 0 : _b.addEventListener("click", async (e) => {
    if (!await dialogManager.ShowConfirmAsync("Odhlásit firmu ze dne firem?", "Opravdu chcete odhlásit Vaši firmu z tohoto dne firem?"))
        return;
    let data = new FormData();
    data.append("action", "rmcd");
    data.append("id", e.target.getAttribute("comp"));
    data.append("idCD", urlSearchParams.get("cd"));
    let [ok, res] = await SendPOSTDataToServerAsync("./event.php", data);
    if (ok) {
        SendToast("Odhlášení.", res, "ok");
        window.location.href = "./";
    }
    else {
        SendToast("Odpověď serveru", res, "error");
    }
});
(_c = document.getElementById("btnAddSite")) === null || _c === void 0 ? void 0 : _c.addEventListener("click", async (e) => {
    if (!await dialogManager.ShowConfirmAsync("Přidat nový stánek?", "Opravdu chcete přidat nový stánek pro tento dne firem?"))
        return;
    let data = new FormData();
    data.append("action", "addSite");
    data.append("id", e.target.getAttribute("comp"));
    data.append("idCD", urlSearchParams.get("cd"));
    let [ok, res] = await SendPOSTDataToServerAsync("./event.php", data);
    if (ok) {
        SendToast("Odpověď serveru.", res, "ok");
        window.location.reload();
    }
    else {
        SendToast("Odpověď serveru", res, "error");
    }
});
(_d = document.getElementById("btnAddPres")) === null || _d === void 0 ? void 0 : _d.addEventListener("click", async (e) => {
    if (!await dialogManager.ShowConfirmAsync("Přidat novou prezentaci?", "Opravdu chcete přidat novou prezentaci pro tento dne firem?"))
        return;
    let data = new FormData();
    data.append("action", "addPres");
    data.append("id", e.target.getAttribute("comp"));
    data.append("idCD", urlSearchParams.get("cd"));
    let [ok, res] = await SendPOSTDataToServerAsync("./event.php", data);
    if (ok) {
        SendToast("Odpověď serveru.", res, "ok");
        window.location.reload();
    }
    else {
        SendToast("Odpověď serveru", res, "error");
    }
});
//# sourceMappingURL=event.js.map
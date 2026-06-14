import { SetupSaveCancelButtons } from "../assets/sharedScripts.js";
import { GlobalDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const urlSearchParams = new URLSearchParams(window.location.search);
SetupSaveCancelButtons(null, "./companies.php", "./company.php", urlSearchParams.get("company"));
const manageFieldsBtn = document.getElementById("manageFields");
manageFieldsBtn === null || manageFieldsBtn === void 0 ? void 0 : manageFieldsBtn.addEventListener("click", async () => {
    //Get fields from HTML
    const fieldsJson = JSON.parse(manageFieldsBtn.getAttribute("fields"));
    const fieldsSelectedJson = JSON.parse(manageFieldsBtn.getAttribute("checked-fields"));
    const fields = new Map();
    for (const key in fieldsJson) {
        fields.set(fieldsJson[key], { value: key, checked: false });
    }
    for (const key of fieldsSelectedJson) {
        let get = fields.get(fieldsJson[key]);
        if (get != undefined) {
            get.checked = true;
            fields.set(fieldsJson[key], get);
        }
    }
    //Show dialog
    const selectedFields = await GlobalDialogManager.ShowCheckboxSelectAsync("Vyberte obory", "Vyberte obory, o které má firma zájem.", null, fields);
    if (selectedFields == null) {
        SendToast("Vyberte obory", "Akce zrušena.", "info");
        return;
    }
    //Send to DB
    const progress = GlobalDialogManager.ShowProgress("Vyberte obory", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false);
    const data = new FormData();
    data.set("id", urlSearchParams.get("company"));
    data.set("action", "setFields");
    data.set("fields", selectedFields.toString());
    const [ok, responce] = await SendPOSTDataToServerAsync("./company.php", data);
    if (!ok) {
        progress === null || progress === void 0 ? void 0 : progress.CloseDialog();
        SendToast("Vyberte obory", "Nelze uložit obory do databáze.", "error");
        await GlobalDialogManager.ShowAlertAsync("Vyberte obory", "Nelze uložit změny do databáze, zkuste to prosím znovu. Důvod: " + responce);
        return;
    }
    //Ok
    SendToast("Vyberte obory", "Změny uloženy!", "ok");
    setTimeout(() => {
        window.location.reload();
    }, 1000);
});
//# sourceMappingURL=company.js.map
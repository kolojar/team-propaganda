var _a, _b;
import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
//Make save button work
(_a = document.getElementById("classroomBtnSave")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async () => {
    var _a;
    //Get elements
    const isNew = ((_a = document.getElementById("classroomBtnSave")) === null || _a === void 0 ? void 0 : _a.getAttribute("exists")) == "false";
    const changes = [];
    const nameElement = document.getElementById("classroomName");
    const placesToSitElement = document.getElementById("classroomPlacesToSit");
    const isActiveElement = document.getElementById("classroomIsActive");
    for (const inputElementOriginal of document.getElementsByClassName("classroomValidate")) {
        const inputElement = inputElementOriginal;
        const [changed, isValid] = await inputElement.validate();
        console.log(changed, isValid);
        if (!isValid) {
            SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error");
            return;
        }
        if (changed) {
            changes.push("• " + inputElement.getOriginalValue() + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
        else if (isNew) {
            changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
    }
    //Show dialog if found change
    if (changes.length == 0) {
        SendToast("Nelze uložit změny!", "Žádné změny nebyly provedeny.", "ok");
        return;
    }
    //Wait for confirm
    if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete uložit provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        const progress = dialogManager.ShowProgress("Ukládání dat", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
        const data = new FormData();
        data.append("action", isNew ? "insert" : "update");
        data.append("table", "classrooms");
        if (!isNew) {
            data.append("id", urlSearchParams.get("classroom"));
        }
        data.append("name", document.getElementById("classroomName").getValue());
        data.append("placesToSit", document.getElementById("classroomPlacesToSit").getValue());
        data.append("isFunctional", document.getElementById("classroomIsFunctional").getValue() ? "1" : "0");
        data.append("note", document.getElementById("classroomNote").getValue());
        const [ok, _] = await SendPOSTDataToServerAsync("./adminFunctions.php", data);
        //progress.CloseDialog()
        if (ok) {
            SendToast("Ukládání dat", "Změny uloženy.", "ok");
            //progress.SetMessage(0,"Změny uloženy")
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        else {
            SendToast("Ukládání dat", "Změny nemohly být uloženy.", "error");
        }
    }
});
//Make classroom cancel button work
(_b = document.getElementById("classroomBtnCancel")) === null || _b === void 0 ? void 0 : _b.addEventListener("click", async function () {
    var _a;
    //Check attendantValidate for changes
    let foundChange = false;
    const changes = [];
    for (const inputElementOriginal of document.getElementsByClassName("classroomValidate")) {
        const inputElement = inputElementOriginal;
        const [changed, isValid] = await inputElement.validate();
        console.log(changed, isValid);
        if (changed) {
            foundChange = true;
            changes.push("• " + inputElement.getOriginalValue() + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
        //changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
    }
    //Wait for confirm
    if (((_a = document.getElementById("classroomBtnCancel")) === null || _a === void 0 ? void 0 : _a.getAttribute("exists")) == "false") {
        if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete zrušit vytváření nové třídy?", true, true)) {
            window.location.replace("?view='classrooms'");
        }
        return;
    }
    if (foundChange && await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete smazat provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        window.location.reload();
    }
});
//# sourceMappingURL=classroom.js.map
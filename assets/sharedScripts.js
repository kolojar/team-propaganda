import { HTMLFormInputElement, HTMLFormToggleElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
export function GetChildenElementsByClassName(element, className) {
    return Array.from(element.querySelectorAll("*")).filter(el => el.classList.contains(className));
}
//export function getChildenElementsByValueId(element: HTMLElement, valueId: string): HTMLElement[] {
//    return Array.from(element.querySelectorAll("*")).filter(el => el.getAttribute("value-id") == valueId) as HTMLElement[] 
//}
export function SetupSaveCancelButtons(dialogManager, holderId, cancelURL, postURL, id, className = "validate", onSaveFunc = null) {
    var _a;
    let holder = null;
    if (holderId == null) {
        holder = document.body;
    }
    else if (holderId instanceof HTMLElement) {
        holder = holderId;
    }
    else {
        holder = document.getElementById(holderId);
    }
    //Setup validation
    let changed = false;
    for (const inputElementOriginal of GetChildenElementsByClassName(holder, className)) {
        if (inputElementOriginal instanceof HTMLFormInputElement) {
            const inputElement = inputElementOriginal;
            inputElement.validationFunction = async (value) => {
                return Promise.resolve(value.length > 0);
            };
            inputElement.validate();
        }
    }
    //Check if exists
    let exists = true;
    const saveBtn = GetChildenElementsByClassName(holder, "btnSave")[0];
    if (saveBtn.hasAttribute("exists")) {
        exists = saveBtn.getAttribute("exists") == "true";
    }
    //Make save button work
    saveBtn.addEventListener("click", async () => {
        //Get elements
        const progress2 = dialogManager.ShowProgress("Hledání změn", "Probíhá hledání změn, čekejte prosím...", () => { }, 0, false, true, true);
        const changes = [];
        //Process elements
        for (const inputElementOriginal of document.getElementsByClassName(className)) {
            const inputElement = inputElementOriginal;
            const [changed, isValid] = await inputElement.validate();
            console.log(changed, isValid);
            if (!isValid) {
                SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error");
                progress2.CloseDialog();
                return;
            }
            if (!exists) {
                changes.push("• " + inputElement.getLabel() + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue() ? "Ano" : "Ne"));
            }
            else if (changed) {
                changes.push("• " + inputElement.getLabel() + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.getOriginalValue() : inputElement.getOriginalValue() ? "Ano" : "Ne") + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue() ? "Ano" : "Ne"));
            }
        }
        //Show dialog if found change
        if (changes.length == 0) {
            SendToast("Nelze uložit změny!", "Žádné změny nebyly provedeny.", "ok");
            progress2.CloseDialog();
            return;
        }
        //Run save function
        if (onSaveFunc != null) {
            if (!(await onSaveFunc())) {
                return;
            }
        }
        //Wait for confirm
        progress2.CloseDialog();
        if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete uložit provedené změny:<br>" + changes.join("<br>"), true, true)) {
            const progress = dialogManager.ShowProgress("Uložit změny", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
            //Create FormData
            const data = new FormData();
            data.append("action", exists ? "update" : "insert");
            //data.append("table", table)
            if (exists) {
                data.append("id", id);
            }
            for (const inputElementOriginal of GetChildenElementsByClassName(holder, className)) {
                const inputElement = inputElementOriginal;
                if (inputElement instanceof HTMLFormToggleElement) {
                    data.append(inputElement.getAttribute("value-id"), inputElement.getValue() ? "1" : "0");
                }
                else {
                    data.append(inputElement.getAttribute("value-id"), inputElement.getValue());
                }
            }
            //Send to server
            const [ok, _] = await SendPOSTDataToServerAsync(postURL, data);
            //progress.CloseDialog()
            if (ok) {
                SendToast("Uložení změn proběhlo úspěšně!", "Změny uloženy.", "ok");
                //progress.SetMessage(0,"Změny uloženy")
                setTimeout(() => {
                    if (!exists) {
                        window.location.href = cancelURL;
                    }
                    else {
                        window.location.reload();
                    }
                }, 1000);
            }
            else {
                SendToast("Nelze uložit změny!", "Změny nemohly být uloženy.", "error");
                progress.CloseDialog();
                await dialogManager.OpenAlert("Uložit změny", "Změny nemohly být uloženy, opakujte akci později.", true, true);
            }
        }
    });
    //Make cancel button work
    (_a = GetChildenElementsByClassName(holder, "btnCancel")[0]) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async function () {
        //Check for changes
        const progress2 = dialogManager.ShowProgress("Hledání změn", "Probíhá hledání změn, čekejte prosím...", () => { }, 0, false, true, true);
        let foundChange = false;
        const changes = [];
        for (const inputElementOriginal of document.getElementsByClassName(className)) {
            const inputElement = inputElementOriginal;
            const [changed, isValid] = await inputElement.validate();
            console.log(changed, isValid);
            if (changed) {
                foundChange = true;
                changes.push("• " + inputElement.getLabel() + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.getOriginalValue() : inputElement.getOriginalValue() ? "Ano" : "Ne") + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue() ? "Ano" : "Ne"));
            }
            //changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
        //Wait for confirm
        progress2.CloseDialog();
        if (!exists) {
            if (await dialogManager.OpenConfirm("Smazat změny?", "Opravdu chcete zrušit vytváření?", true, true)) {
                window.location.href = cancelURL;
            }
            return;
        }
        if (foundChange && await dialogManager.OpenConfirm("Smazat změny?", "Opravdu chcete smazat provedené změny:<br>" + changes.join("<br>"), true, true)) {
            dialogManager.ShowProgress("Smazat změny", "Probíhá rušení změn, čekejte prosím...", () => { }, 0, false, true, true);
            window.location.reload();
        }
        if (!foundChange) {
            SendToast("Nelze smazat změny!", "Žádné změny nebyly provedeny.", "ok");
            return;
        }
    });
}
//Make Row of table highlightable
for (const row of document.getElementsByClassName("clickHighlightRow")) {
    row.addEventListener("click", () => {
        if (row.classList.contains("trHighlight")) {
            row.classList.remove("trHighlight");
        }
        else {
            for (const row2 of document.getElementsByClassName("clickHighlightRow")) {
                row2.classList.remove("trHighlight");
            }
            row.classList.add("trHighlight");
        }
    });
}
export function setupTableDeleteButtons(dialogManager, postURL, idAttributeName) {
    //Get buttons
    for (const button of document.getElementsByClassName("btnTableDelete")) {
        button.addEventListener("click", async () => {
            if (!button.hasAttribute(idAttributeName)) {
                return;
            }
            //Ask for confirm
            if (!await dialogManager.OpenConfirm("Opravdu smazat?", "Opravdu chcete odstranit vybraný řádek?", true, true)) {
                return;
            }
            //Create FormData
            const progress = dialogManager.ShowProgress("Mazání dat", "Probíhá mazání dat z databáze, čekejte prosím...", () => { }, 0, false, true, true);
            const formData = new FormData();
            formData.set("action", "delete");
            formData.set("id", button.getAttribute(idAttributeName));
            //Send request
            const [ok, _] = await SendPOSTDataToServerAsync(postURL, formData);
            if (!ok) {
                SendToast("Mazání dat", "Nelze smazat data", "error");
                progress.CloseDialog();
                await dialogManager.OpenAlert("Mazání dat", "Data nemohla být smazána, opakujte akci později.", true, true);
                return;
            }
            //All OK
            SendToast("Mazání dat", "Data odstraněna.", "ok");
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }
}
//# sourceMappingURL=sharedScripts.js.map
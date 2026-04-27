import { HTMLFormInputElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";
export function setupButtons(dialogManager, className, cancelURL, postURL, id) {
    var _a, _b, _c, _d;
    //Setup validation
    for (const inputElementOriginal of document.getElementsByClassName(className)) {
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
    if ((_a = document.getElementById("btnSave")) === null || _a === void 0 ? void 0 : _a.hasAttribute("exists")) {
        exists = ((_b = document.getElementById("btnSave")) === null || _b === void 0 ? void 0 : _b.getAttribute("exists")) == "true";
    }
    //Make save button work
    (_c = document.getElementById("btnSave")) === null || _c === void 0 ? void 0 : _c.addEventListener("click", async () => {
        //Get elements
        const changes = [];
        //Process elements
        for (const inputElementOriginal of document.getElementsByClassName(className)) {
            const inputElement = inputElementOriginal;
            const [changed, isValid] = await inputElement.validate();
            console.log(changed, isValid);
            if (!isValid) {
                SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error");
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
            return;
        }
        //Wait for confirm
        if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete uložit provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
            const progress = dialogManager.ShowProgress("Ukládání dat", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true);
            //Create FormData
            const data = new FormData();
            data.append("action", exists ? "update" : "insert");
            //data.append("table", table)
            if (exists) {
                data.append("id", id);
            }
            for (const inputElementOriginal of document.getElementsByClassName(className)) {
                const inputElement = inputElementOriginal;
                data.append(inputElement.id, inputElement.getValue());
            }
            //Send to server
            const [ok, _] = await SendPOSTDataToServerAsync(postURL, data);
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
    //Make cancel button work
    (_d = document.getElementById("btnCancel")) === null || _d === void 0 ? void 0 : _d.addEventListener("click", async function () {
        //Check for changes
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
        if (!exists) {
            if (await dialogManager.OpenConfirm("Smazat změny?", "Opravdu chcete zrušit vytváření?", true, true)) {
                window.location.replace(cancelURL);
            }
            return;
        }
        if (foundChange && await dialogManager.OpenConfirm("Smazat změny?", "Opravdu chcete smazat provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
            window.location.reload();
        }
        if (!foundChange) {
            SendToast("Nelze smazat změny!", "Žádné změny nebyly provedeny.", "ok");
            return;
        }
    });
}
//Make Row of user highlightable
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
//# sourceMappingURL=sharedScripts.js.map
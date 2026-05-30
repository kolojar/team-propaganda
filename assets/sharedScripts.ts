import { FormDialog, FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, HTMLFormToggleElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

export function GetChildenElementsByClassName(element: HTMLElement, className: string): HTMLElement[] {
    return Array.from(element.querySelectorAll("*")).filter(el => el.classList.contains(className)) as HTMLElement[]
}

//export function getChildenElementsByValueId(element: HTMLElement, valueId: string): HTMLElement[] {
//    return Array.from(element.querySelectorAll("*")).filter(el => el.getAttribute("value-id") == valueId) as HTMLElement[] 
//}

export function SetupSaveCancelButtons(dialogManager: FormDialogManager, holderId: string | null | HTMLElement, cancelURL: string, postURL: string, id: string, className: string = "validate", onSaveFunc: null | (() => Promise<boolean>) = null) {
    let holder = null;
    if (holderId == null) {
        holder = document.body;
    } else if (holderId instanceof HTMLElement) {
        holder = holderId;
    } else {
        holder = document.getElementById(holderId as string) as HTMLElement;
    }

    //Setup validation
    let changed = false
    for (const inputElementOriginal of GetChildenElementsByClassName(holder, className)) {
        if (inputElementOriginal instanceof HTMLFormInputElement) {
            const inputElement = inputElementOriginal as HTMLFormInputElement
            inputElement.validationFunction = async (value) => {
                return Promise.resolve(value.toString().length > 0)
            }
            inputElement.validate()
        }
    }

    //Check if exists
    let exists = true;
    const saveBtn = GetChildenElementsByClassName(holder, "btnSave")[0]
    if (saveBtn.hasAttribute("exists")) {
        exists = saveBtn.getAttribute("exists") == "true"
    }

    //Make save button work
    saveBtn.addEventListener("click", async () => {
        //Get elements
        const progress2 = dialogManager.ShowProgress("Hledání změn", "Probíhá hledání změn, čekejte prosím...", () => { }, 0, false)
        const changes = []

        //Process elements
        for (const inputElementOriginal of document.getElementsByClassName(className)) {
            const inputElement = inputElementOriginal as HTMLFormInputElement | HTMLFormToggleElement
            const [changed, isValid] = await inputElement.validate()
            console.log(changed, isValid);
            if (!isValid) {
                SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error")
                progress2?.CloseDialog()
                return
            }
            if (!exists) {
                changes.push("• " + inputElement.label + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.valueRaw : inputElement.value ? "Ano" : "Ne"));
            } else if (changed) {
                changes.push("• " + inputElement.label + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.originalValueRaw : inputElement.originalChecked ? "Ano" : "Ne") + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.valueRaw : inputElement.value ? "Ano" : "Ne"));
            }
        }

        //Show dialog if found change
        if (changes.length == 0) {
            SendToast("Nelze uložit změny!", "Žádné změny nebyly provedeny.", "ok")
            progress2?.CloseDialog()
            return
        }

        //Run save function
        if (onSaveFunc != null) {
            if (!(await onSaveFunc())) {
                progress2?.CloseDialog()
                return
            }
        }

        //Wait for confirm
        progress2?.CloseDialog()
        if (await dialogManager.ShowConfirmAsync("Uložit změny?", "Opravdu chcete uložit provedené změny:<br>" + changes.join("<br>"))) {
            const progress = dialogManager.ShowProgress("Uložit změny", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false)

            //Create FormData
            const data = new FormData()
            data.append("action", exists ? "update" : "insert")
            //data.append("table", table)
            if (exists) {
                data.append("id", id)
            }
            for (const inputElementOriginal of GetChildenElementsByClassName(holder, className)) {
                const inputElement = inputElementOriginal as HTMLFormInputElement | HTMLFormToggleElement
                console.log(inputElement);
                if (inputElement instanceof HTMLFormToggleElement) {
                    data.append(inputElement.getAttribute("value-id") as string, inputElement.value ? "1" : "0");
                } else if (inputElement.type == "file") {
                    console.log("file");
                    data.append(inputElement.getAttribute("value-id") as string, inputElement.getAttribute("file") as string);
                } else {
                    data.append(inputElement.getAttribute("value-id") as string, inputElement.value);
                }
            }

            //Send to server
            const [ok, reason] = await SendPOSTDataToServerAsync(postURL, data)
            //progress.CloseDialog()
            if (ok) {
                SendToast("Uložení změn proběhlo úspěšně!", "Změny uloženy.", "ok")
                //progress.SetMessage(0,"Změny uloženy")
                setTimeout(() => {
                    if (!exists) {
                        window.location.href = cancelURL
                    } else {
                        window.location.reload()
                    }
                }, 1000)
            } else {
                SendToast("Nelze uložit změny!", "Změny nemohly být uloženy.", "error")
                progress?.CloseDialog()
                await dialogManager.ShowAlertAsync("Uložit změny", "Změny nemohly být uloženy, opakujte akci později.<br>Důvod: " + reason)
            }
        } else {
            SendToast("Uložit změny", "Ukládání změn zrušeno.", "info")
        }
    })

    //Make cancel button work
    GetChildenElementsByClassName(holder, "btnCancel")[0]?.addEventListener("click", async function () {
        //Check for changes
        const progress2 = dialogManager.ShowProgress("Hledání změn", "Probíhá hledání změn, čekejte prosím...", () => { }, 0, false)
        let foundChange = false
        const changes = []
        for (const inputElementOriginal of document.getElementsByClassName(className)) {
            const inputElement = inputElementOriginal as HTMLFormInputElement | HTMLFormToggleElement
            const [changed, isValid] = await inputElement.validate()
            console.log(changed, isValid);
            if (changed) {
                foundChange = true;
                changes.push("• " + inputElement.label + " " + (inputElement instanceof HTMLFormInputElement ? inputElement.originalValueRaw : inputElement.originalChecked ? "Ano" : "Ne") + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.valueRaw : inputElement.value ? "Ano" : "Ne"));
            }
            //changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getvalueRaw : inputElement.value));
        }

        //Wait for confirm
        progress2?.CloseDialog()
        if (!exists) {
            if (await dialogManager.ShowConfirmAsync("Smazat změny?", "Opravdu chcete zrušit vytváření?")) {
                window.location.href = cancelURL
            }
            return
        }
        if (foundChange) {
            if (await dialogManager.ShowConfirmAsync("Smazat změny?", "Opravdu chcete smazat provedené změny:<br>" + changes.join("<br>"))) {
                dialogManager.ShowProgress("Smazat změny", "Probíhá rušení změn, čekejte prosím...", () => { }, 0, false)
                setTimeout(() => {
                    window.location.reload()
                }, 1000)
            } else {
                SendToast("Smazat změny", "Mazání změn zrušeno.", "info")
            }
        } else {
            SendToast("Nelze smazat změny!", "Žádné změny nebyly provedeny.", "ok")
            return
        }
    })
}

//Make Row of table highlightable
for (const row of document.getElementsByClassName("clickHighlightRow")) {
    (row as HTMLTableRowElement).addEventListener("click", () => {
        if (row.classList.contains("trHighlight")) {
            row.classList.remove("trHighlight")
        } else {
            for (const row2 of document.getElementsByClassName("clickHighlightRow")) {
                row2.classList.remove("trHighlight")
            }
            row.classList.add("trHighlight")
        }
    })
}

export function setupTableDeleteButtons(dialogManager: FormDialogManager, postURL: string, idAttributeName: string) {
    //Get buttons
    for (const button of document.getElementsByClassName("btnTableDelete")) {
        button.addEventListener("click", async () => {
            if (!button.hasAttribute(idAttributeName)) {
                return
            }

            //Ask for confirm
            if (! await dialogManager.ShowConfirmAsync("Opravdu smazat?", "Opravdu chcete odstranit vybraný řádek?")) {
                SendToast("Smazat řádek", "Mazání změn zrušeno.", "info")
                return
            }

            //Create FormData
            const progress = dialogManager.ShowProgress("Mazání dat", "Probíhá mazání dat z databáze, čekejte prosím...", () => { }, 0, false)
            const formData = new FormData()
            formData.set("action", "delete")
            formData.set("id", button.getAttribute(idAttributeName) as string)

            //Send request
            const [ok, reason] = await SendPOSTDataToServerAsync(postURL, formData)
            if (!ok) {
                SendToast("Mazání dat", "Nelze smazat data", "error")
                progress?.CloseDialog()
                await dialogManager.ShowAlertAsync("Mazání dat", "Data nemohla být smazána, opakujte akci později.<br>Důvod: " + reason)
                return
            }

            //All OK
            SendToast("Mazání dat", "Data odstraněna.", "ok")
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        })
    }
}

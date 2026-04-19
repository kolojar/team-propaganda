import { FormDialogManager } from "./formWebScripts/js/formDialogScript.js";
import { SendToast } from "./formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "./formWebScripts/js/serverComunication.js";
import { KeyValuePair } from "./formWebScripts/js/sharedScripts.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)

//Make attendantValidate validable
for (const inputElement of document.getElementsByClassName("schoolValidate")) {
    const attendantValidate = () => {
        if (input.value == undefined || input.value.trim().length == 0) {
            //Value empty
            if (!inputHolder.classList.contains("formErrorBorderColor")) {
                inputHolder.classList.add("formErrorBorderColor")
            }
            return
        } else {
            //Value OK
            if (inputHolder.classList.contains("formErrorBorderColor")) {
                inputHolder.classList.remove("formErrorBorderColor")
            }
        }
        if (input.value != input.placeholder) {
            //Value changed
            if (!inputHolder.classList.contains("formWarnBorderColor")) {
                inputHolder.classList.add("formWarnBorderColor")
            }
            return
        } else {
            //Value same
            if (inputHolder.classList.contains("formWarnBorderColor")) {
                inputHolder.classList.remove("formWarnBorderColor")
            }
        }
    }
    const inputHolder = inputElement.children.item(0) as HTMLDivElement
    const input = inputHolder.children.item(0) as HTMLInputElement
    input.addEventListener("input", attendantValidate)
    input.addEventListener("focusout", attendantValidate)
}

//Make attendant save button work
document.getElementById("schoolBtnSave")?.addEventListener("click", async function () {
    //Check attendantValidate for changes
    let foundChange = false
    const changes  = []
    for (const inputElement of document.getElementsByClassName("schoolValidate")) {
        const inputHolder = inputElement.children.item(0)
        const input = inputHolder?.children.item(0) as HTMLInputElement
        inputHolder?.dispatchEvent(new Event("input"))
        if (inputHolder?.classList.contains("formErrorBorderColor")) {
            SendToast("Nelze uložit změny!", "Některé údaje jsou neplatné.", "error")
            return
        }
        if (inputHolder?.classList.contains("formWarnBorderColor")) {
            foundChange = true
            changes.push("• " + input.placeholder + " → " + input.value);
        }
    }

    //Show dialog if found change
    if (!foundChange) {
        SendToast("Nelze uložit změny!", "Žádné změny nebyly provedeny.", "ok")
        return
    }

    //Wait for confirm
    if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete uložit provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        const progress = dialogManager.ShowProgress("Ukládání dat","Probíhá zápis do databáze, čekejte prosím...",() => {},0,false,true,true)
        const data = new FormData()
        data.append("action","update")
        data.append("table","schools")
        data.append("id",urlSearchParams.get("school")as string);
        data.append("name",(document.getElementById("schoolName") as HTMLInputElement).value);
        data.append("address",(document.getElementById("schoolAddress") as HTMLInputElement).value);
        const [ok,_] = await SendPOSTDataToServerAsync("./adminFunctions.php",data)
        //progress.CloseDialog()
        if (ok) {
            SendToast("Ukládání dat","Změny uloženy.","ok")
            //progress.SetMessage(0,"Změny uloženy")
            setTimeout(() => {
            window.location.reload()
            },1000)
        } else {
            SendToast("Ukládání dat","Změny nemohly být uloženy.","error")
        }
    }
})

//Make attendant cancel button work
document.getElementById("schoolBtnCancel")?.addEventListener("click", async function () {
    //Check attendantValidate for changes
    let foundChange = false
    const changes  = []
    for (const inputElement of document.getElementsByClassName("schoolValidate")) {
        const inputHolder = inputElement.children.item(0)
        const input = inputHolder?.children.item(0) as HTMLInputElement
        inputHolder?.dispatchEvent(new Event("input"))
        if (inputHolder?.classList.contains("formErrorBorderColor")) {
            SendToast("Nelze uložit změny!", "Některé údaje jsou neplatné.", "error")
            return
        }
        if (inputHolder?.classList.contains("formWarnBorderColor")) {
            foundChange = true
            changes.push("• " + input.placeholder + " → " + input.value);
        }
    }

    //Wait for confirm
    if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete smazat provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        window.location.reload()
    }
})
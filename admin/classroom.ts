import { FormDialogManager } from "../formWebScripts/js/formDialogScript.js";
import { HTMLFormInputElement, HTMLFormToggleElement, SendToast } from "../formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "../formWebScripts/js/serverComunication.js";

const dialogManager = new FormDialogManager()
const urlSearchParams = new URLSearchParams(window.location.search)

//Make save button work
document.getElementById("classroomBtnSave")?.addEventListener("click", async () => {
    //Get elements
    const isNew = document.getElementById("classroomBtnSave")?.getAttribute("exists") == "false"
    const changes = []
    const nameElement = document.getElementById("classroomName") as HTMLFormInputElement
    const placesToSitElement = document.getElementById("classroomPlacesToSit") as HTMLFormInputElement
    const isActiveElement = document.getElementById("classroomIsActive") as HTMLFormToggleElement
    for (const inputElementOriginal of document.getElementsByClassName("classroomValidate")) {
        const inputElement = inputElementOriginal as HTMLFormInputElement | HTMLFormToggleElement
        const [changed, isValid] = await inputElement.validate()
        console.log(changed, isValid);
        if (!isValid) {
            SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error")
            return
        }
        if (changed) {
            changes.push("• " + inputElement.getOriginalValue() + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        } else if (isNew) {
            changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
    }

    //Show dialog if found change
    if (changes.length == 0) {
        SendToast("Nelze uložit změny!", "Žádné změny nebyly provedeny.", "ok")
        return
    }

    //Wait for confirm
    if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete uložit provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        const progress = dialogManager.ShowProgress("Ukládání dat", "Probíhá zápis do databáze, čekejte prosím...", () => { }, 0, false, true, true)
        const data = new FormData()
        data.append("action", isNew ? "insert" : "update")
        data.append("table", "classrooms")
        if (!isNew) {
            data.append("id", urlSearchParams.get("classroom") as string)
        }
        data.append("name", (document.getElementById("classroomName") as HTMLFormInputElement).getValue());
        data.append("placesToSit", (document.getElementById("classroomPlacesToSit") as HTMLFormInputElement).getValue());
        data.append("isFunctional", (document.getElementById("classroomIsFunctional") as HTMLFormToggleElement).getValue() ? "1" : "0");
        data.append("note", (document.getElementById("classroomNote") as HTMLFormInputElement).getValue());
        const [ok, _] = await SendPOSTDataToServerAsync("./adminFunctions.php", data)
        //progress.CloseDialog()
        if (ok) {
            SendToast("Ukládání dat", "Změny uloženy.", "ok")
            //progress.SetMessage(0,"Změny uloženy")
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        } else {
            SendToast("Ukládání dat", "Změny nemohly být uloženy.", "error")
        }
    }
})

//Make classroom cancel button work
document.getElementById("classroomBtnCancel")?.addEventListener("click", async function () {
    //Check attendantValidate for changes
    let foundChange = false
    const changes = []
    for (const inputElementOriginal of document.getElementsByClassName("classroomValidate")) {
        const inputElement = inputElementOriginal as HTMLFormInputElement | HTMLFormToggleElement
        const [changed, isValid] = await inputElement.validate()
        console.log(changed, isValid);
        if (changed) {
            foundChange = true;
            changes.push("• " + inputElement.getOriginalValue() + " → " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
        }
        //changes.push("• " + (inputElement instanceof HTMLFormInputElement ? inputElement.getValueRaw() : inputElement.getValue()));
    }

    //Wait for confirm
    if (document.getElementById("classroomBtnCancel")?.getAttribute("exists") == "false") {
        if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete zrušit vytváření nové třídy?", true, true)) {
        window.location.replace("?view='classrooms'")
    }
    return
    }
    if (foundChange && await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete smazat provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        window.location.reload()
    }
})
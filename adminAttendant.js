var _a, _b;
import { FormDialogManager } from "./formWebScripts/js/formDialogScript.js";
import { SendToast } from "./formWebScripts/js/formScript.js";
import { SendPOSTDataToServerAsync } from "./formWebScripts/js/serverComunication.js";
const dialogManager = new FormDialogManager();
const urlSearchParams = new URLSearchParams(window.location.search);
async function deleteUser(userId, name) {
    await dialogManager.OpenConfirm("Smazání uživatele", "Opravdu chcete odebrat uživatele: " + name + "?", true, true);
}
//Make User delete buttons work
//for (const button of document.getElementsByClassName("deleteUserButton")) {
//    (button as HTMLButtonElement).addEventListener("click", async () => {
//        await deleteUser(button.getAttribute("userId") as string, button.getAttribute("userName") as string)
//    })
//}
//Make Parent of user clickable
for (const button of document.getElementsByClassName("parentOfUserCell")) {
    button.addEventListener("click", async () => {
        console.log(await dialogManager.OpenSelect("Vyberte akci", "Co chcete provést?", 0, new Map([["Napsat", 1], ["Zobrazit komunikaci", 2]])));
    });
}
//Make attendantValidate validable
//for (const inputElement of document.getElementsByClassName("attendantValidate")) {
//    const input = inputElement as HTMLFormInputElement
//    input.validationFunction = (value) => {
//        if (value == undefined || value.trim().length == 0) {
//            //Value empty
//            return false
//        } else {
//            //Value OK
//            return true
//        }
//    }
//}
//Make attendant save button work
(_a = document.getElementById("attendantBtnSave")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", async function () {
    //Check attendantValidate for changes
    const changes = [];
    for (const inputElementOriginal of document.getElementsByClassName("attendantValidate")) {
        const inputElement = inputElementOriginal;
        const [changed, isValid] = await inputElement.validate();
        console.log(changed, isValid);
        if (!isValid) {
            SendToast("Nelze uložit změny!", "Pole obsahuje neplatnou hodnotu.", "error");
            return;
        }
        if (changed) {
            changes.push("• " + inputElement.getOriginalValue() + " → " + inputElement.getValueRaw());
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
        data.append("action", "update");
        data.append("table", "users");
        data.append("id", urlSearchParams.get("user"));
        data.append("name", document.getElementById("attendantName").getValue());
        data.append("surname", document.getElementById("attendantSurname").getValue());
        data.append("email", document.getElementById("attendantEmail").getValue());
        data.append("school_id", document.getElementById("attendantSchool").getValue());
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
//Make attendant cancel button work
(_b = document.getElementById("attendantBtnCancel")) === null || _b === void 0 ? void 0 : _b.addEventListener("click", async function () {
    //Check attendantValidate for changes
    let foundChange = false;
    const changes = [];
    for (const inputElement of document.getElementsByClassName("attendantValidate")) {
        const inputHolder = inputElement.children.item(0);
        const input = inputHolder === null || inputHolder === void 0 ? void 0 : inputHolder.children.item(0);
        inputHolder === null || inputHolder === void 0 ? void 0 : inputHolder.dispatchEvent(new Event("input"));
        if (inputHolder === null || inputHolder === void 0 ? void 0 : inputHolder.classList.contains("formErrorBorderColor")) {
            SendToast("Nelze uložit změny!", "Některé údaje jsou neplatné.", "error");
            return;
        }
        if (inputHolder === null || inputHolder === void 0 ? void 0 : inputHolder.classList.contains("formWarnBorderColor")) {
            foundChange = true;
            changes.push("• " + input.placeholder + " → " + input.value);
        }
    }
    //Wait for confirm
    if (await dialogManager.OpenConfirm("Uložit změny?", "Opravdu chcete smazat provedené změny:\r\n" + changes.join("\r\n"), true, true)) {
        window.location.reload();
    }
});
//Make attendant change school button work
const attendantSchool = document.getElementById("attendantSchool");
attendantSchool.validationFunction = async (value) => {
    const timestamp = new Date();
    const data = new FormData(undefined, null);
    data.set("action", "getSchools");
    data.set("table", "");
    console.log(attendantSchool.getValue());
    data.set("query", attendantSchool.getValueRaw());
    const [ok, msg] = await SendPOSTDataToServerAsync("./adminFunctions.php", data);
    const options = new Map();
    for (const school of JSON.parse(msg)) {
        console.log(school);
        options.set(school.name + " → " + school.address, school.id);
    }
    console.log(options);
    attendantSchool.setOptions(options, timestamp);
    return Promise.resolve(true);
};
attendantSchool.validate();
//# sourceMappingURL=adminAttendant.js.map